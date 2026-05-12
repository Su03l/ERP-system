<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'journal_entries.view');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $this->sameCompany($user, $journalEntry->company_id)
            && $user->hasPermission('journal_entries.view', $journalEntry->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'journal_entries.create');
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $this->sameCompany($user, $journalEntry->company_id)
            && $user->hasPermission('journal_entries.update', $journalEntry->company_id);
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return false;
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        return $this->sameCompany($user, $journalEntry->company_id)
            && $user->hasPermission('journal_entries.post', $journalEntry->company_id);
    }

    public function approve(User $user, JournalEntry $journalEntry): bool
    {
        return $this->sameCompany($user, $journalEntry->company_id)
            && $user->hasPermission('journal_entries.approve', $journalEntry->company_id);
    }

    public function reject(User $user, JournalEntry $journalEntry): bool
    {
        return $this->approve($user, $journalEntry);
    }

    public function reverse(User $user, JournalEntry $journalEntry): bool
    {
        return $this->sameCompany($user, $journalEntry->company_id)
            && $user->hasPermission('journal_entries.reverse', $journalEntry->company_id);
    }

    private function can(User $user, string $permission): bool
    {
        return $user->company_id !== null && $user->hasPermission($permission, $user->company_id);
    }

    private function sameCompany(User $user, int $companyId): bool
    {
        return $user->company_id !== null && $user->company_id === $companyId;
    }
}
