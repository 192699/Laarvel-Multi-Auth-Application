<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessProductImport;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    public function showImportForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        // Dispatch job to process the import
        ProcessProductImport::dispatch($path);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product import has been queued. Products will be imported in the background.');
    }
}

