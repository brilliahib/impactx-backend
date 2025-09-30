<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function index()
    {
        $profile = UserProfile::where('user_id', Auth::id())->first();

        if (!$profile) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User profile not found',
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User profile retrieved successfully',
            ],
            'data' => $profile,
        ]);
    }

    public function getByUsername($username)
    {
        $profile = UserProfile::whereHas('user', function ($query) use ($username) {
            $query->where('username', $username);
        })->first();

        if (!$profile) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User profile not found',
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User profile retrieved successfully',
            ],
            'data' => $profile,
        ]);
    }

    public function store(CreateUserProfileRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('profile_images')) {
            $file = $request->file('profile_images');
            $username = Auth::user()->username ?? 'user_' . Auth::id();

            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '_' . time()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('profile_images/' . $username, $filename, 'public');

            $data['profile_images'] = $path;
        }

        $profile = UserProfile::create($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
                'message' => 'User profile created successfully',
            ],
            'data' => $profile,
        ], 201);
    }

    public function update(UpdateUserProfileRequest $request)
    {
        $profile = UserProfile::where('user_id', Auth::id())->first();

        if (!$profile) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User profile not found',
                ],
                'data' => null,
            ], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('profile_images')) {
            $file = $request->file('profile_images');
            $username = Auth::user()->username ?? 'user_' . Auth::id();
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '_' . time()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('profile_images/' . $username, $filename, 'public');

            if ($profile->profile_images && Storage::disk('public')->exists($profile->profile_images)) {
                Storage::disk('public')->delete($profile->profile_images);
            }

            $data['profile_images'] = $path;
        }

        $profile->update($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User profile updated successfully',
            ],
            'data' => $profile,
        ]);
    }

    public function destroy()
    {
        $profile = UserProfile::where('user_id', Auth::id())->first();

        if (!$profile) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User profile not found',
                ],
                'data' => null,
            ], 404);
        }

        $profile->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User profile deleted successfully',
            ],
            'data' => null,
        ]);
    }

    public function getProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Unauthorized',
                ],
                'data' => null,
            ], 401);
        }

        $userWithProfile = \App\Models\User::with([
            'profile:id,user_id,profile_images,role,university,major'
        ])->find($user->id);

        if (!$userWithProfile) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'User not found',
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User profile retrieved successfully',
            ],
            'data' => [
                'id' => $userWithProfile->id,
                'first_name' => $userWithProfile->first_name,
                'last_name' => $userWithProfile->last_name,
                'email' => $userWithProfile->email,
                'username' => $userWithProfile->username,
                'profile' => $userWithProfile->profile,
            ],
        ]);
    }
}
