<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateActivityRegistrationRequest;
use App\Models\ActivityParticipant;
use App\Models\ActivityRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
        ]);

        $registrations = ActivityRegistration::with([
            'user' => function ($q) {
                $q->select('id', 'first_name', 'last_name')
                    ->with(['profile:id,user_id,profile_images,role,university']);
            }
        ])
            ->where('activity_id', $request->activity_id)
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'activity_id' => $registration->activity_id,
                    'status' => $registration->status,
                    'created_at' => $registration->created_at,
                    'user' => [
                        'id' => $registration->user->id,
                        'name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                        'profile' => [
                            'profile_images' => $registration->user->profile->profile_images ?? null,
                            'role' => $registration->user->profile->role ?? null,
                            'university' => $registration->user->profile->university ?? null,
                        ],
                    ],
                ];
            });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Registrations retrieved successfully',
            ],
            'data' => $registrations,
        ], 200);
    }

    public function store(CreateActivityRegistrationRequest $request)
    {
        $registration = ActivityRegistration::create([
            'activity_id' => $request->activity_id,
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
                'message' => 'Registration created successfully',
            ],
            'data' => $registration,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $registration = ActivityRegistration::with('activity')->findOrFail($id);

        if ($registration->activity->user_id !== auth()->id()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'You are not authorized to update this registration',
                ],
            ], 403);
        }

        if ($request->status === 'accepted') {
            $registration->activity->participants()->syncWithoutDetaching([$registration->user_id]);

            Notification::create([
                'user_id' => $registration->user_id,
                'activity_id' => $registration->activity_id,
                'type' => 'accepted',
                'message' => "Your registration for {$registration->activity->title} has been accepted!",
            ]);

            $registration->delete();

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'statusCode' => 200,
                    'message' => 'Registration accepted and removed from registrations',
                ],
            ], 200);
        }

        if ($request->status === 'rejected') {
            Notification::create([
                'user_id' => $registration->user_id,
                'activity_id' => $registration->activity_id,
                'type' => 'rejected',
                'message' => "Your registration for {$registration->activity->title} has been rejected.",
            ]);

            $registration->delete();

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'statusCode' => 200,
                    'message' => 'Registration rejected and removed from registrations',
                ],
            ], 200);
        }

        $registration->status = 'pending';
        $registration->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Registration set to pending',
            ],
            'data' => $registration,
        ], 200);
    }

    public function checkApplied($activityId)
    {
        $user = Auth::user();

        $applied = ActivityRegistration::where('activity_id', $activityId)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => $applied ? 'User has applied' : 'User has not applied',
            ],
            'data' => [
                'applied' => $applied,
            ]
        ], 200);
    }
}
