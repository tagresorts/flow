<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'step_id',
        'status',
        'assigned_to',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
