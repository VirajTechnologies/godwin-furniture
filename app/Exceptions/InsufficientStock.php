<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStock extends RuntimeException
{
    public function __construct(string $productName, int $available, int $requested)
    {
        parent::__construct($productName.' has '.$available.' in the warehouse. This transfer asks for '.$requested.'.');
    }
}
