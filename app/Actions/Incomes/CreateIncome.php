<?php

declare(strict_types=1);

namespace App\Actions\Incomes;

use App\Models\Income;

final class CreateIncome
{
    /**
     * @param array{date:string, source:string, description?:string|null, amount:int|float|string} $data
     */
    public function execute(array $data): Income
    {
        return Income::create([
            'date' => $data['date'],
            'source' => $data['source'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
        ]);
    }
}
