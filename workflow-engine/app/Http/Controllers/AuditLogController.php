<?php

namespace App\Http\Controllers;

use App\Models\RequestAuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $query = RequestAuditLog::with('request.workflow', 'actor')->latest('timestamp');
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', "%$q%")
                    ->orWhereHas('actor', fn ($a) => $a->where('name', 'like', "%$q%"))
                    ->orWhereHas('request.workflow', fn ($w) => $w->where('name', 'like', "%$q%"));
            });
        }
        return $query->paginate(20);
    }
}

