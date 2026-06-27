<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportExpenses;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_expenses_from_csv(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $path = $this->writeCsv([
            ['date', 'category', 'description', 'amount'],
            ['2026-06-15', 'Community Services', 'Cleanup supplies', '123.45'],
        ]);

        $result = app(ImportExpenses::class)->execute($path, $creator);

        $this->assertSame(1, $result->created);
        $this->assertSame(0, $result->failed());
        $this->assertDatabaseHas('expenses', [
            'date' => '2026-06-15 00:00:00',
            'category' => 'Community Services',
            'description' => 'Cleanup supplies',
            'amount' => 123.45,
            'created_by' => $creator->id,
        ]);
    }

    public function test_it_reports_invalid_rows_without_importing_them(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $path = $this->writeCsv([
            ['date', 'category', 'description', 'amount'],
            ['not-a-date', '', 'Invalid row', '-5'],
        ]);

        $result = app(ImportExpenses::class)->execute($path, $creator);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_it_skips_blank_rows(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $path = $this->writeCsv([
            ['date', 'category', 'description', 'amount'],
            ['', '', '', ''],
        ]);

        $result = app(ImportExpenses::class)->execute($path, $creator);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_import_route_imports_csv_for_treasurer(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $file = UploadedFile::fake()->createWithContent(
            'expenses.csv',
            "date,category,description,amount\n2026-06-15,Community Services,Cleanup supplies,123.45\n",
        );

        $this->actingAs($creator)
            ->post(route('expenses.import'), [
                'csv_file' => $file,
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'date' => '2026-06-15 00:00:00',
            'category' => 'Community Services',
            'description' => 'Cleanup supplies',
            'amount' => 123.45,
            'created_by' => $creator->id,
        ]);
    }

    public function test_import_route_is_forbidden_for_staff(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'expenses.csv',
            "date,category,description,amount\n2026-06-15,Community Services,Cleanup supplies,123.45\n",
        );

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->post(route('expenses.import'), [
                'csv_file' => $file,
            ])
            ->assertForbidden();
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'expenses-import-');
        $stream = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
