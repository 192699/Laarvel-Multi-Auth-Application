@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h1>Admin Dashboard</h1>

<div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0;">
    <p><strong>Welcome to the Admin Dashboard!</strong></p>
    <p>This dashboard shows real-time user presence tracking. Users appear as "Online" when they're actively using the application and "Offline" when they're not.</p>
    <p><small>Total registered users: {{ $totalUsers }}</small></p>
</div>

<h2>User Presence (Real-time)</h2>
<p style="color: #666; margin-bottom: 15px;">
    <small>This section updates automatically when users log in/out or close their browser. Presence is tracked via WebSocket connections.</small>
</p>

<div id="presence-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div>
        <h3 style="color: #27ae60; margin-bottom: 10px;">Online Users ({{ $onlineUsers->count() }})</h3>
        <div id="online-users" style="min-height: 100px; padding: 10px; background: #f0f9f4; border: 1px solid #27ae60; border-radius: 4px;">
            @if($onlineUsers->count() > 0)
                @foreach($onlineUsers as $user)
                    @if($user)
                        <div class="user-item" data-user-id="{{ $user->id }}" style="padding: 8px; margin: 5px 0; background: white; border-radius: 3px;">
                            <strong>{{ $user->name }}</strong> ({{ $user->email }})<br>
                            <small style="color: #666;">Role: {{ $user->role }}</small>
                        </div>
                    @endif
                @endforeach
            @else
                <p style="color: #999; font-style: italic;">No users currently online</p>
            @endif
        </div>
    </div>

    <div>
        <h3 style="color: #95a5a6; margin-bottom: 10px;">Offline Users ({{ $offlineUsers->count() }})</h3>
        <div id="offline-users" style="min-height: 100px; padding: 10px; background: #f8f9fa; border: 1px solid #95a5a6; border-radius: 4px;">
            @forelse($offlineUsers as $user)
                <div class="user-item" data-user-id="{{ $user->id }}" style="padding: 8px; margin: 5px 0; background: white; border-radius: 3px; opacity: 0.7;">
                    <strong>{{ $user->name }}</strong> ({{ $user->email }})<br>
                    <small style="color: #666;">Role: {{ $user->role }}</small>
                </div>
            @empty
                <p style="color: #999; font-style: italic;">No users marked as offline yet</p>
                <p style="color: #666; font-size: 12px; margin-top: 10px;">
                    <em>Note: Users will appear here once they've logged in at least once and their presence has been tracked.</em>
                </p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        if (!window.appPusher) {
            console.error('Pusher not initialized');
            return;
        }

        // Get the already-subscribed channel from the global instance
        const channel = window.appPusher.channel('presence') || window.appPusher.subscribe('presence');

        channel.bind('pusher:subscription_succeeded', function (members) {
            console.log('✅ Subscribed to presence channel');
            console.log('Members object:', members);
            console.log('Members count:', members.count);
            console.log('Members keys:', Object.keys(members.members || {}));
            
            // Update the UI with current members from Pusher
            const onlineContainer = document.getElementById('online-users');
            const offlineContainer = document.getElementById('offline-users');
            
            if (onlineContainer && offlineContainer) {
                // Don't clear existing database-loaded users, just add/update with Pusher members
                const memberCount = members.count || 0;
                console.log('Processing', memberCount, 'members from presence channel');
                
                if (memberCount > 0 && members.forEach) {
                    // Add/update members from Pusher presence channel
                    members.forEach(function(member) {
                        const userId = member.id;
                        const existingItem = onlineContainer.querySelector(`[data-user-id="${userId}"]`);
                        
                        if (!existingItem) {
                            // Add new member if not already in list
                            const div = document.createElement('div');
                            div.className = 'user-item';
                            div.setAttribute('data-user-id', userId);
                            div.style.cssText = 'padding: 8px; margin: 5px 0; background: white; border-radius: 3px;';
                            div.innerHTML = `<strong>${member.info.name}</strong> (${member.info.email})<br><small style="color: #666;">Role: ${member.info.role}</small>`;
                            onlineContainer.appendChild(div);
                            
                            // Remove "No users currently online" message if present
                            const emptyMsg = onlineContainer.querySelector('p[style*="font-style: italic"]');
                            if (emptyMsg) {
                                emptyMsg.remove();
                            }
                        }
                    });
                } else {
                    console.log('No members in presence channel or forEach not available');
                }
            }
        });
        
        channel.bind('pusher:member_added', function(member) {
            console.log('User joined:', member.info);
            const onlineContainer = document.getElementById('online-users');
            if (onlineContainer) {
                // Check if user already exists
                const existingItem = onlineContainer.querySelector(`[data-user-id="${member.id}"]`);
                if (!existingItem) {
                    const div = document.createElement('div');
                    div.className = 'user-item';
                    div.setAttribute('data-user-id', member.id);
                    div.style.cssText = 'padding: 8px; margin: 5px 0; background: white; border-radius: 3px;';
                    div.innerHTML = `<strong>${member.info.name}</strong> (${member.info.email})<br><small style="color: #666;">Role: ${member.info.role}</small>`;
                    onlineContainer.appendChild(div);
                    
                    // Remove "No users currently online" message if present
                    const emptyMsg = onlineContainer.querySelector('p[style*="font-style: italic"]');
                    if (emptyMsg) {
                        emptyMsg.remove();
                    }
                }
            }
        });
        
        channel.bind('pusher:member_removed', function(member) {
            console.log('User left:', member.info);
            const onlineContainer = document.getElementById('online-users');
            const offlineContainer = document.getElementById('offline-users');
            
            if (onlineContainer) {
                const userItem = onlineContainer.querySelector(`[data-user-id="${member.id}"]`);
                if (userItem) {
                    userItem.remove();
                    
                    // Show empty message if no users left
                    if (onlineContainer.children.length === 0) {
                        onlineContainer.innerHTML = '<p style="color: #999; font-style: italic;">No users currently online</p>';
                    }
                }
            }
        });

        channel.bind('user.presence.changed', function (data) {
            const userItem = document.querySelector(`[data-user-id="${data.user_id}"]`);
            const onlineContainer = document.getElementById('online-users');
            const offlineContainer = document.getElementById('offline-users');

            if (!onlineContainer || !offlineContainer) {
                return;
            }

            if (data.status === 'online') {
                if (userItem && userItem.parentElement === offlineContainer) {
                    userItem.remove();
                }
                if (!userItem) {
                    const div = document.createElement('div');
                    div.className = 'user-item';
                    div.setAttribute('data-user-id', data.user_id);
                    div.innerHTML = `<strong>${data.name}</strong> (${data.email}) - ${data.role}`;
                    onlineContainer.appendChild(div);
                }
            } else {
                if (userItem && userItem.parentElement === onlineContainer) {
                    userItem.remove();
                }
                if (!userItem) {
                    const div = document.createElement('div');
                    div.className = 'user-item';
                    div.setAttribute('data-user-id', data.user_id);
                    div.innerHTML = `<strong>${data.name}</strong> (${data.email}) - ${data.role}`;
                    offlineContainer.appendChild(div);
                }
            }
        });
    })();
</script>
@endpush
@endsection

