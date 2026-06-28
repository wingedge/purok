<?php

declare(strict_types=1);

namespace App\Actions\Incomes;

use App\Models\Income;

final class DeleteIncome
{
    public function execute(Income $income): void
    {
        $income->delete();
    }
}
