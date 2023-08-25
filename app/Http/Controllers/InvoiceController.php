<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    //index
    public function index(Request $request)
    {
        $invoiceNumber = $request->input('invoice_number');
        $customerId = $request->input('customer_id');
        $perPage = $request->input('per_page');

        $invoices = invoice::query()
            ->when($invoiceNumber, function ($query) use ($invoiceNumber) {
                $query->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
            })
            ->when($customerId, function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->paginate($perPage);

        // check if invoices has customers
        if ($invoices->count() > 0) {
            // load the customer relationship and select only the needed fields
            $invoices->load(['customer:id,first_name,last_name']);

            // calculate the total invoice items
            $invoices->map(function ($invoice) {
                $invoice->total_items = $invoice->invoiceItems->count();

                // remove invoice items from response
                unset($invoice->invoiceItems);

                return $invoice;
            });

            // hide the pivot table fields
            $invoices->makeHidden(['customer_id']);

        }

        return response()->json($invoices);
    }

    //show
    public function show($id)
    {
        $invoice = invoice::find($id);

        if (!$invoice) {
            throw new NotFoundException('Invoice not found');
        }

        // load invoice items and customer
        $invoice->load(['customer', 'invoiceItems']);

        // hide the pivot table fields
        $invoice->makeHidden(['customer_id']);

        return response()->json($invoice);
    }

    //store
    public function store(Request $request)
    {
        $payload = $request->only([
            'customer_id',
            'invoice_number',
            'issue_date',
            'due_date',
            'description',
            'exchange_rate',
            'sub_total',
            'tax',
            'total',
            'paid',
            'balance',
            'status',
            'invoice_items',
        ]);

        // validate
        $validator = Validator::make($payload, [
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'nullable|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'exchange_rate' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'paid' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'status' => 'nullable|string|in:Active,Inactive',
            'invoice_items' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if invoice already exists by invoice number
        if (isset($payload['invoice_number'])) {
            $invoice = invoice::where('invoice_number', $payload['invoice_number'])->first();

            if ($invoice) {
                throw new BadRequestException('Invoice number already exists');
            }
        } else {
            // generate invoice number automatically
            $payload['invoice_number'] = invoice::generateInvoiceNumber(1);
        }

        // using transaction to ensure atomicity
        DB::beginTransaction();

        try {

            $invoice = invoice::create($payload);

            if (!$invoice) {
                throw new BadRequestException('Failed to create invoice');
            }

            // create invoice items
            if (isset($payload['invoice_items'])) {
                $invoiceItems = $payload['invoice_items'];

                // validate invoice items
                foreach ($invoiceItems as $invoiceItem) {
                    $validator = Validator::make($invoiceItem, [
                        'item_name' => 'required|string|max:50',
                        'quantity' => 'required|integer',
                        'unit_price' => 'required|numeric',
                        'total' => 'nullable|numeric',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // calculate total
                    $invoiceItem['total'] = $invoiceItem['quantity'] * $invoiceItem['unit_price'];

                    // create invoice item
                    $invoiceItem = $invoice->invoiceItems()->create($invoiceItem);

                    if (!$invoiceItem) {
                        throw new BadRequestException('Invoice item not created');
                    }
                }

                // check if total are not provided
                if (!isset($payload['total'])) {
                    // update total
                    $invoice->total = $invoice->invoiceItems()->sum('total');
                    $invoice->save();
                }

            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json($invoice);
    }

    //update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'customer_id',
            'invoice_number',
            'issue_date',
            'due_date',
            'description',
            'exchange_rate',
            'sub_total',
            'tax',
            'total',
            'paid',
            'balance',
            'status',
            'invoice_items',
        ]);

        // validate
        $validator = Validator::make($payload, [
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_number' => 'nullable|unique:invoices,invoice_number,' . $id,
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'exchange_rate' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'paid' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'status' => 'nullable|string|in:Active,Inactive',
            'invoice_items' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $invoice = invoice::find($id);

        if (!$invoice) {
            throw new NotFoundException('Invoice not found');
        }

        // check if invoice already exists by invoice number and not the current invoice
        if (isset($payload['invoice_number'])) {
            $invoiceExisted = invoice::where('invoice_number', $payload['invoice_number'])
                ->where('id', '!=', $id)
                ->first();

            if ($invoiceExisted) {
                throw new BadRequestException('Invoice number already exists');
            }
        }

        // using transaction to ensure atomicity
        DB::beginTransaction();

        try {

            if (!$invoice->update($payload)) {
                throw new BadRequestException('Failed to update invoice');
            }

            // delete all existing invoice items
            $invoice->invoiceItems()->delete();

            // update invoice items
            if (isset($payload['invoice_items'])) {
                $invoiceItems = $payload['invoice_items'];

                // validate invoice items
                foreach ($invoiceItems as $invoiceItem) {
                    $validator = Validator::make($invoiceItem, [
                        'item_name' => 'required|string|max:50',
                        'quantity' => 'required|integer',
                        'unit_price' => 'required|numeric',
                        'total' => 'nullable|numeric',
                    ]);

                    if ($validator->fails()) {
                        throw new UnprocessableEntityException($validator->errors());
                    }

                    // calculate total
                    $invoiceItem['total'] = $invoiceItem['quantity'] * $invoiceItem['unit_price'];

                    // create invoice item
                    $invoiceItem = $invoice->invoiceItems()->create($invoiceItem);

                    if (!$invoiceItem) {
                        throw new BadRequestException('Invoice item not updated');
                    }

                }

                // check if total are not provided
                if (!isset($payload['total'])) {
                    // update total
                    $invoice->total = $invoice->invoiceItems()->sum('total');
                    $invoice->save();
                }

            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['message' => 'Invoice updated successfully']);
    }

    //destroy
    public function destroy($id)
    {
        $invoice = invoice::find($id);

        if (!$invoice) {
            throw new NotFoundException('Invoice not found');
        }

        if (!$invoice->delete()) {
            throw new BadRequestException('Invoice not deleted');
        }

        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
