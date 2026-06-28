<?php

declare(strict_types=1);

namespace App\Actions\Incomes;

use App\Models\Income;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListIncomes
{
    public function execute(int $perPage = 10): LengthAwarePaginator
    {
        return Income::query()
            ->orderByDesc('date')
            ->paginate($perPage);
    }
}
