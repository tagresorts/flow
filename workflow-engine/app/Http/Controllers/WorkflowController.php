<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\FormTemplate;
use App\Models\Form;
use App\Models\FormField;
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

    /**
     * Attach a form template to this workflow (stored in visual_config.meta.form_template_id).
     */
    public function setFormTemplate(Request $request, Workflow $workflow)
    {
        $data = $request->validate([
            'form_template_id' => 'required|integer|exists:form_templates,id',
        ]);

        // Store link in visual_config.meta
        $visual = $workflow->visual_config ?? [];
        $visual['meta'] = $visual['meta'] ?? [];
        $visual['meta']['form_template_id'] = $data['form_template_id'];

        // Publish template to Form & FormFields for runtime usage
        $template = FormTemplate::findOrFail($data['form_template_id']);
        $schema = $template->json_schema ?? ['fields' => []];

        $form = Form::updateOrCreate(
            ['workflow_id' => $workflow->id],
            [
                'name' => $template->name,
                'description' => 'Published from template #' . $template->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'is_active' => true,
            ]
        );

        // Recreate fields
        FormField::where('form_id', $form->id)->delete();
        foreach (($schema['fields'] ?? []) as $f) {
            FormField::create([
                'form_id' => $form->id,
                'label' => $f['label'] ?? ucfirst($f['variable_name'] ?? $f['type'] ?? 'Field'),
                'type' => $f['type'] ?? 'text',
                'variable_name' => $f['variable_name'] ?? null,
                'options' => $f['options'] ?? [],
                'default_value' => $f['default_value'] ?? null,
                'is_required' => (bool)($f['is_required'] ?? false),
                'validation_rules' => null,
            ]);
        }

        $visual['meta']['form_id'] = $form->id;

        $workflow->update([
            'visual_config' => $visual,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json($workflow->fresh(), 200);
    }

    /**
     * Get linked Form Template for this workflow (if any).
     */
    public function getFormTemplate(Workflow $workflow)
    {
        $visual = $workflow->visual_config ?? [];
        $templateId = $visual['meta']['form_template_id'] ?? null;
        if (! $templateId) {
            return response()->json(null);
        }
        $template = \App\Models\FormTemplate::find($templateId);
        return response()->json($template);
    }

    /**
     * Get the published Form (with fields) for a workflow
     */
    public function getForm(Workflow $workflow)
    {
        $form = Form::with('fields')->where('workflow_id', $workflow->id)->first();
        return response()->json($form);
    }
}
