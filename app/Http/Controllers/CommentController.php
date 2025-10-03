<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentMention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $feedId)
    {
        $data = $request->validate([
            'content'   => 'required|string',
            'mentions'  => 'array',
            'mentions.*' => 'exists:users,id',
        ]);

        $comment = Comment::create([
            'feed_id' => $feedId,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        if (!empty($data['mentions'])) {
            foreach ($data['mentions'] as $userId) {
                CommentMention::create([
                    'comment_id'        => $comment->id,
                    'mentioned_user_id' => $userId,
                ]);
            }
        }

        return response()->json([
            'meta' => [
                'status'     => true,
                'statusCode' => 201,
                'message'    => 'Comment created successfully',
            ],
            'data' => $comment->load('user', 'mentions.mentionedUser'),
        ], 201);
    }

    public function getByFeed($feedId)
    {
        $comments = Comment::with([
            'user:id,first_name,last_name',
            'user.profile:id,user_id,role,university,profile_images'
        ])
            ->where('feed_id', $feedId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($comment) {
                $user = $comment->user;
                $profile = $user && $user->profile ? $user->profile : null;

                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toDateTimeString(),
                    'user' => [
                        'first_name'     => $user->first_name ?? null,
                        'last_name'      => $user->last_name ?? null,
                        'role'           => $profile->role ?? null,
                        'university'     => $profile->university ?? null,
                        'profile_images' => $profile->profile_images ?? null,
                    ],
                ];
            });

        return response()->json([
            'meta' => [
                'status'     => true,
                'statusCode' => 200,
                'message'    => 'Comments fetched successfully',
            ],
            'data' => $comments,
        ], 200);
    }
}
