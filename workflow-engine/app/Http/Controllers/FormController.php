<?php

namespace App\Http\Controllers;

use App\Models\Form;

class FormController extends Controller
{
    public function index()
    {
        return Form::with('workflow', 'fields')->latest()->paginate(20);
    }
}

