<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 1rem 0; }
        nav { display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; text-decoration: none; margin: 0 10px; }
        nav a:hover { text-decoration: underline; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f8f9fa; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group textarea { min-height: 100px; }
        .errors { color: #e74c3c; margin: 10px 0; }
        .errors ul { list-style: none; }
    </style>
    @stack('styles')
</head>
<body>
    <header>
        <nav class="container">
            <div>
                <a href="/">Home</a>
                @auth('admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                    <a href="{{ route('admin.products.index') }}">Products</a>
                @endauth
                @auth('customer')
                    <a href="{{ route('customer.dashboard') }}">Customer Dashboard</a>
                @endauth
            </div>
            <div>
                @auth('admin')
                    <span>Admin: {{ auth('admin')->user()->name }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn" style="margin-left: 10px;">Logout</button>
                    </form>
                @endauth
                @auth('customer')
                    <span>Customer: {{ auth('customer')->user()->name }}</span>
                    <form action="{{ route('customer.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn" style="margin-left: 10px;">Logout</button>
                    </form>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @if(auth('admin')->check() || auth('customer')->check())
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>
            // Initialize global Pusher instance for authenticated users (admins and customers)
            window.appPusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                authEndpoint: '{{ url('/broadcasting/auth') }}',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            });

            // Automatically subscribe to presence channel for all authenticated users
            const presenceChannel = window.appPusher.subscribe('presence');
            
            presenceChannel.bind('pusher:subscription_succeeded', function(members) {
                console.log('✅ Subscribed to presence channel. Total members:', members.count);
                console.log('Current members:', members);
            });

            presenceChannel.bind('pusher:subscription_error', function(error) {
                console.error('❌ Presence channel subscription error:', error);
            });

            presenceChannel.bind('pusher:member_added', function(member) {
                console.log('👤 User joined presence channel:', member.info);
            });

            presenceChannel.bind('pusher:member_removed', function(member) {
                console.log('👋 User left presence channel:', member.info);
            });
            
            // Debug: Log when channel state changes
            presenceChannel.bind('pusher:subscription_count', function(data) {
                console.log('📊 Presence channel subscription count:', data.subscription_count);
            });

            // Notify backend when user closes the tab / window to mark them offline
            window.addEventListener('beforeunload', function () {
                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    navigator.sendBeacon('{{ url('/presence/disconnect') }}', formData);
                } catch (e) {
                    // Best-effort only; ignore failures
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>

