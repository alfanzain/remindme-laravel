<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialException extends Exception
{
    protected $message = 'Invalid credentials';
}