<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
// use App\Models\Role;
use Laravel\Sanctum\PersonalAccessToken;
// use Exception;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    // POST Register
    public function register (Request $request){
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'password' => ['required', 'string', 'min:6'],
            'device_id' => ['required', 'string', 'max:100'],
        ]);
        $existingUser = User::where('device_id', $validated['device_id'])->first();
        if($existingUser){
            return response()->json([
                "success" => false,
                "message" => "Device ID is already registered with another user.",
                "data" => []
            ],409);
        }
        $user = User::create([
            "username" => $validated['username'],
            "email" => $validated['email'],
            "name" => $validated['name'],
            "phone" => $validated['phone'],
            "role_id" => $validated['role_id'],
            "password" => Hash::make($validated['password']),
            "active_status" => false,
            "device_id" => $validated['device_id'],
        ]);
        $user->load('role');


        return response()->json([
            "success" => true,
            "message" => "User Registered Successfully",
            "data" => [
                "user" => $user->name,
                "username" =>  $user->username,
                "role" => $user->role->name,
            ]
        ],201);
    }



    // POST Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $user = User::with('role')->where('username', $validated['username'])->first();

        if (!$user) {
            return response()->json(["success" => false, "message" => "User not found"], 404);
        }
        if (!$user->active_status) {
            return response()->json(["success" => false, "message" => "User not active"], 403);
        }
        if ($user->device_id && $user->device_id !== $validated['device_id']) {
            return response()->json(["success" => false, "message" => "Login from this device is not allowed"], 403);
        }
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                "success" => false, 
                "message" => "Username Atau Password Tidak Sesuai"
            ], 401);
        }


        $user->tokens()->where('name', $validated['device_id'])->delete();
        
        $token = $user->createToken($validated['device_id'])->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Login successful",
            "data" => [
                "token" => $token,
                "name" => $user->name,
                "username" => $user->username,
                "role" => $user->role->name,
            ]
        ]);
    }


    // GET Profile
    public function getProfile (Request $request){
        $user  = $request->user()->load('role');

        return response()->json([
            "success" => true,
            "message" => "User Profile",
            "data" => [
                "name" => $user->name,
                "username" =>  $user->username,
                "email" => $user->email,
                "active_status" => $user->active_status,
                "role" => $user->role->name,
            ]
        ],200);
    }

    public function logout(Request $request)
    {
        try {
            /** @var PersonalAccessToken|null $token */
            $token = $request->user()->currentAccessToken();

            if ($token) {
                $token->delete(); 
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout successful. Token revoked.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    // PUT Update My Profile
    public function updateMyProfile(Request $request)
    {
        $user = $request->user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'username' => 'sometimes|required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'username', 'email', 'phone']));

        return response()->json([
            "success" => true,
            "message" => "Profile updated successfully",
            "data" => $user
        ]);
    }

    // PUT Update my password
    public function updateMyPassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:5',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'
            ],
        ], [
            'new_password.min' => 'Password must be at least 5 characters long.',
            'new_password.regex' => 'Password must contain at least one letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Old password is incorrect.",
            ], 401);
        }

        // Update password baru (dihash)
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            "success" => true,
            "message" => "Password updated successfully.",
        ]);
    }

}
