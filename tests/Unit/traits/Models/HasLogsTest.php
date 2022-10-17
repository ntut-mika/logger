<?php

namespace Mika\Logger\Test\Unit\traits\Models;

use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Test\Stubs\Models\Stub;
use Mika\Logger\Test\TestCase;

class HasLogsTest extends TestCase
{
    public function test_logs_relation()
    {
        $stub = Stub::create([
            'title' => 'create'
        ]);

        $stub->update([
            'title' => 'update'
        ]);

        $this->assertEquals(2, $stub->logs->count());
        $this->assertEquals(1, $stub->logs(ModelActionEnum::CREATE)->count());
        $this->assertEquals(1, $stub->logs(ModelActionEnum::UPDATE)->count());
    }
}
