<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'workflow_version_id',
        'requester_id',
        'status',
        'current_step_id',
        'priority',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function workflowVersion()
    {
        return $this->belongsTo(WorkflowVersion::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function formData()
    {
        return $this->hasMany(RequestFormData::class);
    }

    public function steps()
    {
        return $this->hasMany(RequestStep::class);
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(RequestAuditLog::class);
    }
}
