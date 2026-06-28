<?php

declare(strict_types=1);

namespace App\Actions\Certificates;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SearchCertificateMembers
{
    /**
     * @return Collection<int, array{id:int, name:string, deps:string}>
     */
    public function execute(?string $term, int $limit = 10): Collection
    {
        $term = trim((string) $term);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Member::query()
            ->where('name', 'like', "%{$term}%")
            ->orWhereHas('dependents', fn (Builder $query): Builder => $query->where('name', 'like', "%{$term}%"))
            ->with('dependents')
            ->limit($limit)
            ->get()
            ->map(fn (Member $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'deps' => $member->dependents->pluck('name')->implode(', '),
            ]);
    }
}
