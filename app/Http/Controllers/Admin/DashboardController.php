<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPresence;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ensure current admin is marked as online
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            UserPresence::updateOrCreate(
                ['user_id' => $currentAdmin->id],
                [
                    'status' => 'online',
                    'last_seen_at' => now(),
                ]
            );
        }

        // Get users with online status using join to ensure we get valid users
        $onlineUsers = User::join('user_presence', 'users.id', '=', 'user_presence.user_id')
            ->where('user_presence.status', 'online')
            ->select('users.*')
            ->distinct()
            ->get();

        // Get users with offline status using join
        $offlineUsers = User::join('user_presence', 'users.id', '=', 'user_presence.user_id')
            ->where('user_presence.status', 'offline')
            ->select('users.*')
            ->distinct()
            ->get();

        // Get total user count for reference
        $totalUsers = User::count();

        return view('admin.dashboard', compact('onlineUsers', 'offlineUsers', 'totalUsers'));
    }
}

