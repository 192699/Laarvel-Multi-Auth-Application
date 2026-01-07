@extends('layouts.app')

@section('title', 'Import Products')

@section('content')
<h1>Import Products</h1>

<p>Upload a CSV or Excel file with up to 100,000 products. The file should have the following columns:</p>
<ul>
    <li>name (required)</li>
    <li>description (optional)</li>
    <li>price (required)</li>
    <li>image (optional - if not provided, default image will be used)</li>
    <li>category (optional)</li>
    <li>stock (optional, defaults to 0)</li>
</ul>

<form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="file">CSV/Excel File *</label>
        <input type="file" name="file" id="file" accept=".csv,.xlsx,.xls" required>
        <small>Maximum file size: 10MB</small>
    </div>

    <button type="submit" class="btn btn-success">Import Products</button>
    <a href="{{ route('admin.products.index') }}" class="btn">Cancel</a>
</form>

<p style="margin-top: 20px;">
    <strong>Note:</strong> The import will be processed in the background. Products will be imported in chunks to prevent timeouts.
</p>
@endsection

