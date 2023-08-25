<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    // index
    public function index(Request $request)
    {
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $code = $request->input('code');
        $perPage = $request->input('per_page');

        $employees = Employee::query()
            ->when($firstName, function ($query) use ($firstName) {
                $query->where('first_name', 'like', '%' . $firstName . '%');
            })
            ->when($lastName, function ($query) use ($lastName) {
                $query->where('last_name', 'like', '%' . $lastName . '%');
            })
            ->when($code, function ($query) use ($code) {
                $query->where('code', 'like', '%' . $code . '%');
            })
            ->paginate($perPage);

        // check if employees has positions and departments
        if ($employees->count() > 0) {
            // load the department and position relationships and select only the needed fields
            $employees->load(['department:id,name', 'position:id,name']);

            // hide the pivot table fields
            $employees->makeHidden(['department_id', 'position_id']);

        }

        // tranform profile_url to url for preview
        $employees->transform(function ($employee) {
            if ($employee->profile_url) {
                $disk = Storage::disk('gcs');
                $employee->profile_url = $disk->temporaryUrl($employee->profile_url, now()->addMinutes(60));

                return $employee;
            }

            return $employee;
        });

        return response()->json($employees);
    }

    // show
    public function show($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }

        // load the department and position relationships and select only the needed fields
        $employee->load(['department:id,name', 'position:id,name']);

        // hide the pivot table fields
        $employee->makeHidden(['department_id', 'position_id']);

        // check if profile_url exists
        if ($employee->profile_url) {
            $disk = Storage::disk('gcs');
            $employee->profile_url = $disk->temporaryUrl($employee->profile_url, now()->addMinutes(60));
        }

        return response()->json($employee);
    }

    // store
    public function store(Request $request)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'code',
            'gender',
            'phone_number',
            'number_of_children',
            'profile_file',
            'address',
            'dob',
            'doj',
            'dol',
            'status',
            'position_id',
            'department_id',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'code' => 'nullable|string',
            'gender' => 'required|string|in:Male,Female',
            'phone_number' => 'nullable|string',
            'number_of_children' => 'nullable|integer',
            'profile_file' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'address' => 'nullable|string',
            'dob' => 'nullable|string',
            'doj' => 'nullable|string',
            'dol' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'position_id' => 'required|integer|exists:positions,id',
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if employee already exists by email
        $employeeExsisted = Employee::where('email', $payload['email'])->first();

        if ($employeeExsisted) {
            throw new BadRequestException('Employee already exists');
        }

        // check if position and department not found
        $position = Position::find($payload['position_id']);
        if (!$position) {
            throw new NotFoundException('Position not found');
        }

        $department = Department::find($payload['department_id']);
        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        // check profile file provided from request
        if (isset($payload['profile_file'])) {

            $file = $payload['profile_file'];
            $disk = Storage::disk('gcs');

            $fileUrl = $disk->put('employees', $file);

            // set the profile url
            $payload['profile_url'] = $fileUrl;
        }

        $employee = Employee::create($payload);

        // attach the department and position
        $employee->department()->associate($request->input('department_id'));
        $employee->position()->associate($request->input('position_id'));

        if (!$employee) {

            // delete the file if it exists
            if (isset($payload['profile_file'])) {
                $disk = Storage::disk('gcs');
                $disk->delete($fileUrl);
            }

            throw new BadRequestException('Failed to create employee');
        }

        return response()->json($employee);
    }

    // update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'code',
            'gender',
            'phone_number',
            'number_of_children',
            'profile_file',
            'address',
            'dob',
            'doj',
            'dol',
            'status',
            'position_id',
            'department_id',
        ]);

        // optional all fields when validator
        $validator = Validator::make($payload, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'code' => 'nullable|string',
            'gender' => 'nullable|string|in:Male,Female',
            'phone_number' => 'nullable|string',
            'number_of_children' => 'nullable|integer',
            'profile_file' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'address' => 'nullable|string',
            'dob' => 'nullable|string',
            'doj' => 'nullable|string',
            'dol' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'position_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $employee = Employee::find($id);

        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }

        // check if position and department are provided
        if (isset($payload['position_id']) && isset($payload['department_id'])) {
            $position = Position::find($payload['position_id']);
            $department = Department::find($payload['department_id']);

            if (!$position) {
                throw new NotFoundException('Position not found');
            }

            if (!$department) {
                throw new NotFoundException('Department not found');
            }
        }

        if (isset($payload['profile_file'])) {
            $file = $payload['profile_file'];
            $disk = Storage::disk('gcs');

            $fileUrl = $disk->put('employees', $file);

            // delete the old profile
            if ($employee->profile_url) {
                $disk->delete($employee->profile_url);
            }

            $payload['profile_url'] = $fileUrl;

        }

        if (!$employee->update($payload)) {
            throw new BadRequestException('Failed to update employee');
        }

        return response()->json([
            'message' => 'Employee updated successfully',
        ]);

    }

    // destroy
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }

        if (!$employee->delete()) {
            throw new BadRequestException('Failed to delete employee');
        }

        // if profile_url exists
        if ($employee->profile_url) {
            $disk = Storage::disk('gcs');
            $disk->delete($employee->profile_url);
        }

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }
}
