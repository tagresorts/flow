<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_id',
        'user_id',
        'role_id',
        'approval_type',
        'escalation_step_id',
    ];

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function escalationStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'escalation_step_id');
    }
}
