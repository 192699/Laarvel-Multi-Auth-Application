@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
    <h1 style="margin:0;">Products Management</h1>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-success">Create Product</a>
        <a href="{{ route('admin.products.import') }}" class="btn">Import Products</a>
    </div>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:10px 15px 5px 15px;">
    <table style="margin:0;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->category ?? 'N/A' }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <a href="{{ route('admin.products.show', $product) }}" class="btn">View</a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn">Edit</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $products->links() }}
    </div>
</div>

@push('styles')
<style>
    .pagination-wrapper {
        margin-top: 16px;
        display: flex;
        justify-content: flex-end;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 14px;
    }

    .pagination li {
        display: inline-flex;
    }

    .pagination a,
    .pagination span {
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        color: #374151;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination a:hover {
        background-color: #f3f4f6;
        border-color: #2563eb;
        color: #2563eb;
    }

    .pagination .active span {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        font-weight: 600;
    }

    .pagination .disabled span {
        background-color: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }
</style>

@endpush
@endsection

