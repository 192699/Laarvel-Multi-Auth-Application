<?php

namespace App\Listeners;

use App\Events\UserPresenceChanged;
use App\Models\UserPresence;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateUserPresence implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserPresenceChanged $event): void
    {
        UserPresence::updateOrCreate(
            ['user_id' => $event->user->id],
            [
                'status' => $event->status,
                'last_seen_at' => now(),
            ]
        );
    }
}

