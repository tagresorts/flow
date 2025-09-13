<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'visual_config',
        'created_by',
        'updated_by',
        'is_active',
        'version',
    ];

    protected $casts = [
        'visual_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }

    public function versions()
    {
        return $this->hasMany(WorkflowVersion::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
