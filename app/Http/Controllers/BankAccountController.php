<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    // index
    public function index(Request $request)
    {
        $bankName = $request->input('bank_name');
        $accountName = $request->input('account_name');
        $accountNumber = $request->input('account_number');
        $perPage = $request->input('per_page');

        $bankAccounts = BankAccount::query()
            ->when($bankName, function ($query) use ($bankName) {
                $query->where('bank_name', 'like', '%' . $bankName . '%');
            })
            ->when($accountName, function ($query) use ($accountName) {
                $query->where('account_name', 'like', '%' . $accountName . '%');
            })
            ->when($accountNumber, function ($query) use ($accountNumber) {
                $query->where('account_number', 'like', '%' . $accountNumber . '%');
            })
            ->paginate($perPage);

        return response()->json($bankAccounts);
    }

    // show
    public function show($id)
    {
        $bankAccount = BankAccount::find($id);

        if (!$bankAccount) {
            throw new NotFoundException('Bank account not found');
        }

        return response()->json($bankAccount);
    }

    // store
    public function store(Request $request)
    {
        $payload = $request->only([
            'bank_name',
            'account_number',
            'account_name',
            'account_type',
            'status',
        ]);

        $validator = Validator::make($payload, [
            'bank_name' => 'required|string',
            'account_number' => 'required',
            'account_name' => 'required|string',
            'account_type' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors()->first());
        }

        // check if bank account already exists
        $bankAccount = BankAccount::where('account_number', $payload['account_number'])->first();

        if ($bankAccount) {
            throw new BadRequestException('Bank account already exists');
        }

        $bankAccount = BankAccount::create($payload);

        if (!$bankAccount) {
            throw new BadRequestException('Bank account could not be created');
        }

        return response()->json($bankAccount, 201);
    }

    // update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'bank_name',
            'account_number',
            'account_name',
            'account_type',
            'status',
        ]);

        $validator = Validator::make($payload, [
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable',
            'account_name' => 'nullable|string',
            'account_type' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors()->first());
        }

        $bankAccount = BankAccount::find($id);

        if (!$bankAccount) {
            throw new NotFoundException('Bank account not found');
        }

        if (!$bankAccount->update($payload)) {
            throw new BadRequestException('Bank account could not be updated');
        }

        return response()->json($bankAccount);
    }

    // destroy
    public function destroy($id)
    {
        $bankAccount = BankAccount::find($id);

        if (!$bankAccount) {
            throw new NotFoundException('Bank account not found');
        }

        if (!$bankAccount->delete()) {
            throw new BadRequestException('Bank account could not be deleted');
        }

        return response()->json($bankAccount);
    }
}
