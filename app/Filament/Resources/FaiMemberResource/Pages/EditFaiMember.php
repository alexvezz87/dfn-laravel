<?php

namespace App\Filament\Resources\FaiMemberResource\Pages;

use App\Filament\Resources\FaiMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaiMember extends EditRecord
{
    protected static string $resource = FaiMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
