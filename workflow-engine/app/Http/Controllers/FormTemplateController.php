<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function index()
    {
        return FormTemplate::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'json_schema' => 'required|array',
        ]);

        $template = FormTemplate::create([
            'name' => $data['name'],
            'json_schema' => $data['json_schema'],
            'created_by' => $request->user()->id,
        ]);

        return response()->json($template, 201);
    }
}

