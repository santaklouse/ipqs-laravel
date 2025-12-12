<?php

namespace IpqsLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use IpqsLaravel\IpqsService;

/**
 * @method static array checkIp(string $ip, ?string $userAgent = NULL, ?string $userLanguage = NULL, ?int $strictness = NULL)
 * @method static array verifyEmail(string $email, array $options = [])
 * @method static array validatePhone(string $phone, array $options = [])
 * @method static array bulkValidateCsv(string $type, array $list)
 * @see \IpqsLaravel\IpqsService
 */
class Ipqs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IpqsService::class;
    }
}
