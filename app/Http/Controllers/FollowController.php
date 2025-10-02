<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function follow(Request $request, $username)
    {
        $user = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        if ($user->id == $target->id) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 400,
                    'message' => 'You cannot follow yourself.'
                ],
                'data' => null
            ], 400);
        }

        if ($user->followings()->where('followed_id', $target->id)->exists()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 400,
                    'message' => 'Already following this user.'
                ],
                'data' => null
            ], 400);
        }

        $user->followings()->attach($target->id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Successfully followed the user.'
            ],
            'data' => ['followed_username' => $username]
        ], 200);
    }

    // unfollow a user by username
    public function unfollow(Request $request, $username)
    {
        $user = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        if (!$user->followings()->where('followed_id', $target->id)->exists()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 400,
                    'message' => 'You are not following this user.'
                ],
                'data' => null
            ], 400);
        }

        $user->followings()->detach($target->id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Successfully unfollowed the user.'
            ],
            'data' => ['unfollowed_username' => $username]
        ], 200);
    }


    public function followersByUsername($username)
    {
        $authUserId = auth()->id();
        $user = User::where('username', $username)->firstOrFail();

        $followers = $user->followers()
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.username',
                'user_profiles.role',
                'user_profiles.university',
                'user_profiles.profile_images'
            )
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->withExists(['followers as is_followed' => function ($q) use ($authUserId) {
                $q->where('follower_id', $authUserId);
            }])
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Followers retrieved successfully.'
            ],
            'data' => $followers
        ], 200);
    }

    public function followingsByUsername($username)
    {
        $authUserId = auth()->id();
        $user = User::where('username', $username)->firstOrFail();

        $followings = $user->followings()
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.username',
                'user_profiles.role',
                'user_profiles.university',
                'user_profiles.profile_images'
            )
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->get()
            ->map(function ($following) use ($authUserId) {
                $following->is_followed = \DB::table('follows')
                    ->where('follower_id', $authUserId)
                    ->where('followed_id', $following->id)
                    ->exists();
                return $following;
            });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Followings retrieved successfully.'
            ],
            'data' => $followings
        ], 200);
    }

    public function countFollowersAndFollowings($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $followersCount = $user->followers()->count();
        $followingsCount = $user->followings()->count();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Follower and following counts retrieved successfully.'
            ],
            'data' => [
                'username' => $user->username,
                'followers_count' => $followersCount,
                'followings_count' => $followingsCount
            ]
        ], 200);
    }

    public function isFollowing($username)
    {
        $user = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        $isFollowing = $user->followings()->where('followed_id', $target->id)->exists();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Check follow status successfully.'
            ],
            'data' => [
                'username' => $target->username,
                'is_following' => $isFollowing
            ]
        ], 200);
    }
}
