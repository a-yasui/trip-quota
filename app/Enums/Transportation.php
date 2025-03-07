<?php

namespace App\Enums;

enum Transportation: string
{
    case FLIGHT = 'flight';
    case TRAIN = 'train';
    case BUS = 'bus';
    case FERRY = 'ferry';
    case CAR = 'car';
    case WALK = 'walk';
    case BIKE = 'bike';
    case OTHER = 'other';
}
