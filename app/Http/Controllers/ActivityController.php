<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with([
            'user:id,first_name,last_name',
            'user.profile:id,user_id,profile_images,role,university'
        ])->get();

        $data = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'activity_type' => $activity->activity_type,
                'activity_category' => $activity->activity_category,
                'max_participants' => $activity->max_participants,
                'description' => $activity->description,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => trim($activity->user->first_name . ' ' . $activity->user->last_name),
                    'role' => $activity->user->profile?->role,
                    'university' => $activity->user->profile?->university,
                    'profile_images' => $activity->user->profile?->profile_images,
                ],
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Activities retrieved successfully',
            ],
            'data' => $data,
        ]);
    }

    public function getActivityUser($username)
    {
        $activities = Activity::whereHas('user', function ($q) use ($username) {
            $q->where('username', $username);
        })
            ->with([
                'user:id,first_name,last_name',
                'user.profile:id,user_id,profile_images,role,university'
            ])
            ->get();

        $data = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'activity_type' => $activity->activity_type,
                'activity_category' => $activity->activity_category,
                'max_participants' => $activity->max_participants,
                'description' => $activity->description,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => trim($activity->user->first_name . ' ' . $activity->user->last_name),
                    'role' => $activity->user->profile?->role,
                    'university' => $activity->user->profile?->university,
                    'profile_images' => $activity->user->profile?->profile_images,
                ],
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'User activities retrieved successfully',
            ],
            'data' => $data,
        ]);
    }

    public function getActivityDetail($id)
    {
        $data = Activity::find($id);

        if (!$data) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Activity not found',
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Activity detail retrieved successfully',
            ],
            'data' => $data,
        ]);
    }

    public function store(CreateActivityRequest $request)
    {
        $data = $request->validated();

        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('activity_images')) {
            $file = $request->file('activity_images');
            $username = Auth::user()->username ?? 'user_' . Auth::id();

            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '_' . time()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('activity_images/' . $username, $filename, 'public');

            $data['activity_images'] = $path;
        }

        $activity = Activity::create($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
                'message' => 'Activity created successfully',
            ],
            'data' => $activity,
        ], 201);
    }

    public function update(UpdateActivityRequest $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Activity not found',
                ],
                'data' => null,
            ], 404);
        }

        if ($activity->user_id !== Auth::id()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'Unauthorized to update this activity',
                ],
                'data' => null,
            ], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('images')) {
            $file = $request->file('images');
            $username = Auth::user()->username ?? 'user_' . Auth::id();

            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '_' . time()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('activity_images/' . $username, $filename, 'public');

            $data['images'] = $path;
        }

        $activity->update($data);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Activity updated successfully',
            ],
            'data' => $activity,
        ]);
    }


    public function destroy($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Activity not found',
                ],
                'data' => null,
            ], 404);
        }

        if ($activity->user_id !== Auth::id()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'Unauthorized to delete this activity',
                ],
                'data' => null,
            ], 403);
        }

        $activity->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Activity deleted successfully',
            ],
            'data' => null,
        ]);
    }
}
