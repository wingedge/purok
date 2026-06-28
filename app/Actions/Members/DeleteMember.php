<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;

final class DeleteMember
{
    public function execute(Member $member): void
    {
        $member->delete();
    }
}
