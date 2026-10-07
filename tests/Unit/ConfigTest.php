<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * APP_KEY can be set in .env or in a file specified in APP_KEY_FILE.
 */
class ConfigTest extends TestCase
{
    const KEY_FILE = __DIR__.'/.keyfile';

    public function testAppKeyFromEnvironment()
    {
        $this->assertSame('key-from-env', $this->getAppKey(['APP_KEY' => 'key-from-env', 'APP_KEY_FILE' => '']));
    }

    public function testAppKeyFromFile()
    {
        $this->assertSame(trim(file_get_contents(self::KEY_FILE)), $this->getAppKey(['APP_KEY' => '', 'APP_KEY_FILE' => self::KEY_FILE]));
    }

    public function testEnvironmentTakesPrecedenceOverFile()
    {
        $this->assertSame('key-from-env', $this->getAppKey(['APP_KEY' => 'key-from-env', 'APP_KEY_FILE' => self::KEY_FILE]));
    }

    protected function getAppKey(array $env)
    {
        $original = [];
        foreach ($env as $name => $value) {
            $original[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null];
            $_ENV[$name] = $_SERVER[$name] = $value;
        }

        try {
            return (require base_path('config/app.php'))['key'];
        } finally {
            foreach ($original as $name => [$env_value, $server_value]) {
                $_ENV[$name] = $env_value;
                $_SERVER[$name] = $server_value;
            }
        }
    }
}
