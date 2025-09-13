<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowVersion;
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
        // Not used in MVP
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workflow $workflow)
    {
        // Not used in MVP
    }

    /**
     * Ensure a default workflow exists (for builder bootstrap) and return it.
     */
    public function ensureDefault(Request $request)
    {
        $workflow = Workflow::query()->first();
        if (! $workflow) {
            $workflow = Workflow::create([
                'name' => 'Untitled Workflow',
                'description' => 'Default workflow created by builder',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'is_active' => false,
                'version' => 1,
            ]);
        }

        return $workflow->refresh();
    }

    /**
     * Save visual designer config (builder canvas state) on a workflow.
     */
    public function saveVisual(Request $request, Workflow $workflow)
    {
        $data = $request->validate([
            'visual_config' => 'required|array',
        ]);

        $workflow->fill([
            'visual_config' => $data['visual_config'],
            'updated_by' => $request->user()->id,
        ])->save();

        return response()->json($workflow->fresh(), 200);
    }

    /**
     * Create a workflow version snapshot from definition JSON.
     */
    public function createVersion(Request $request, Workflow $workflow)
    {
        $data = $request->validate([
            'definition_json' => 'required|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $versionNumber = ($workflow->versions()->max('version') ?? 0) + 1;

        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id,
            'version' => $versionNumber,
            'definition_json' => $data['definition_json'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        if (! empty($data['is_active'])) {
            $workflow->update(['version' => $versionNumber, 'is_active' => true]);
        }

        return response()->json($version->load('workflow'), 201);
    }
}
