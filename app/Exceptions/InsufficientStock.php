<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStock extends RuntimeException
{
    public function __construct(string $productName, int $available, int $requested, string $place = 'warehouse')
    {
        parent::__construct(
            $productName.': need '.$requested.', but the '.$place.' has only '.$available.'.'
        );
    }
}
