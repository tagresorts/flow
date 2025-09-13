<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_name',
        'type',
        'config_data',
        'sla_hours',
        'next_step_id',
        'order_index',
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function nextStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_id');
    }

    public function approvers()
    {
        return $this->hasMany(StepApprover::class, 'step_id');
    }
}
