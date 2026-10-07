<?php

namespace Tests\Feature;

use App\Http\Middleware\CanInstall;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    public function testInstallerIsNotAvailableWhenInstalled()
    {
        $this->get(route('installer.welcome'))->assertRedirect(route('dashboard'));
    }

    public function testInstallerPages()
    {
        $this->withoutMiddleware(CanInstall::class);

        $this->get(route('installer.welcome'))->assertOk();
        $this->get(route('installer.requirements'))->assertOk()->assertSee('PDO');
        $this->get(route('installer.permissions'))->assertOk()->assertSee('storage/logs/');
        $this->get(route('installer.environment'))->assertOk()->assertSee('database_hostname');
    }

    public function testInvalidEnvironmentIsNotSaved()
    {
        $this->withoutMiddleware(CanInstall::class);
        $env = file_get_contents(base_path('.env'));

        $this->post(route('installer.environment.save'), ['app_url' => 'not-url'])
            ->assertOk()
            ->assertSee('database_hostname');

        $this->assertSame($env, file_get_contents(base_path('.env')));
    }
}
