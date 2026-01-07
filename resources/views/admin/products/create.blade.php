@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<h1>Create Product</h1>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description">{{ old('description') }}</textarea>
    </div>

    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" required>
    </div>

    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" name="image" id="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="category">Category</label>
        <input type="text" name="category" id="category" value="{{ old('category') }}">
    </div>

    <div class="form-group">
        <label for="stock">Stock *</label>
        <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', 0) }}" required>
    </div>

    <button type="submit" class="btn btn-success">Create Product</button>
    <a href="{{ route('admin.products.index') }}" class="btn">Cancel</a>
</form>
@endsection

