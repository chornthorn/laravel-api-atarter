<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\BankAccount;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    //index
    public function index(Request $request)
    {
        $firstName = $request->query('first_name') ?? '';
        $lastName = $request->query('last_name') ?? '';
        $email = $request->query('email') ?? '';
        $phoneNumber = $request->query('phone_number') ?? '';
        $perPage = $request->input('per_page');

        $vendors = Vendor::query()
            ->when($firstName, function ($query) use ($firstName) {
                $query->where('first_name', 'like', '%' . $firstName . '%');
            })
            ->when($lastName, function ($query) use ($lastName) {
                $query->where('last_name', 'like', '%' . $lastName . '%');
            })
            ->when($email, function ($query) use ($email) {
                $query->where('email', 'like', '%' . $email . '%');
            })
            ->when($phoneNumber, function ($query) use ($phoneNumber) {
                $query->where('phone_number', 'like', '%' . $phoneNumber . '%');
            })
            ->paginate($perPage);

        // count total bank accounts
        $vendors->getCollection()->transform(function ($vendor) {
            $vendor->total_bank_accounts = $vendor->bankAccounts->count();

            // remove bank accounts from response
            unset($vendor->bankAccounts);
            return $vendor;
        });

        return response()->json($vendors);
    }

    //show
    public function show(Request $request, $id)
    {
        $vendor = Vendor::query()->find($id);

        if (!$vendor) {
            throw new NotFoundException('Vendor not found');
        }

        // custom pivot table and select some fields
        $vendor->bank_accounts = $vendor->bankAccounts->map(function ($bankAccount) {
            return $bankAccount->only(
                [
                    'id',
                    'bank_name',
                    'account_number',
                    'account_name',
                    'account_type',
                ]
            );
        });

        // remove bank accounts from response
        unset($vendor->bankAccounts);

        return response()->json($vendor);
    }

    //store
    public function store(Request $request)
    {

        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'address',
            'website',
            'notes',
            'vat_number',
            'status',
            'bank_accounts',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'address' => 'nullable|string',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'bank_accounts' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if vendor already exists
        $vendor = Vendor::where('email', $payload['email'])->first();
        if ($vendor) {
            throw new BadRequestException('Email already exists');
        }

        // use transaction to avoid race condition
        DB::beginTransaction();

        try {
            $vendor = Vendor::create($payload);

            if (!$vendor) {
                throw new BadRequestException('Vendor could not be created');
            }

            // validate bank accounts request
            if (isset($payload['bank_accounts'])) {
                $bankAccounts = $payload['bank_accounts'];

                foreach ($bankAccounts as $bankAccount) {
                    $validator = Validator::make($bankAccount, [
                        'bank_name' => 'required|string',
                        'account_number' => 'required|string',
                        'account_name' => 'required|string',
                        'account_type' => 'nullable|string',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // check if bank account already exists by account number and account name
                    $bankAccountExsisted = BankAccount::where('account_number', $bankAccount['account_number'])
                        ->where('account_name', $bankAccount['account_name'])
                        ->first();

                    if ($bankAccountExsisted) {
                        throw new BadRequestException('Bank account number ' . $bankAccount['account_number'] . ' already exists, please try again');
                    }

                    // create bank account
                    $vendorBankAccount = $vendor->bankAccounts()->create($bankAccount);

                    if (!$vendorBankAccount) {
                        throw new BadRequestException('Bank account could not be created');
                    }
                }

            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json($vendor);
    }

    //update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'address',
            'website',
            'notes',
            'vat_number',
            'status',
            'bank_accounts',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'bank_accounts' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors()->first());
        }

        $vendor = Vendor::query()->findOrFail($id);

        if (!$vendor) {

            throw new NotFoundException('Vendor not found');
        }

        // check if email already exists with different id
        $emailExists = Vendor::where('email', $payload['email'])->where('id', '!=', $id)->first();
        if ($emailExists) {
            throw new BadRequestException('Email already in use');
        }

        // use transaction to avoid race condition
        DB::beginTransaction();

        try {
            if (!$vendor->update($payload)) {
                throw new BadRequestException('Vendor could not be updated');
            }

            // delete all existing bank accounts of this vendor
            $vendor->bankAccounts()->delete();

            // validate bank accounts request
            if (isset($payload['bank_accounts'])) {
                $bankAccounts = $payload['bank_accounts'];

                foreach ($bankAccounts as $bankAccount) {
                    $validator = Validator::make($bankAccount, [
                        'bank_name' => 'required|string',
                        'account_number' => 'required|string',
                        'account_name' => 'required|string',
                        'account_type' => 'nullable|string',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // check if bank account already exists by account number and account name
                    $bankAccountExsisted = BankAccount::where('account_number', $bankAccount['account_number'])
                        ->where('account_name', $bankAccount['account_name'])
                        ->first();

                    if ($bankAccountExsisted) {
                        throw new BadRequestException('Bank account number ' . $bankAccount['account_number'] . ' already exists, please try again');
                    }

                    // create bank account
                    $vendorBankAccount = $vendor->bankAccounts()->create($bankAccount);

                    if (!$vendorBankAccount) {
                        throw new BadRequestException('Bank account could not be created');
                    }
                }

            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
