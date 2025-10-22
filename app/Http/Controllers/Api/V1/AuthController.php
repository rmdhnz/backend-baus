<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST Login
    public function login (Request $request){
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_id' => 'required|string'
        ]);

        $user = User::with('role')->where('username',$validated['username'])->first();

        if(!$user || !Hash::check($validated['password'],$user->password)) { 
            return response()->json([
                "success" => false,
                "message" => "Invalid Username or Password",
                "data" => []
            ],401);
        }
        $token = $user->createToken($validated['device_id'])->plainTextToken;

        $cookie = cookie('auth_token',$token,60 * 24 * 7);

        return response()->json([
            "success" => true,
            "message" => "Login Successful",
            "data" => [
                "token" => $token,
                "user" => $user->name,
                "username" =>  $user->username,
                "role" => $user->role->name,
            ]
        ],200)->withCookie($cookie);
    }
}
