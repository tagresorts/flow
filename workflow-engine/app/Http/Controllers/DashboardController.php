<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $userId = $request->user()->id;

        $myOpen = WorkflowRequest::where('requester_id', $userId)->whereIn('status', ['Pending','In Progress'])->count();
        $myApproved = WorkflowRequest::where('requester_id', $userId)->where('status', 'Approved')->count();
        $myRejected = WorkflowRequest::where('requester_id', $userId)->where('status', 'Rejected')->count();

        return [
            'open' => $myOpen,
            'approved' => $myApproved,
            'rejected' => $myRejected,
        ];
    }

    public function list(Request $request)
    {
        $userId = $request->user()->id;
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $query = WorkflowRequest::where('requester_id', $userId)->with('workflow')->latest();
        if ($status) {
            $query->where('status', $status);
        }
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)->orWhereHas('workflow', function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%");
                });
            });
        }
        return $query->paginate(10);
    }
}

