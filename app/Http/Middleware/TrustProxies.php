<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_FORWARDED |
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;

    /**
     * The trusted proxies for this application (APP_TRUSTED_PROXIES).
     *
     * @return array|string|null
     */
    protected function proxies()
    {
        if (static::$alwaysTrustProxies) {
            return static::$alwaysTrustProxies;
        }
        $proxies = config('trustedproxy.proxies');

        // '*' and '**' are passed as strings.
        return is_array($proxies) ? array_filter($proxies) : $proxies;
    }
}
