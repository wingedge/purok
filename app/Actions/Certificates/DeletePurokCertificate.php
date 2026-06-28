<?php

declare(strict_types=1);

namespace App\Actions\Certificates;

use App\Models\PurokCertificate;

final class DeletePurokCertificate
{
    public function execute(PurokCertificate $certificate): void
    {
        $certificate->delete();
    }
}
