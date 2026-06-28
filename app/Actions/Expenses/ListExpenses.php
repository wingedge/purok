<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListExpenses
{
    public function execute(int $perPage = 10): LengthAwarePaginator
    {
        return Expense::query()
            ->with('creator')
            ->orderByDesc('date')
            ->paginate($perPage);
    }
}
