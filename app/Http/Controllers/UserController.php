<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'date_of_birth' => 'required|date',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users'),
                ],
                'location' => [
                    'required',
                    Rule::in(\App\Helpers\LocationHelper::locations())
                ],
            ]);
            $user = User::create($data);

            return response()->json($user, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json(['errors' => $e->errors()], 400);
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');

        try {
            $data = $request->validate([
                'first_name' => 'string',
                'last_name' => 'string',
                'date_of_birth' => 'date',
                'location' => [
                    Rule::in(\App\Helpers\LocationHelper::locations())
                ],
            ]);

            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->update($data);

            return response()->json(['message' => 'User updated', 'user' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        if (empty($user->id)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted'], 200);
    }
}
