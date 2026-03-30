<?php

namespace App\Filament\Resources\EnrichedItems\Pages;

use App\Filament\Resources\EnrichedItems\EnrichedItemResource;
use Filament\Resources\Pages\ListRecords;

class ListEnrichedItems extends ListRecords
{
    protected static string $resource = EnrichedItemResource::class;
}
