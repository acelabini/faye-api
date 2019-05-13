<?php

namespace App\Utils\Enumerators;

class RolesEnumerator
{
    use Enumerates;

    const GUEST = 1;
    const USER = 2;
    const ADMIN = 3;
}
