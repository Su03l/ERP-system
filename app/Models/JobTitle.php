<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\JobTitleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'name_ar',
    'name_en',
    'code',
    'description',
    'status',
])]
class JobTitle extends Model
{
    /** @use HasFactory<JobTitleFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the employees assigned to this job title.
     *
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
