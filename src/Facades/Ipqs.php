<?php

namespace IpqsLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \IpqsLaravel\IpqsService
 */
class Ipqs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IpqsService::class;
    }
}
