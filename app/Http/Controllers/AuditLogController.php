<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexAuditLogRequest;
use App\Services\AuditLogExportQuery;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(IndexAuditLogRequest $request, AuditLogExportQuery $query): JsonResponse
    {
        return response()->json(['data' => $query->rows($request->validated(), $request->user())]);
    }
}
