<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFeedRequest;
use App\Http\Requests\UpdateFeedRequest;
use App\Models\Activity;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::with([
            'user:id,first_name,last_name',
            'user.profile:id,user_id,profile_images,role,university',
            'activity:id,title,activity_type,activity_category,max_participants,description'
        ])->get();

        $data = $feeds->map(function ($feed) {
            return [
                'id' => $feed->id,
                'content' => $feed->content,
                'created_at' => $feed->created_at,
                'updated_at' => $feed->updated_at,
                'user' => [
                    'id' => $feed->user->id,
                    'name' => trim($feed->user->first_name . ' ' . $feed->user->last_name),
                    'role' => $feed->user->profile?->role,
                    'university' => $feed->user->profile?->university,
                    'profile_images' => $feed->user->profile?->profile_images,
                ],
                'activity' => $feed->activity,
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Feeds retrieved successfully',
            ],
            'data' => $data,
        ]);
    }


    public function getByUsername($username)
    {
        $feeds = Feed::whereHas('user', function ($q) use ($username) {
            $q->where('username', $username);
        })->with([
            'user:id,first_name,last_name',
            'user.profile:id,user_id,profile_images,role,university',
            'activity:id,title,activity_type,activity_category,max_participants,description'
        ])->get();

        $data = $feeds->map(function ($feed) {
            return [
                'id' => $feed->id,
                'content' => $feed->content,
                'created_at' => $feed->created_at,
                'updated_at' => $feed->updated_at,
                'user' => [
                    'id' => $feed->user->id,
                    'name' => trim($feed->user->first_name . ' ' . $feed->user->last_name),
                    'role' => $feed->user->profile?->role,
                    'university' => $feed->user->profile?->university,
                    'profile_images' => $feed->user->profile?->profile_images,
                ],
                'activity' => $feed->activity,
            ];
        });


        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User feeds retrieved successfully',
            ],
            'data' => $data,
        ]);
    }

    public function store(CreateFeedRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $feed = Feed::create($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
                'message' => 'Feed created successfully',
            ],
            'data' => $feed,
        ], 201);
    }

    public function destroy($id)
    {
        $feed = Feed::find($id);

        if (!$feed) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Feed not found',
                ],
                'data' => null,
            ], 404);
        }

        $feed->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Feed deleted successfully',
            ],
            'data' => null,
        ]);
    }

    public function show($id)
    {
        $feed = Feed::with([
            'user:id,name,profile_images,role,university',
            'activity:id,title,activity_type,activity_category,max_participants,description'
        ])->find($id);

        if (!$feed) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Feed not found',
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Feed retrieved successfully',
            ],
            'data' => $feed,
        ]);
    }

    public function update(UpdateFeedRequest $request, $id)
    {
        $feed = Feed::find($id);

        if (!$feed) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Feed not found',
                ],
                'data' => null,
            ], 404);
        }

        $data = $request->validated();

        if (isset($data['activity_id'])) {
            $activityExists = Activity::where('id', $data['activity_id'])->exists();

            if (!$activityExists) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 404,
                        'message' => 'Activity not found',
                    ],
                    'data' => null,
                ], 404);
            }
        }

        $feed->update($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Feed updated successfully',
            ],
            'data' => $feed,
        ]);
    }
}
