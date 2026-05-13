<?php

namespace App\Services;

use App\Models\CompanyDocument;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentExportQuery
{
    public function __construct(
        private readonly DocumentExpiryService $expiryService,
    ) {}

    /**
     * @param  array{document_type?: string, status?: string, expiry_from?: string, expiry_until?: string, include_file_paths?: bool}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function companyDocuments(array $filters, User $actor): array
    {
        $this->authorize($actor, 'company_documents.view');

        return $this->rows($this->applyFilters(CompanyDocument::query()->forCompany($actor->company_id), $filters), 'company_document', $actor, $filters);
    }

    /**
     * @param  array{document_type?: string, status?: string, expiry_from?: string, expiry_until?: string, include_file_paths?: bool}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function employeeDocuments(array $filters, User $actor): array
    {
        $this->authorize($actor, 'employee_documents.view');

        return $this->rows($this->applyFilters(EmployeeDocument::query()->forCompany($actor->company_id)->with('employee'), $filters), 'employee_document', $actor, $filters);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function expiring(array $filters, User $actor, int $days = 30): array
    {
        $this->authorize($actor, 'company_documents.view');
        $this->authorize($actor, 'employee_documents.view');

        return $this->expiryRows($this->expiryService->expiringWithin($days, $actor->company_id), 'expiring', $actor, $filters);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function expired(array $filters, User $actor): array
    {
        $this->authorize($actor, 'company_documents.view');
        $this->authorize($actor, 'employee_documents.view');

        return $this->expiryRows($this->expiryService->expired($actor->company_id), 'expired', $actor, $filters);
    }

    private function authorize(User $actor, string $permission): void
    {
        if ($actor->company_id === null || ! $actor->hasPermission($permission, $actor->company_id)) {
            throw new AuthorizationException('You are not authorized to export documents.');
        }
    }

    /**
     * @param  Builder<CompanyDocument|EmployeeDocument>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<CompanyDocument|EmployeeDocument>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['document_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('document_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['expiry_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('expiry_date', '>=', $date))
            ->when($filters['expiry_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('expiry_date', '<=', $date))
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    /**
     * @param  Builder<CompanyDocument|EmployeeDocument>  $query
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function rows(Builder $query, string $ownerType, User $actor, array $filters): array
    {
        return $query->get()->map(fn (Model $document): array => $this->row($document, $ownerType, null, $actor, $filters))->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function expiryRows(mixed $groups, string $state, User $actor, array $filters): array
    {
        $rows = [];

        foreach ($groups as $key => $documents) {
            $ownerType = $key === 'employee_documents' ? 'employee_document' : 'company_document';

            foreach ($documents as $document) {
                $rows[] = $this->row($document, $ownerType, $state, $actor, $filters);
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function row(Model $document, string $ownerType, ?string $state, User $actor, array $filters): array
    {
        $includeFilePath = ($filters['include_file_paths'] ?? false)
            && $actor->hasPermission('documents.file_paths.view', $actor->company_id);

        return [
            'owner_type' => $ownerType,
            'state' => $state,
            'document_type' => $document->document_type?->value ?? $document->document_type,
            'document_type_label' => $document->document_type?->label(),
            'status' => $document->status?->value ?? $document->status,
            'status_label' => $document->status?->label(),
            'title_ar' => $document->title_ar,
            'title_en' => $document->title_en,
            'issue_date' => $document->issue_date?->toDateString(),
            'expiry_date' => $document->expiry_date?->toDateString(),
            'file_path' => $includeFilePath ? $document->file_path : null,
        ];
    }
}
