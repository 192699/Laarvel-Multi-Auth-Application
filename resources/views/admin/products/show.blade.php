@extends('layouts.app')

@section('title', 'View Product')

@section('content')
<h1>{{ $product->name }}</h1>

<div style="margin: 20px 0;">
    <p><strong>Description:</strong> {{ $product->description ?? 'N/A' }}</p>
    <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
    <p><strong>Category:</strong> {{ $product->category ?? 'N/A' }}</p>
    <p><strong>Stock:</strong> {{ $product->stock }}</p>
    @if($product->image)
        <p><strong>Image:</strong></p>
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width: 300px;">
    @endif
</div>

<a href="{{ route('admin.products.edit', $product) }}" class="btn">Edit</a>
<a href="{{ route('admin.products.index') }}" class="btn">Back to List</a>
@endsection

