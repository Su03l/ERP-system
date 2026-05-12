<?php

namespace App\Http\Controllers;

use App\Actions\ApproveJournalEntry;
use App\Actions\CreateJournalEntry;
use App\Actions\PostJournalEntry;
use App\Actions\RejectJournalEntry;
use App\Actions\ReverseJournalEntry;
use App\Actions\UpdateJournalEntry;
use App\Http\Requests\ApproveJournalEntryRequest;
use App\Http\Requests\IndexJournalEntryRequest;
use App\Http\Requests\PostJournalEntryRequest;
use App\Http\Requests\RejectJournalEntryRequest;
use App\Http\Requests\ReverseJournalEntryRequest;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class JournalEntryController extends Controller
{
    public function index(IndexJournalEntryRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', JournalEntry::class);
        $filters = $request->validated();

        $journalEntries = JournalEntry::query()
            ->forCurrentCompany()
            ->with('lines')
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('entry_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('entry_date', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn ($query, string $source) => $query->where('source', $source))
            ->when($filters['journal_number'] ?? null, fn ($query, string $number) => $query->where('journal_number', 'like', "%{$number}%"))
            ->when($filters['account_id'] ?? null, fn ($query, int $accountId) => $query->whereHas('lines', fn ($query) => $query->where('account_id', $accountId)))
            ->latest('entry_date')
            ->latest('id')
            ->paginate();

        return JournalEntryResource::collection($journalEntries);
    }

    public function store(StoreJournalEntryRequest $request, CreateJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(JournalEntry $journalEntry): JournalEntryResource
    {
        Gate::authorize('view', $journalEntry);

        return JournalEntryResource::make($journalEntry->load('lines'));
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry, UpdateJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($journalEntry, $request->validated(), $request->user()));
    }

    public function post(PostJournalEntryRequest $request, JournalEntry $journalEntry, PostJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($journalEntry, $request->user(), $request->validated('comment')));
    }

    public function approve(ApproveJournalEntryRequest $request, JournalEntry $journalEntry, ApproveJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($journalEntry, $request->user(), $request->validated('comment')));
    }

    public function reject(RejectJournalEntryRequest $request, JournalEntry $journalEntry, RejectJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($journalEntry, $request->user(), $request->validated('comment')));
    }

    public function reverse(ReverseJournalEntryRequest $request, JournalEntry $journalEntry, ReverseJournalEntry $action): JournalEntryResource
    {
        return JournalEntryResource::make($action->handle($journalEntry, $request->user(), $request->validated('comment')));
    }

    public function destroy(JournalEntry $journalEntry): never
    {
        abort(405);
    }
}
