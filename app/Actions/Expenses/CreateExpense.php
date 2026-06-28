<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;
use App\Models\User;

final class CreateExpense
{
    /**
     * @param array{date:string, category:string, description?:string|null, amount:int|float|string} $data
     */
    public function execute(array $data, User $creator): Expense
    {
        return Expense::create([
            'date' => $data['date'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'created_by' => $creator->id,
        ]);
    }
}
