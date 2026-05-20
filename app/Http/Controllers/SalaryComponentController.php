<?php

namespace App\Http\Controllers;

use App\Actions\CreateSalaryComponent;
use App\Actions\UpdateSalaryComponent;
use App\Http\Requests\IndexSalaryComponentRequest;
use App\Http\Requests\StoreSalaryComponentRequest;
use App\Http\Requests\UpdateSalaryComponentRequest;
use App\Http\Resources\SalaryComponentResource;
use App\Models\SalaryComponent;
use Illuminate\Support\Facades\Gate;

class SalaryComponentController extends Controller
{
    public function index(IndexSalaryComponentRequest $request)
    {
        Gate::authorize('viewAny', SalaryComponent::class);
        $filters = $request->validated();

        $components = SalaryComponent::query()
            ->forCurrentCompany()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            }))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate();

        if ($request->expectsJson()) {
            return SalaryComponentResource::collection($components);
        }

        return view('salary-components.index', compact('components'));
    }

    public function create()
    {
        Gate::authorize('create', SalaryComponent::class);

        return view('salary-components.create');
    }

    public function store(StoreSalaryComponentRequest $request, CreateSalaryComponent $action)
    {
        $component = $action->handle($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return SalaryComponentResource::make($component);
        }

        return redirect()->route('salary-components.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المكون بنجاح.' : 'Salary component created.');
    }

    public function show(SalaryComponent $salaryComponent)
    {
        Gate::authorize('view', $salaryComponent);

        if (request()->expectsJson()) {
            return SalaryComponentResource::make($salaryComponent);
        }

        return redirect()->route('salary-components.edit', $salaryComponent->id);
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        Gate::authorize('update', $salaryComponent);

        return view('salary-components.edit', compact('salaryComponent'));
    }

    public function update(UpdateSalaryComponentRequest $request, SalaryComponent $salaryComponent, UpdateSalaryComponent $action)
    {
        $result = $action->handle($salaryComponent, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return SalaryComponentResource::make($result);
        }

        return redirect()->route('salary-components.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المكون بنجاح.' : 'Salary component updated.');
    }

    public function destroy(SalaryComponent $salaryComponent): never
    {
        abort(405);
    }
}
