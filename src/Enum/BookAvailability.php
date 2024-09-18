<?php

namespace Code16\LaravelTiteliveClient\Enum;

enum BookAvailability: int
{
    case AvailableOnDemand = 1;
    case Forthcoming = 2;
    case Reprint = 3;
    case Unavailable = 4;
    case CommercialChange = 5;
    case OutOfPrint = 6;
    case Missing = 7;
    case ToBePublishedAgain = 8;

    public function getLabel(): string
    {
        return match($this) {
            BookAvailability::AvailableOnDemand => 'Sur commande',
            BookAvailability::Forthcoming => 'À paraître',
            BookAvailability::Reprint,
            BookAvailability::ToBePublishedAgain => 'À reparaître',
            BookAvailability::Unavailable,
            BookAvailability::Missing,
            BookAvailability::CommercialChange => 'Indisponible',
            BookAvailability::OutOfPrint => 'Épuisé',
        };
    }
}
