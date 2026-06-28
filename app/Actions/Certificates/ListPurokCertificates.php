<?php

declare(strict_types=1);

namespace App\Actions\Certificates;

use App\Models\PurokCertificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPurokCertificates
{
    public function execute(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PurokCertificate::query()
            ->with(['member.dependents'])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('member', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('member.dependents', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
