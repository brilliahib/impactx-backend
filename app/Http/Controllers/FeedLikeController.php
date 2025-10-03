<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\FeedLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedLikeController extends Controller
{
    public function toggle($feedId)
    {
        $feed = Feed::findOrFail($feedId);

        $like = FeedLike::where('feed_id', $feedId)
            ->where('user_id', Auth::id())
            ->first();

        if ($like) {
            $like->delete();

            return response()->json([
                'meta' => [
                    'status'     => true,
                    'statusCode' => 200,
                    'message'    => 'Feed unliked successfully',
                ],
                'data' => [
                    'liked' => false,
                    'total_likes' => $feed->likes()->count(),
                ]
            ], 200);
        } else {
            $like = FeedLike::create([
                'feed_id' => $feedId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'meta' => [
                    'status'     => true,
                    'statusCode' => 201,
                    'message'    => 'Feed liked successfully',
                ],
                'data' => [
                    'liked' => true,
                    'total_likes' => $feed->likes()->count(),
                ]
            ], 201);
        }
    }
}
