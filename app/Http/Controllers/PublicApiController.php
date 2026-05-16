<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\SalesInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function company(Request $request): JsonResponse
    {
        $company = $request->attributes->get('company');

        return response()->json([
            'data' => [
                'id' => $company->id,
                'name' => $company->name,
                'legal_name' => $company->legal_name,
                'email' => $company->email,
                'phone' => $company->phone,
                'locale' => $company->locale,
                'timezone' => $company->timezone,
                'currency' => $company->currency,
            ],
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('company_id');

        return response()->json([
            'data' => Customer::query()
                ->forCompany($companyId)
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (Customer $customer): array => [
                    'id' => $customer->id,
                    'name_ar' => $customer->name_ar,
                    'name_en' => $customer->name_en,
                    'code' => $customer->code,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status?->value,
                ]),
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('company_id');

        return response()->json([
            'data' => SalesInvoice::query()
                ->forCompany($companyId)
                ->with('customer')
                ->latest('invoice_date')
                ->limit(50)
                ->get()
                ->map(fn (SalesInvoice $invoice): array => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date?->toDateString(),
                    'due_date' => $invoice->due_date?->toDateString(),
                    'customer' => $invoice->customer?->name_ar,
                    'status' => $invoice->status?->value,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                    'currency' => $invoice->currency,
                ]),
        ]);
    }

    public function projects(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('company_id');

        return response()->json([
            'data' => Project::query()
                ->forCompany($companyId)
                ->with('customer')
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (Project $project): array => [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name_ar' => $project->name_ar,
                    'name_en' => $project->name_en,
                    'customer' => $project->customer?->name_ar,
                    'status' => $project->status?->value,
                    'priority' => $project->priority?->value,
                    'progress_percentage' => $project->progress_percentage,
                ]),
        ]);
    }
}
