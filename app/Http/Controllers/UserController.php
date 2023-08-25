<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // index
    public function index(Request $request)
    {
        $first_name = $request->input('first_name') ?? null;
        $last_name = $request->input('last_name') ?? null;
        $per_page = $request->input('per_page');

        $users = User::query()
            ->when($first_name, function ($query) use ($first_name) {
                $query->where('first_name', 'like', '%' . $first_name . '%');
            })
            ->when($last_name, function ($query) use ($last_name) {
                $query->where('last_name', 'like', '%' . $last_name . '%');
            })
            ->paginate($per_page);

        // transform to hide pivot table
        $users->getCollection()->transform(function ($user) {

            // roles
            $user->roles = $user->roles()->pluck('name');

            return $user;
        });

        return response()->json($users);
    }

    // show
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        // roles
        $user->roles = $user->roles()->pluck('name');

        return response()->json($user);
    }

    // store
    public function store(Request $request)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'password',
            'roles',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'roles' => 'required|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        // check if user already exists
        $user = User::where('email', $payload['email'])->first();

        if ($user) {
            throw new BadRequestException('User already exists');
        }

        DB::beginTransaction();

        try {

            $user = User::create($payload);

            if (!$user) {
                throw new BadRequestException('User could not be created');
            }

            // check if roles are provided
            if (isset($payload['roles'])) {
                $rolesRequest = $payload['roles'];

                foreach ($rolesRequest as $role) {

                    // check if role exists
                    $roleDb = Role::where('name', $role)->first();

                    if (!$roleDb) {
                        throw new BadRequestException('Role ' . $role . ' does not exist');
                    }

                    // attach role to user
                    $user->roles()->attach($roleDb->id);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['message' => 'User created successfully']);
    }

    // update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'roles',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'roles' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors());
        }

        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        DB::beginTransaction();

        try {

            $user->fill($payload);

            if (!$user->save()) {
                throw new BadRequestException('User could not be updated');
            }

            // check if roles are provided
            if (isset($payload['roles'])) {
                $rolesRequest = $payload['roles'];

                // detach all roles
                $user->roles()->detach();

                foreach ($rolesRequest as $role) {

                    // check if role exists
                    $roleDb = Role::where('name', $role)->first();

                    if (!$roleDb) {
                        throw new BadRequestException('Role ' . $role . ' does not exist');
                    }

                    // attach role to user
                    $user->roles()->attach($roleDb->id);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['message' => 'User updated successfully']);
    }

    // destroy
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        if (!$user->delete()) {
            throw new BadRequestException('User deletion failed');
        }

        return response()->json(['message' => 'User deleted successfully']);
    }
}
