<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveJobTitle;
use App\Actions\CreateJobTitle;
use App\Actions\UpdateJobTitle;
use App\Http\Requests\IndexJobTitleRequest;
use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Http\Resources\JobTitleResource;
use App\Models\JobTitle;
use App\Services\JobTitleIndexQuery;
use Illuminate\Support\Facades\Gate;

class JobTitleController extends Controller
{
    public function index(IndexJobTitleRequest $request, JobTitleIndexQuery $query)
    {
        if ($request->expectsJson()) {
            return JobTitleResource::collection($query->paginate($request->validated()));
        }

        $jobTitles = $query->paginate($request->validated());

        return view('job-titles.index', compact('jobTitles'));
    }

    public function create()
    {
        Gate::authorize('create', JobTitle::class);

        return view('job-titles.create');
    }

    public function store(StoreJobTitleRequest $request, CreateJobTitle $action)
    {
        $jobTitle = $action->handle($request->validated(), $request->user());
        if ($request->expectsJson()) {
            return JobTitleResource::make($jobTitle);
        }

        return redirect()->route('job-titles.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المسمى الوظيفي بنجاح.' : 'Job Title created successfully.');
    }

    public function show(JobTitle $jobTitle)
    {
        Gate::authorize('view', $jobTitle);

        if (request()->expectsJson()) {
            return JobTitleResource::make($jobTitle);
        }

        return view('job-titles.show', compact('jobTitle'));
    }

    public function edit(JobTitle $jobTitle)
    {
        Gate::authorize('update', $jobTitle);

        return view('job-titles.edit', compact('jobTitle'));
    }

    public function update(UpdateJobTitleRequest $request, JobTitle $jobTitle, UpdateJobTitle $action)
    {
        $updatedJobTitle = $action->handle($jobTitle, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return JobTitleResource::make($updatedJobTitle);
        }

        return redirect()->route('job-titles.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المسمى الوظيفي بنجاح.' : 'Job Title updated successfully.');
    }

    public function destroy(JobTitle $jobTitle, ArchiveJobTitle $action)
    {
        $action->handle($jobTitle, request()->user());

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('job-titles.index')->with('success', app()->getLocale() === 'ar' ? 'تم حذف المسمى الوظيفي بنجاح.' : 'Job Title deleted successfully.');
    }
}
