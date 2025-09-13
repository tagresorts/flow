<?php

namespace App\Http\Controllers;

use App\Models\RequestStep;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the pending approvals for the authenticated user.
     */
    public function index()
    {
        // This is a simplified query. A real implementation would also check for role-based approvals.
        return RequestStep::where('assigned_to', auth()->id())
            ->where('status', 'Pending')
            ->with('request.workflow')
            ->latest()
            ->paginate(10);
    }

    /**
     * Approve a request step.
     */
    public function approve(Request $request, RequestStep $requestStep)
    {
        $this->authorize('approve', $requestStep);

        $requestStep->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        // Here you would trigger the next step in the workflow.
        // This could involve finding the next_step_id, creating a new RequestStep, and notifying the next approver.
        // If there is no next step, the request itself would be marked as 'Approved'.

        // Notify next approver or requester (placeholder email)
        if ($requestStep->request) {
            $requester = $requestStep->request->requester; // could be null on simplified data
            if ($requester) {
                Notification::route('mail', $requester->email)->notify(new class extends \Illuminate\Notifications\Notification {
                    public function via($notifiable) { return ['mail']; }
                    public function toMail($notifiable) { return (new MailMessage)->subject('Request approved')->line('Your request has been approved.'); }
                });
            }
        }

        return response()->json(['message' => 'Request approved.']);
    }

    /**
     * Reject a request step.
     */
    public function reject(Request $request, RequestStep $requestStep)
    {
        $this->authorize('reject', $requestStep);

        $requestStep->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(), // or 'rejected_by' if you add that to the table
            'approved_at' => now(), // or 'rejected_at'
            'notes' => $request->input('notes'),
        ]);

        // When a request is rejected, the entire request is usually marked as 'Rejected'.
        $requestStep->request()->update(['status' => 'Rejected']);

        // Notify requester
        if ($requestStep->request) {
            $requester = $requestStep->request->requester;
            if ($requester) {
                Notification::route('mail', $requester->email)->notify(new class extends \Illuminate\Notifications\Notification {
                    public function via($notifiable) { return ['mail']; }
                    public function toMail($notifiable) { return (new MailMessage)->subject('Request rejected')->line('Your request has been rejected.'); }
                });
            }
        }

        return response()->json(['message' => 'Request rejected.']);
    }

    /**
     * Reassign an approval step to another user (admin takeover)
     */
    public function reassign(Request $request, RequestStep $requestStep)
    {
        $this->authorize('update', $requestStep);
        $data = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $requestStep->update(['assigned_to' => $data['assigned_to']]);
        return response()->json(['message' => 'Request step reassigned.']);
    }
}
