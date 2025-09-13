<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserDirectoryController extends Controller
{
    public function users(Request $request)
    {
        $q = $request->string('q')->toString();
        $query = User::query()->select('id','name','email','username')->orderBy('name');
        if ($q) {
            $query->where(function($s) use ($q) {
                $s->where('name','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhere('username','like',"%$q%");
            });
        }
        return $query->limit(50)->get();
    }
}

