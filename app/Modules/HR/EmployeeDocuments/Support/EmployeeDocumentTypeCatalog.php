<?php

namespace App\Modules\HR\EmployeeDocuments\Support;

use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\HRReferenceData\Support\ReferenceMetadataContract;
use Illuminate\Database\Eloquent\Collection;

class EmployeeDocumentTypeCatalog
{
    public const CATEGORY = ReferenceMetadataContract::EMPLOYEE_DOCUMENT_TYPE;

    /** @return Collection<int, ReferenceData> */
    public function inputOptions(): Collection
    {
        return ReferenceData::query()
            ->where('category', self::CATEGORY)
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function resolveForHistory(int $id): ?ReferenceData
    {
        return ReferenceData::withTrashed()
            ->where('category', self::CATEGORY)
            ->find($id);
    }
}
