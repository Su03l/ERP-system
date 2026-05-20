<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Project;
use App\Models\SalesInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class GlobalSearchController extends Controller
{
    /**
     * Handle the global search query.
     */
    public function search(Request $request): JsonResponse
    {
        $queryStr = $request->query('q', '');

        if (! is_string($queryStr) || trim($queryStr) === '') {
            return response()->json([
                'employees' => [],
                'projects' => [],
                'invoices' => [],
                'customers' => [],
                'documents' => [],
            ]);
        }

        $term = trim($queryStr);
        $locale = app()->getLocale();
        $isAr = $locale === 'ar';

        // 1. Employees Search
        $employees = Employee::forCurrentCompany()
            ->where(function ($q) use ($term) {
                $q->where('first_name_ar', 'like', "%{$term}%")
                    ->orWhere('last_name_ar', 'like', "%{$term}%")
                    ->orWhere('first_name_en', 'like', "%{$term}%")
                    ->orWhere('last_name_en', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('employee_number', 'like', "%{$term}%");
            })
            ->take(5)
            ->get()
            ->map(function (Employee $employee) use ($isAr) {
                $name = $isAr
                    ? trim("{$employee->first_name_ar} {$employee->last_name_ar}")
                    : trim("{$employee->first_name_en} {$employee->last_name_en}");

                if (empty($name)) {
                    $name = $isAr ? $employee->first_name_ar : $employee->first_name_en;
                }

                return [
                    'id' => $employee->id,
                    'title' => $name ?: $employee->email,
                    'subtitle' => $employee->employee_number ?: $employee->email,
                    'url' => Route::has('employees.show') ? route('employees.show', $employee->id) : '/employees/'.$employee->id,
                ];
            });

        // 2. Projects Search
        $projects = Project::forCurrentCompany()
            ->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                    ->orWhere('name_en', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            })
            ->take(5)
            ->get()
            ->map(function (Project $project) use ($isAr) {
                return [
                    'id' => $project->id,
                    'title' => $isAr ? $project->name_ar : $project->name_en,
                    'subtitle' => $project->code,
                    'url' => Route::has('projects.show') ? route('projects.show', $project->id) : '/projects/'.$project->id,
                ];
            });

        // 3. Invoices Search
        $invoices = SalesInvoice::forCurrentCompany()
            ->where('invoice_number', 'like', "%{$term}%")
            ->take(5)
            ->get()
            ->map(function (SalesInvoice $invoice) use ($isAr) {
                $formattedAmount = number_format($invoice->total_amount, 2).' '.($invoice->currency ?: 'SAR');
                $subtitle = $isAr
                    ? "قيمة: {$formattedAmount} | المستحق: {$invoice->balance_due}"
                    : "Total: {$formattedAmount} | Balance: {$invoice->balance_due}";

                return [
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'subtitle' => $subtitle,
                    'url' => Route::has('subscription-invoices.show') ? route('subscription-invoices.show', $invoice->id) : '/invoices/'.$invoice->id,
                ];
            });

        // 4. Customers Search
        $customers = Customer::forCurrentCompany()
            ->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                    ->orWhere('name_en', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->take(5)
            ->get()
            ->map(function (Customer $customer) use ($isAr) {
                return [
                    'id' => $customer->id,
                    'title' => $isAr ? $customer->name_ar : $customer->name_en,
                    'subtitle' => $customer->code ?: $customer->email,
                    'url' => '/customers/'.$customer->id,
                ];
            });

        // 5. Documents Search
        $documents = CompanyDocument::forCurrentCompany()
            ->where(function ($q) use ($term) {
                $q->where('title_ar', 'like', "%{$term}%")
                    ->orWhere('title_en', 'like', "%{$term}%");
            })
            ->take(5)
            ->get()
            ->map(function (CompanyDocument $document) use ($isAr) {
                return [
                    'id' => $document->id,
                    'title' => $isAr ? $document->title_ar : $document->title_en,
                    'subtitle' => $document->document_type ? $document->document_type->value : '',
                    'url' => '/documents/'.$document->id,
                ];
            });

        return response()->json([
            'employees' => $employees,
            'projects' => $projects,
            'invoices' => $invoices,
            'customers' => $customers,
            'documents' => $documents,
        ]);
    }
}
