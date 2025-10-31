<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class SupervisorController extends Controller
{

    // GET all users
    public function getAllUser (Request $request){
        $users = User::with('role')->get();
        return response()->json([
            "success" => true,
            "message" => "List of all users",
            "total" => count($users),
            "data" => $users
        ]);
    }
    // PUT Activate User
    public function activateUser (Request $request){
        // 
        $validated = $request->validate([
            'username' => ['required','string','exists:users,username'],
        ]);
        $user = User::where('username',$validated['username'])->first();
        if(!$user) {
            return response()->json([
                "success" => false,
                "message" => "User not found",
                "data" => [],
            ],404);
        }

        $user->active_status = true;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "User activated successfully",
            "data" => [
                "username" => $user->username,
                "active_status" => $user->active_status,
            ]
        ],200); 
    }

    // Get All User In-Active
    public function getInActiveUsers (){
        $users = User::with('role')->where('active_status',false)->get();
        return response()->json([
            "success" => true,
            "total" => count($users),
            "data" => $users,
        ]);
    }
}
