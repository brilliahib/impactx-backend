<?php

namespace App\Http\Controllers;

use App\Models\ActivityParticipant;
use Illuminate\Http\Request;

class ActivityParticipantController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
        ]);

        $participants = ActivityParticipant::with([
            'user' => function ($q) {
                $q->select('id', 'first_name', 'last_name')
                    ->with(['profile:id,user_id,profile_images,role,university']);
            }
        ])
            ->where('activity_id', $request->activity_id)
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'activity_id' => $participant->activity_id,
                    'created_at' => $participant->created_at,
                    'user' => [
                        'id' => $participant->user->id,
                        'name' => $participant->user->first_name . ' ' . $participant->user->last_name,
                        'profile' => [
                            'profile_images' => $participant->user->profile->profile_images ?? null,
                            'role' => $participant->user->profile->role ?? null,
                            'university' => $participant->user->profile->university ?? null,
                        ],
                    ],
                ];
            });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Participants retrieved successfully',
            ],
            'data' => $participants,
        ], 200);
    }

    public function destroy($userId)
    {
        $participant = ActivityParticipant::where('user_id', $userId)->first();

        if (!$participant) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Participant not found',
                ],
                'data' => null,
            ], 404);
        }

        if ($participant->activity->user_id !== auth()->id()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'You are not authorized to remove this participant',
                ],
                'data' => null,
            ], 403);
        }

        $participant->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Participant removed successfully',
            ],
            'data' => null,
        ], 200);
    }
}
