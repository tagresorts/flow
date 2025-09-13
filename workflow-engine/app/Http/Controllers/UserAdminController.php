<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index()
    {
        return User::select('id','name','email','username')->orderBy('name')->paginate(20);
    }

    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => 'required|string']);
        $user->assignRole($data['role']);
        return ['status' => 'ok'];
    }

    public function revokeRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => 'required|string']);
        $user->removeRole($data['role']);
        return ['status' => 'ok'];
    }
}

