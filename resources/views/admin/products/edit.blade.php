@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<h1>Edit Product</h1>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
    </div>

    <div class="form-group">
        <label for="image">Image</label>
        @if($product->image)
            <p>Current: <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width: 100px;"></p>
        @endif
        <input type="file" name="image" id="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="category">Category</label>
        <input type="text" name="category" id="category" value="{{ old('category', $product->category) }}">
    </div>

    <div class="form-group">
        <label for="stock">Stock *</label>
        <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', $product->stock) }}" required>
    </div>

    <button type="submit" class="btn btn-success">Update Product</button>
    <a href="{{ route('admin.products.index') }}" class="btn">Cancel</a>
</form>
@endsection

