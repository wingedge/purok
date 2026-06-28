<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;
use Illuminate\Support\Facades\DB;

final class UpdateMember
{
    public function __construct(
        private readonly SyncMemberDependents $syncMemberDependents,
    ) {}

    /**
     * @param array{
     *     name:string,
     *     phone?:string|null,
     *     email?:string|null,
     *     birthday?:string|null,
     *     indigent?:bool,
     *     dependents?:array<int, array{name?:string|null, relationship?:string|null}>
     * } $data
     */
    public function execute(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data): Member {
            $member->update([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'indigent' => $data['indigent'] ?? false,
                'birthday' => $data['birthday'] ?? null,
            ]);

            $this->syncMemberDependents->execute($member, $data['dependents'] ?? []);

            return $member->refresh();
        });
    }
}
