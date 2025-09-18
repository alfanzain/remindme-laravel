<?php

namespace App\Exceptions;

use Exception;

class InvalidRefreshTokenException extends Exception
{
    protected $message = 'Invalid refresh token';
}