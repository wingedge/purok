<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;

final class UpdateExpense
{
    /**
     * @param array{date:string, category:string, description?:string|null, amount:int|float|string} $data
     */
    public function execute(Expense $expense, array $data): Expense
    {
        $expense->update([
            'date' => $data['date'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
        ]);

        return $expense->refresh();
    }
}
