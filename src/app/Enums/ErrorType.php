<?php

namespace App\Enums;

enum ErrorType
{
    case ERR_BAD_REQUEST;
    case ERR_INVALID_CREDS;
    case ERR_INVALID_TOKEN;
    case ERR_INVALID_ACCESS_TOKEN;
    case ERR_INVALID_REFRESH_TOKEN;
    case ERR_FORBIDDEN_ACCESS;
    case ERR_NOT_FOUND;
    case ERR_INTERNAL_ERROR;
}