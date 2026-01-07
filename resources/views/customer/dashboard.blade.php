@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
<div style="margin-bottom: 30px;">
    <h1 style="margin-bottom: 10px;">Customer Dashboard</h1>
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; color: white;">Welcome, {{ $user->name }}! 👋</h2>
        <p style="margin: 0; opacity: 0.9;">Thank you for being part of our platform. Here's what you can do.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #3498db;">
        <h3 style="margin: 0 0 10px 0; color: #3498db;">📦 Available Products</h3>
        <p style="font-size: 32px; margin: 0; font-weight: bold; color: #2c3e50;">{{ $totalProducts }}</p>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Products in our catalog</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #27ae60;">
        <h3 style="margin: 0 0 10px 0; color: #27ae60;">👤 Your Account</h3>
        <p style="margin: 5px 0; color: #2c3e50;"><strong>Email:</strong> {{ $user->email }}</p>
        <p style="margin: 5px 0; color: #2c3e50;"><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #e74c3c;">
        <h3 style="margin: 0 0 10px 0; color: #e74c3c;">ℹ️ Information</h3>
        <p style="margin: 0; color: #666; font-size: 14px;">Browse our product catalog and explore what we have to offer.</p>
    </div>
</div>

@if($featuredProducts->count() > 0)
<div style="margin-top: 30px;">
    <h2 style="margin-bottom: 20px;">Featured Products</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
        @foreach($featuredProducts as $product)
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; transition: transform 0.2s, box-shadow 0.2s;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <div style="background: #f3f4f6; height: 120px; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; color: #9ca3af;">
                @if($product->image && $product->image !== 'products/default.png')
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width: 100%; max-height: 120px; object-fit: cover; border-radius: 4px;">
                @else
                    <span>📦</span>
                @endif
            </div>
            <h4 style="margin: 0 0 8px 0; font-size: 16px; color: #2c3e50;">{{ Str::limit($product->name, 30) }}</h4>
            <p style="margin: 0 0 8px 0; color: #666; font-size: 14px;">
                <strong style="color: #27ae60; font-size: 18px;">${{ number_format($product->price, 2) }}</strong>
            </p>
            @if($product->category)
                <p style="margin: 0 0 10px 0; color: #9ca3af; font-size: 12px;">{{ $product->category }}</p>
            @endif
            <p style="margin: 0; font-size: 12px; color: {{ $product->stock > 0 ? '#27ae60' : '#e74c3c' }};">
                Stock: {{ $product->stock }}
            </p>
        </div>
        @endforeach
    </div>
</div>
@else
<div style="background: #f8f9fa; padding: 30px; border-radius: 8px; text-align: center; margin-top: 30px;">
    <p style="color: #666; margin: 0;">No products available at the moment. Check back later!</p>
</div>
@endif

<div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <h3 style="margin-top: 0;">Quick Actions</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="/" class="btn" style="text-decoration: none;">🏠 Go to Home</a>
        <p style="margin: 0; padding: 10px 20px; background: #e5e7eb; border-radius: 4px; color: #666;">
            💡 Tip: Contact admin for product inquiries
        </p>
    </div>
</div>
@endsection

