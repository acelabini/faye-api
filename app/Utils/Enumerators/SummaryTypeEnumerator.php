<?php

namespace App\Utils\Enumerators;

class SummaryTypeEnumerator
{
    use Enumerates;

    const PIE = 'pie';
    const BAR = 'bar';
    const CLOUD = 'cloud';
    const PERCENTAGE = 'percentage';
}
