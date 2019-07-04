<?php

namespace App\Utils\Enumerators;

class UserStatusEnumerator
{
    use Enumerates;

    const BLOCKED = 'blocked';
    const ACTIVE = 'active';
    const PENDING = 'pending';
}
