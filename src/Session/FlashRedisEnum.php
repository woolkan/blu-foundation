<?php
declare(strict_types=1);

namespace Blu\Foundation\Session;

enum FlashRedisEnum: string
{
    case Flash_Redis_Prefix = 'flash:';
    case Flash_Msq_Key_Auth_Error = 'auth_error';
}
