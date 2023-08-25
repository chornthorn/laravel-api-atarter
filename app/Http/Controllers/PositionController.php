<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{

    public function index(Request $request)
    {
        $name = $request->input('name');
        $code = $request->input('code');
        $perPage = $request->input('per_page');

        $positions = Position::query()
            ->when($name, function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when($code, function ($query) use ($code) {
                $query->where('code', 'like', '%' . $code . '%');
            })
            ->paginate($perPage);

        return response()->json($positions);
    }

    /**
     * Retrieves and displays a specific position.
     *
     * @param int $id The ID of the position to be displayed.
     * @throws NotFoundException If the position is not found.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the position data.
     */
    public function show($id)
    {
        $position_show = Position::find($id);

        if (!$position_show) {
            throw new NotFoundException('Position not found');
        }

        return response()->json($position_show);
    }

    /**
     * Store the data sent in the request.
     *
     * @param Request $request The request object that contains the data to be stored.
     * @throws UnprocessableEntityException If the validation fails.
     * @throws BadRequestException If the position creation fails.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the created position.
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

        $positionExists = Position::where('name', $payload['name'])->first();

        if ($positionExists) {
            throw new BadRequestException('Position already exists');
        }

        $position = Position::create($payload);

        if (!$position) {
            throw new BadRequestException('Position creation failed');
        }

        return response()->json($position, 201);
    }

    /**
     * Updates a position based on the provided request data.
     *
     * @param Request $request The request object containing the data to update the position.
     * @param int $id The ID of the position to update.
     * @throws UnprocessableEntityException If the request data fails validation.
     * @throws NotFoundException If the position with the provided ID is not found.
     * @throws BadRequestException If the position update fails.
     * @return \Illuminate\Http\JsonResponse The updated position data in JSON format.
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

        $position = Position::find($id);

        if (!$position) {
            throw new NotFoundException('Position not found');
        }

        $position->fill($payload);

        if (!$position->save()) {
            throw new BadRequestException('Position update failed');
        }

        return response()->json($position);
    }

    /**
     * Deletes a position by its ID.
     *
     * @param int $id The ID of the position to be deleted.
     * @throws NotFoundException If the position with the given ID does not exist.
     * @throws BadRequestException If the position deletion fails.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success of the deletion.
     */
    public function destroy($id)
    {
        $position = Position::find($id);

        if (!$position) {
            throw new NotFoundException('Position not found');
        }

        if (!$position->delete()) {
            throw new BadRequestException('Position deletion failed');
        }

        return response()->json(['message' => 'Position deleted successfully']);
    }

}
