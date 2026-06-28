<?php

declare(strict_types=1);

namespace App\Actions\Incomes;

use App\Models\Income;

final class UpdateIncome
{
    /**
     * @param array{date:string, source:string, description?:string|null, amount:int|float|string} $data
     */
    public function execute(Income $income, array $data): Income
    {
        $income->update([
            'date' => $data['date'],
            'source' => $data['source'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
        ]);

        return $income->refresh();
    }
}
