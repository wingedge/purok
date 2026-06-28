<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Finance\ExpenseCategories;
use App\Support\Finance\IncomeSources;
use PHPUnit\Framework\TestCase;

class FinanceOptionsTest extends TestCase
{
    public function test_expense_categories_expose_values_and_filament_options(): void
    {
        $values = ExpenseCategories::values();

        $this->assertContains('Misc', $values);
        $this->assertSame($values, array_values(ExpenseCategories::options()));
        $this->assertSame($values, array_keys(ExpenseCategories::options()));
    }

    public function test_income_sources_expose_values_and_rental_source(): void
    {
        $values = IncomeSources::values();

        $this->assertSame('Rentals - Chairs / Table rental', IncomeSources::rental());
        $this->assertSame(IncomeSources::rental(), $values[0]);
        $this->assertSame($values, array_values(IncomeSources::options()));
        $this->assertSame($values, array_keys(IncomeSources::options()));
    }
}
