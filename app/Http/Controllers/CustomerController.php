<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\BankAccount;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // index
    public function index(Request $request)
    {
        $firstName = $request->input('first_name') ?? '';
        $lastName = $request->input('last_name') ?? '';
        $email = $request->input('email') ?? '';
        $phoneNumber = $request->input('phone_number') ?? '';
        $perPage = $request->input('per_page');

        $customers = Customer::query()
            ->when($firstName, function ($query) use ($firstName) {
                $query->where('first_name', 'like', '%' . $firstName . '%');
            })
            ->when($lastName, function ($query) use ($lastName) {
                $query->where('last_name', 'like', '%' . $lastName . '%');
            })
            ->paginate($perPage);

        // count total bank accounts
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_bank_accounts = $customer->bankAccounts->count();

            // remove bank accounts from response
            unset($customer->bankAccounts);
            return $customer;
        });

        return response()->json($customers);
    }

    // show
    public function show($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        // transform the customer
        $customer->bank_accounts = $customer->bankAccounts->map(function ($bankAccount) {
            return $bankAccount->only(
                ['id', 'bank_name', 'account_number', 'account_name', 'account_type']
            );
        });

        // remove bank_accounts field from customer
        unset($customer->bankAccounts);

        return response()->json($customer);
    }

    // store
    public function store(Request $request)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'address',
            'vat_number',
            'status',
            'bank_accounts',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'nullable|email',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:20',
            'status' => 'nullable|in:Active,Inactive',
            'bank_accounts' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if customer already exists by email or phone number
        $customer = Customer::where('email', $payload['email'])
            ->orWhere('phone_number', $payload['phone_number'])->first();

        if ($customer) {
            throw new BadRequestException('Customer already exists');
        }

        // use transaction to ensure atomicity
        DB::beginTransaction();

        try {

            // create customer
            $customer = Customer::create($payload);

            if (!$customer) {
                throw new BadRequestException('Customer not created');
            }

            // create bank accounts
            if (isset($payload['bank_accounts'])) {
                $bankAccounts = $payload['bank_accounts'];

                foreach ($bankAccounts as $bankAccount) {
                    // validate bank account
                    $validator = Validator::make($bankAccount, [
                        'bank_name' => 'required|string|max:50',
                        'account_number' => 'required|string|max:50',
                        'account_name' => 'required|string|max:50',
                        'account_type' => 'nullable|string|max:50',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // check if bank account already exists by account number and account name
                    $bankAccountExsisted = BankAccount::where('account_number', $bankAccount['account_number'])
                        ->where('account_name', $bankAccount['account_name'])->first();

                    if ($bankAccountExsisted) {
                        throw new BadRequestException('Bank account already exists');
                    }

                    // create bank account
                    $bankAccount = $customer->bankAccounts()->create($bankAccount);

                    if (!$bankAccount) {
                        throw new BadRequestException('Bank account not created');
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json($customer);
    }

    // update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'address',
            'vat_number',
            'status',
            'bank_accounts',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:20',
            'status' => 'nullable|in:Active,Inactive',
            'bank_accounts' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        // use transaction to ensure atomicity
        DB::beginTransaction();

        try {

            // update customer
            if (!$customer->update($payload)) {
                throw new BadRequestException('Customer not updated');
            }

            // delete all existing bank accounts and create new with updated data
            $customer->bankAccounts()->delete();

            // update bank accounts
            if (isset($payload['bank_accounts'])) {
                $bankAccounts = $payload['bank_accounts'];

                foreach ($bankAccounts as $bankAccount) {
                    // validate bank account
                    $validator = Validator::make($bankAccount, [
                        'bank_name' => 'required|string|max:50',
                        'account_number' => 'required|string|max:50',
                        'account_name' => 'required|string|max:50',
                        'account_type' => 'nullable|string|max:50',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // check if bank account already exists by account number and account name
                    $bankAccountExsisted = BankAccount::where('account_number', $bankAccount['account_number'])
                        ->where('account_name', $bankAccount['account_name'])->first();

                    if ($bankAccountExsisted) {
                        throw new BadRequestException('Bank account already exists');
                    }

                    // create bank account
                    $bankAccount = $customer->bankAccounts()->create($bankAccount);

                    if (!$bankAccount) {
                        throw new BadRequestException('Bank account not created');
                    }

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // destroy
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        if (!$customer->delete()) {
            throw new BadRequestException('Customer not deleted');
        }

        return response()->json(['message' => 'Customer deleted']);
    }
}
