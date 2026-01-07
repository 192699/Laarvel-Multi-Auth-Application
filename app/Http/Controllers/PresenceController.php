<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\UserPresenceChanged;
use App\Models\UserPresence;

class PresenceController extends Controller
{
    /**
     * Authenticate the presence channel
     */
    public function authenticate(Request $request)
    {
        // Check both admin and customer guards
        $user = Auth::guard('admin')->user() ?? Auth::guard('customer')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        if (!$socketId || !$channelName) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        // Update presence to online
        UserPresence::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'online',
                'last_seen_at' => now(),
            ]
        );

        // Broadcast presence change
        broadcast(new UserPresenceChanged($user, 'online'));

        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );

        $auth = $pusher->authorizeChannel($channelName, $socketId, [
            'user_id' => (string) $user->id,
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);

        return response($auth);
    }

    /**
     * Handle user disconnect
     */
    public function disconnect(Request $request)
    {
        // Check both admin and customer guards
        $user = Auth::guard('admin')->user() ?? Auth::guard('customer')->user();
        
        if ($user) {
            UserPresence::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'offline',
                    'last_seen_at' => now(),
                ]
            );

            broadcast(new UserPresenceChanged($user, 'offline'));
        }

        return response()->json(['status' => 'disconnected']);
    }
}

