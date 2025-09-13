<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Workflow::with('createdBy')->latest()->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This would typically return a view in a web context
        return response()->json(['message' => 'Form for creating a workflow.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workflow = Workflow::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json($workflow, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Workflow $workflow)
    {
        return $workflow->load('steps.approvers', 'versions');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workflow $workflow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workflow $workflow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workflow $workflow)
    {
        //
    }
}
