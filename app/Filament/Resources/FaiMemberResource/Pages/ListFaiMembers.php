<?php

namespace App\Filament\Resources\FaiMemberResource\Pages;

use App\Filament\Resources\FaiMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaiMembers extends ListRecords
{
    protected static string $resource = FaiMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
