<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use App\Services\ApprovalInboxService;
use App\Services\WorkflowExecutionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalInboxController extends Controller
{
    public function __construct(
        protected ApprovalInboxService $approvalInboxService,
        protected WorkflowExecutionService $workflowExecutionService
    ) {}

    /**
     * Display a listing of the pending approvals.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $status = $request->query('status', 'pending');
        $moduleKey = $request->query('module_key');

        $filters = [
            'status' => $status,
        ];

        if ($moduleKey) {
            $filters['module_key'] = $moduleKey;
        }

        // Fetch instances assigned to this user/roles/permissions
        $instances = $this->approvalInboxService->pendingFor($user, $filters);

        return view('approvals.index', compact('instances', 'status', 'moduleKey'));
    }

    /**
     * Display the specified approval detail and steps history.
     */
    public function show(WorkflowInstance $instance, Request $request): View
    {
        $user = $request->user();

        // Multi-tenant scoping
        abort_unless($instance->company_id === $user->company_id, 403);

        $instance->load([
            'workflow.steps',
            'actions.actedBy',
            'actions.workflowStep',
            'requestedBy',
            'currentStep',
        ]);

        // Determine if current logged-in user can decide on this step
        $canAct = false;
        try {
            $this->workflowExecutionService->ensureActorCanAct($instance, $user);
            $canAct = true;
        } catch (AuthorizationException $e) {
            $canAct = false;
        }

        return view('approvals.show', compact('instance', 'canAct'));
    }

    /**
     * Process decision on workflow instance (approve, reject, or return).
     */
    public function decide(Request $request, WorkflowInstance $instance): RedirectResponse
    {
        $user = $request->user();

        // Multi-tenant scoping
        abort_unless($instance->company_id === $user->company_id, 403);

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject,return'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $comment = $validated['comment'] ?? null;

            if ($validated['action'] === 'approve') {
                $this->workflowExecutionService->approve($instance, $user, $comment);
                $successMessage = app()->getLocale() === 'ar' ? 'تمت الموافقة على الطلب بنجاح.' : 'Request approved successfully.';
            } elseif ($validated['action'] === 'reject') {
                $this->workflowExecutionService->reject($instance, $user, $comment);
                $successMessage = app()->getLocale() === 'ar' ? 'تم رفض الطلب بنجاح.' : 'Request rejected successfully.';
            } else {
                $this->workflowExecutionService->returnBack($instance, $user, $comment);
                $successMessage = app()->getLocale() === 'ar' ? 'تم إرجاع الطلب للمراجعة.' : 'Request returned for review.';
            }

            return redirect()->route('approvals.index')->with('success', $successMessage);

        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
