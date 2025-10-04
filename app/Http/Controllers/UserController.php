<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function suggestUsers()
    {
        $user = Auth::user();

        $suggestions = User::with('profile:id,user_id,profile_images,role,university')
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $user->followings()->pluck('followed_id'))
            ->inRandomOrder()
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'username']);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User suggestions retrieved successfully.'
            ],
            'data' => $suggestions
        ], 200);
    }

    public function getAllUsers()
    {
        $user = Auth::user();

        $users = User::with('profile:id,user_id,profile_images,role,university')
            ->where('id', '!=', $user->id)
            ->get(['id', 'first_name', 'last_name', 'username', 'email']);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'All users retrieved successfully.'
            ],
            'data' => $users
        ], 200);
    }

    public function getUserByUsername($username)
    {
        $user = User::with('profile:id,user_id,profile_images,role,university')
            ->where('username', $username)
            ->first(['id', 'first_name', 'last_name', 'username', 'email']);

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User not found.'
                ],
                'data' => null
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User retrieved successfully.'
            ],
            'data' => $user
        ], 200);
    }
}
