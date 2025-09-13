<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'action',
        'actor_id',
        'timestamp',
        'metadata_json',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
