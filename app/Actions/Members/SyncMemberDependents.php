<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;
use Illuminate\Support\Facades\DB;

class SyncMemberDependents
{
    /**
     * @param array<int, array{name?: string|null, relationship?: string|null}> $dependents
     */
    public function execute(Member $member, array $dependents): void
    {
        DB::transaction(function () use ($member, $dependents): void {
            $member->dependents()->delete();

            foreach ($dependents as $dependent) {
                $name = trim((string) ($dependent['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $member->dependents()->create([
                    'name' => $name,
                    'relationship' => $dependent['relationship'] ?? null,
                ]);
            }
        });
    }
}
