<?php

namespace App\Utils\Enumerators;

class QuestionSetStatusEnumerator
{
    use Enumerates;

    const INACTIVE = 0;
    const ACTIVE = 1;
    const DEFAULT = 2;
}
