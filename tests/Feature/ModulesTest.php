<?php

namespace Tests\Feature;

use App\Misc\Modules\Repository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

/**
 * Modules from tests/Fixtures/Modules.
 */
class ModulesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(RepositoryInterface::class, new Repository($this->app, base_path('tests/Fixtures/Modules')));
        \App\Module::clearModulesCache();
    }

    public function testModuleIsFoundByAlias()
    {
        $module = \Module::findByAlias('TESTMODULE');

        $this->assertSame('testmodule', $module->getAlias());
        $this->assertSame('1.0.0', $module->version);
        $this->assertStringEndsWith('/TestModule/', \Module::getModulePathByAlias('testmodule'));
        $this->assertNull(\Module::findByAlias('missing'));
    }

    public function testStatusIsStoredInDb()
    {
        $this->assertFalse(\Module::isActive('testmodule'));

        \Module::findByAlias('testmodule')->enable();
        \Module::clearCache();
        $this->assertTrue(\Module::isActive('testmodule'));
        $this->assertTrue((bool)\App\Module::isActive('testmodule'));
        $this->assertArrayHasKey('test module', \Module::getActive());

        \Module::findByAlias('testmodule')->disable();
        \Module::clearCache();
        $this->assertFalse(\Module::isActive('testmodule'));
    }

    public function testRegistrationErrorIsPassedToFilter()
    {
        $errors = [];
        \Eventy::removeAllFilters('modules.register_error');
        \Eventy::addFilter('modules.register_error', function ($e, $module) use (&$errors) {
            $errors[$module->getAlias()] = $e->getMessage();
        }, 20, 2);

        \Module::findByAlias('brokenmodule')->register();

        $this->assertSame(['brokenmodule' => 'Broken module'], $errors);
    }

    public function testModuleMigrationsAreRun()
    {
        try {
            $this->artisan('module:migrate', ['module' => ['Test Module'], '--force' => true])->assertSuccessful();
            $this->assertTrue(\Schema::hasTable('test_module_items'));
        } finally {
            \Schema::dropIfExists('test_module_items');
            \DB::table('migrations')->where('migration', '2026_01_01_000000_create_test_module_items_table')->delete();
        }
    }
}
