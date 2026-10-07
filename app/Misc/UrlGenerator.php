<?php
/**
 * URL generator with FreeScout specific behaviour
 * (ported from overrides/laravel/framework/src/Illuminate/Routing/UrlGenerator.php):
 * - x_ query parameters are passed to all route URLs, xs_ parameters - only to the current route.
 * - Root URL is taken from APP_URL (filterable via url_generator.app_url),
 *   as $request->root() does not determine subdirectory properly.
 * - Subdirectory is not duplicated in the path.
 */

namespace App\Misc;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class UrlGenerator extends BaseUrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        if (!is_null($this->routes->getByName($name))) {
            $parameters = (array)$parameters;
            $query = $this->request->query();

            foreach ($query as $param => $value) {
                // Pass x_ parameters globally.
                // Pass xs_ parameters only on the same page.
                if (preg_match('/^x_/', $param)
                    || (preg_match('/^xs_/', $param) && $name == optional($this->request->route())->getName())
                ) {
                    $parameters[$param] = $value;
                }
            }
        }

        return parent::route($name, $parameters, $absolute);
    }

    public function formatRoot($scheme, $root = null)
    {
        if (is_null($root) && is_null($this->cachedRoot)) {
            $this->cachedRoot = \Eventy::filter('url_generator.app_url', $this->forcedRoot ?: config('app.url'));

            if (!$this->cachedRoot || \Helper::isDefaultAppUrl($this->cachedRoot)) {
                $this->cachedRoot = $this->request->root();
            }

            // Remove the slash at the end as on some systems there is a slash at the end.
            $this->cachedRoot = rtrim($this->cachedRoot, '/');
        }

        return parent::formatRoot($scheme, $root);
    }

    public function format($root, $path, $route = null)
    {
        // Cut subdirectory from path.
        $subdirectory = \Helper::getSubdirectory(false, true);
        if ($subdirectory && preg_match('#'.preg_quote($subdirectory).'$#', trim($root, '/'))) {
            $path = preg_replace('#^'.preg_quote($subdirectory).'#', '', '/'.trim($path, '/'));
        }

        return parent::format($root, $path, $route);
    }
}
