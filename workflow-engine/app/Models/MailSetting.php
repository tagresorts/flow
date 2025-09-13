<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'provider','host','port','encryption','username','password_encrypted','from_address','from_name','is_active',
    ];
}

