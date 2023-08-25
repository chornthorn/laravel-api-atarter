<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{

    public function index(Request $request)
    {
        $name = $request->input('name');
        $code = $request->input('code');
        $perPage = $request->input('per_page');

        $departments = Department::query()
            ->when($name, function ($query) use ($name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->when($code, function ($query) use ($code) {
                return $query->where('code', 'like', '%' . $code . '%');
            })
            ->paginate($perPage);

        return response()->json($departments);
    }

    /**
     * Displays the department with the specified ID.
     *
     * @param int $id The ID of the department to display.
     * @throws NotFoundException If the department with the specified ID is not found.
     * @return JsonResponse The JSON response containing the department information.
     */
    public function show($id)
    {
        $department_show = Department::find($id);

        if (!$department_show) {
            throw new NotFoundException('Department not found');
        }

        return response()->json($department_show);
    }

    /**
     * Stores a new department based on the request data.
     *
     * @param Request $request The HTTP request object.
     * @throws UnprocessableEntityException If the validation fails.
     * @throws BadRequestException If the department creation fails.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the created department.
     */
    public function store(Request $request)
    {
        $payload = $request->only(['name', 'description']);

        $validator = Validator::make($payload, [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if the department already exists
        $departmentExsited = Department::where('name', $payload['name'])->first();

        if ($departmentExsited) {
            throw new BadRequestException('Department already exists');
        }

        $department = Department::create($payload);

        if (!$department) {
            throw new BadRequestException('Failed to create department');
        }

        return response()->json($department);
    }

    /**
     * Updates a department.
     *
     * @param Request $request The request object.
     * @param mixed $id The department ID.
     * @throws UnprocessableEntityException If the validation fails.
     * @throws NotFoundException If the department is not found.
     * @throws BadRequestException If the update fails.
     * @return \Illuminate\Http\JsonResponse The updated department.
     */
    public function update(Request $request, $id)
    {
        $payload = $request->only(['name', 'description']);

        $validator = Validator::make($payload, [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $department = Department::find($id);

        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        if (!$department->update($payload)) {
            throw new BadRequestException('Failed to update department');
        }

        return response()->json($department);
    }

    /**
     * Deletes a department.
     *
     * @param mixed $id The ID of the department to be deleted.
     * @throws NotFoundException if the department is not found.
     * @throws BadRequestException if the department deletion fails.
     * @return JsonResponse The JSON response indicating the success of the deletion.
     */
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        if (!$department->delete()) {
            throw new BadRequestException('Failed to delete department');
        }

        return response()->json([
            'message' => 'Department deleted successfully',
        ]);
    }

    /**
     * Maps a position to a department based on the provided request data.
     *
     * @param Request $request The HTTP request object containing the data needed for mapping.
     * @throws UnprocessableEntityException If the request data fails validation.
     * @throws NotFoundException If the position or department does not exist.
     * @throws BadRequestException If the position is already mapped to the department.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the mapping.
     */
    public function map(Request $request)
    {
        $payload = $request->only(['position_id', 'department_id']);

        $validator = Validator::make($payload, [
            'position_id' => 'required|integer|exists:positions,id',
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $position = Position::find($payload['position_id']);

        if (!$position) {
            throw new NotFoundException('Position not found');
        }

        $department = Department::find($payload['department_id']);

        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        // sync the position to the department
        $department->positions()->syncWithoutDetaching($position);

        return response()->json([
            'message' => 'Position mapped to department successfully',
        ]);

    }

    // unmap position to department using body both position_id and department_id
    public function unmap(Request $request)
    {
        $payload = $request->only(['position_id', 'department_id']);

        $validator = Validator::make($payload, [
            'position_id' => 'required|integer|exists:positions,id',
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $position = Position::find($payload['position_id']);

        if (!$position) {
            throw new NotFoundException('Position not found');
        }

        $department = Department::find($payload['department_id']);

        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        // detach the position from the department
        $department->positions()->detach($position);

        return response()->json([
            'message' => 'Position unmapped from department successfully',
        ]);
    }

    /**
     * Retrieves the positions associated with the specified department.
     *
     * @param mixed $id The ID of the department.
     * @throws NotFoundException If the department is not found.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the positions.
     */
    public function positions($id)
    {
        $department = Department::find($id);

        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        // hide the pivot table data from the response
        if ($department->positions) {
            $department->positions->makeHidden(['pivot']);
        }

        return response()->json($department->positions);
    }
}
