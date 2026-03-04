<?php

namespace App\Filament\Resources\Donors\Pages;

use App\Actions\Donor\CreateUserForDonor;
use App\Filament\Resources\Donors\DonorResource;
use App\Models\Donor;
use Filament\Resources\Pages\CreateRecord;

class CreateDonor extends CreateRecord
{
    protected static string $resource = DonorResource::class;

    protected function afterCreate(): void
    {
        /** @var Donor $record */
        $record = $this->record;

        app(CreateUserForDonor::class)->execute($record);
    }
}
