<?php
/**
 * Session guard with FreeScout filter (ported from overrides/laravel/framework/src/Illuminate/Auth/SessionGuard.php).
 * Modules can change credentials validation result via `session_guard.validate_credentials` filter.
 */

namespace App\Misc;

use Illuminate\Auth\SessionGuard as BaseSessionGuard;

class SessionGuard extends BaseSessionGuard
{
    protected function hasValidCredentials($user, #[\SensitiveParameter] $credentials)
    {
        $validated = !is_null($user)
            && \Eventy::filter('session_guard.validate_credentials', $this->provider->validateCredentials($user, $credentials), $user, $credentials);

        if ($validated) {
            $this->fireValidatedEvent($user);
        }

        return $validated;
    }
}
