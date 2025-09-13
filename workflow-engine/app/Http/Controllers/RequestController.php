<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return WorkflowRequest::where('requester_id', auth()->id())->with('workflow')->latest()->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // In a real app, you'd probably return a view with a list of available workflows
        return response()->json(['message' => 'Form for creating a request.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'form_data' => 'required|array',
        ]);

        // This is a simplified implementation. A real implementation would be more complex.
        // It would need to find the first step of the workflow and assign it.
        $workflow = \App\Models\Workflow::find($validated['workflow_id']);

        $newRequest = WorkflowRequest::create([
            'workflow_id' => $workflow->id,
            'workflow_version_id' => $workflow->versions()->latest()->first()->id,
            'requester_id' => auth()->id(),
            'status' => 'Pending',
            'priority' => $request->input('priority', 'normal'),
        ]);

        foreach ($validated['form_data'] as $field_id => $value) {
            \App\Models\RequestFormData::create([
                'request_id' => $newRequest->id,
                'form_field_id' => $field_id,
                'value' => $value,
            ]);
        }

        // Here you would trigger the workflow, find the first step, create a RequestStep, and notify the approver.
        // For now, we'll just return the created request.

        return response()->json($newRequest->load('formData'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkflowRequest $workflowRequest)
    {
        $this->authorize('view', $workflowRequest);
        return $workflowRequest->load('formData', 'steps.approvers', 'auditLogs');
    }
}
