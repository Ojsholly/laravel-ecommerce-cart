<?php

namespace App\Exceptions;

use Exception;

class EmptyCartException extends Exception
{
    public function __construct(string $message = 'Cart is empty.', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
