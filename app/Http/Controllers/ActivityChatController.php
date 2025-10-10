<?php

namespace App\Http\Controllers;

use App\Models\ActivityChat;
use Illuminate\Http\Request;

class ActivityChatController extends Controller
{
    public function index($activityId)
    {
        $chats = ActivityChat::where('activity_id', $activityId)
            ->with([
                'user:id,first_name,last_name',
                'user.profile:id,user_id,profile_images,role,university',
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Chats retrieved successfully',
            ],
            'data' => $chats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'message' => 'required|string',
        ]);

        $chat = ActivityChat::create([
            'activity_id' => $validated['activity_id'],
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        $chat->load([
            'user:id,first_name,last_name',
            'user.profile:id,user_id,profile_images,role,university',
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
                'message' => 'Chat sent successfully',
            ],
            'data' => $chat,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $chat = ActivityChat::find($id);

        if (!$chat) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Chat not found',
                ],
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $chat->update($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Chat updated successfully',
            ],
            'data' => $chat,
        ]);
    }

    public function destroy($id)
    {
        $chat = ActivityChat::find($id);

        if (!$chat) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Chat not found',
                ],
                'data' => null,
            ], 404);
        }

        $chat->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Chat deleted successfully',
            ],
            'data' => null,
        ]);
    }
}
