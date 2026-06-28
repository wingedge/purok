<?php

declare(strict_types=1);

namespace App\Actions\Certificates;

use App\Models\PurokCertificate;

final class UpdatePurokCertificate
{
    /**
     * @param array{member_id:int|string, request_date:string, purpose:string} $data
     */
    public function execute(PurokCertificate $certificate, array $data): PurokCertificate
    {
        $certificate->update([
            'member_id' => $data['member_id'],
            'request_date' => $data['request_date'],
            'purpose' => $data['purpose'],
        ]);

        return $certificate->refresh();
    }
}
