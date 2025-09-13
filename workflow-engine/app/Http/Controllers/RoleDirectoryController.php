<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleDirectoryController extends Controller
{
    public function roles(Request $request)
    {
        // Prefer Spatie roles; fallback not implemented here
        $roles = \Spatie\Permission\Models\Role::query()->select('id','name','guard_name')->orderBy('name')->get();
        return $roles;
    }
}

