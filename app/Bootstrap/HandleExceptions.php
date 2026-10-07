<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;
use Throwable;

class HandleExceptions extends BaseHandleExceptions
{
    /**
     * Do not add to the output the "PHP Request Shutdown: Unexpected characters at end of address: <> (errflg=3)"
     * error happening inside imap_* functions.
     */
    protected function renderHttpResponse(Throwable $e)
    {
        if (strstr($e->getMessage(), 'PHP Request Shutdown: Unexpected characters at end of address')) {
            return;
        }

        parent::renderHttpResponse($e);
    }
}
