<?php

namespace Tests\Unit;

use TorMorten\Eventy\Events;
use Tests\TestCase;

/**
 * FreeScout behaviour of overrides/tormjens/eventy.
 */
class EventyTest extends TestCase
{
    public function testMissingArgumentsArePassedAsNull()
    {
        $eventy = new Events();
        $eventy->addFilter('test', function ($value, $param) {
            return $value.':'.var_export($param, true);
        }, 20, 2);

        $this->assertSame('value:NULL', $eventy->filter('test', 'value'));
    }

    public function testClosuresAreStoredAsIs()
    {
        $eventy = new Events();
        $callback = function ($value) {
            return $value.'!';
        };
        $eventy->addFilter('test', $callback);

        $this->assertSame($callback, $eventy->getFilter()->getListeners('test')[0]['callback']);

        $eventy->removeFilter('test', $callback);
        $this->assertSame('value', $eventy->filter('test', 'value'));
    }
}
