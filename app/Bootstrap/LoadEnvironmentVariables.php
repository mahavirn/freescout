<?php

namespace App\Bootstrap;

use App\Misc\EnvParser;
use Dotenv\Dotenv;
use Dotenv\Loader\Loader;
use Dotenv\Store\StoreBuilder;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as BaseLoadEnvironmentVariables;
use Illuminate\Support\Env;

class LoadEnvironmentVariables extends BaseLoadEnvironmentVariables
{
    /**
     * Same as parent, but with FreeScout compatible parser.
     */
    protected function createDotenv($app)
    {
        $store = StoreBuilder::createWithNoNames()
            ->addPath($app->environmentPath())
            ->addName($app->environmentFile())
            ->shortCircuit()
            ->make();

        return new Dotenv($store, new EnvParser(), new Loader(), Env::getRepository());
    }
}
