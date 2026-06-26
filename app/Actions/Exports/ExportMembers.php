<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Member;

final class ExportMembers
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'name',
            'phone',
            'email',
            'birthday',
            'indigent',
            'dependent_names',
            'dependent_relationships',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Member::query()
            ->with('dependents')
            ->orderBy('name')
            ->chunk(500, function ($members) use ($stream): void {
                foreach ($members as $member) {
                    fputcsv($stream, $this->row($member));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Member $member): array
    {
        return [
            $member->id,
            $member->name,
            $member->phone,
            $member->email,
            $member->birthday?->format('Y-m-d'),
            $member->indigent ? 'yes' : 'no',
            $member->dependents->pluck('name')->implode('|'),
            $member->dependents->pluck('relationship')->map(
                fn (?string $relationship): string => $relationship ?? '',
            )->implode('|'),
            $member->created_at?->format('Y-m-d H:i:s'),
            $member->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
