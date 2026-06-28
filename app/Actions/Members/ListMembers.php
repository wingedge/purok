<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListMembers
{
    public function execute(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Member::query()
            ->withCount('dependents')
            ->when($search, function (Builder $query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
