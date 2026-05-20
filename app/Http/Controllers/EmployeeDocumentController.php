<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request)
    {
        // This is a placeholder controller for UI Task 247
        // Return dummy data or empty collection for now so the UI can render
        $documents = collect([]);

        return view('employee-documents.index', compact('documents'));
    }
}
