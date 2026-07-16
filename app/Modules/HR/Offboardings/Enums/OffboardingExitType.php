<?php

namespace App\Modules\HR\Offboardings\Enums;

enum OffboardingExitType: string
{
    case Resignation = 'RESIGNATION';
    case Termination = 'TERMINATION';
    case EndOfContract = 'END_OF_CONTRACT';
    case Retirement = 'RETIREMENT';
    case Other = 'OTHER';
}
