<?php
/**
 * HTTP request with FreeScout specific behaviour
 * (ported from overrides/symfony/http-foundation/Request.php).
 */

namespace App\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    /**
     * Use visitor IP passed by CloudFlare.
     */
    public function getClientIps(): array
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])
            && ($_SERVER['REMOTE_ADDR'] ?? '') != $_SERVER['HTTP_CF_CONNECTING_IP']
            && config('app.cloudflare_is_used')
            // https://github.com/freescout-help-desk/freescout/security/advisories/GHSA-9cm3-qvj2-8hg4
            && \Helper::isValidIp($_SERVER['HTTP_CF_CONNECTING_IP'])
        ) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            $this->server->set('REMOTE_ADDR', $_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        return parent::getClientIps();
    }

    /**
     * FreeScout determines protocol using app.url parameter.
     */
    public function isSecure(): bool
    {
        return \Helper::isHttps();
    }
}
