<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestFormData extends Model
{
    use HasFactory;

    protected $table = 'request_form_data';

    protected $fillable = [
        'request_id',
        'form_field_id',
        'value',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function formField()
    {
        return $this->belongsTo(FormField::class);
    }
}
