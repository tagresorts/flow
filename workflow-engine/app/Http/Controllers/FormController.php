<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use App\Models\Workflow;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index()
    {
        return Form::with('workflow', 'fields')->latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workflow_id' => 'required|exists:workflows,id',
            'fields' => 'required|array',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string|in:text,number,date,select,radio,checkbox,file,textarea',
            'fields.*.variable_name' => 'required|string',
            'fields.*.options' => 'nullable|array',
            'fields.*.default_value' => 'nullable|string',
            'fields.*.is_required' => 'boolean',
        ]);

        $form = Form::create([
            'workflow_id' => $data['workflow_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'is_active' => true,
        ]);

        // Create form fields
        foreach ($data['fields'] as $fieldData) {
            FormField::create([
                'form_id' => $form->id,
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'variable_name' => $fieldData['variable_name'],
                'options' => $fieldData['options'] ?? [],
                'default_value' => $fieldData['default_value'] ?? null,
                'is_required' => $fieldData['is_required'] ?? false,
                'validation_rules' => null,
            ]);
        }

        return response()->json($form->load('workflow', 'fields'), 201);
    }
}

