<?php

namespace App\Models;

use App\Enums\MemberAccountStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'nis',
    'absen',
    'student_name',
    'class_name',
    'major',
    'academic_year',
    'gender',
    'graduation_year',
    'account_status',
])]
class Member extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_status' => MemberAccountStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saver(): HasOne
    {
        return $this->hasOne(Saver::class);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('account_status', MemberAccountStatus::Inactive);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('account_status', MemberAccountStatus::Active);
    }
}
