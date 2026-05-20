<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexAuditLogRequest;
use App\Services\AuditLogExportQuery;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     *
     * @param IndexAuditLogRequest $request
     * @param AuditLogExportQuery $query
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(IndexAuditLogRequest $request, AuditLogExportQuery $query): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->expectsJson()) {
            return response()->json(['data' => $query->rows($request->validated(), $request->user())]);
        }

        $companyId = $request->user()?->company_id;
        $users = \App\Models\User::where('company_id', $companyId)->get();
        $actions = \App\Models\AuditLog::where('company_id', $companyId)
            ->distinct()
            ->pluck('action');

        $filters = $request->validated();

        $logs = \App\Models\AuditLog::query()
            ->where('company_id', $companyId)
            ->when($filters['user_id'] ?? null, function ($q, $userId) {
                return $q->where('user_id', $userId);
            })
            ->when($filters['action'] ?? null, function ($q, $action) {
                return $q->where('action', 'like', "%{$action}%");
            })
            ->when($filters['ip_address'] ?? null, function ($q, $ip) {
                return $q->where('ip_address', $ip);
            })
            ->when($filters['date_from'] ?? null, function ($q, $date) {
                return $q->whereDate('created_at', '>=', $date);
            })
            ->when($filters['date_to'] ?? null, function ($q, $date) {
                return $q->whereDate('created_at', '<=', $date);
            })
            ->with('user')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('audit-logs.index', compact('logs', 'users', 'actions', 'filters'));
    }
}
