<?php

namespace Mika\Logger\Test\Feature;

use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Test\Stubs\Models\Stub;
use Mika\Logger\Test\TestCase;

class ModelListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create()
    {
        $stub = Stub::create(['title' => 'init value']);
        $logs = $stub->logs(ModelActionEnum::CREATE)->get();


        $this->assertEquals(1, $logs->count());
        $this->assertEquals(ModelActionEnum::CREATE->value, $logs[0]->content['action']);
        $this->assertEquals($stub->{$stub->getKeyName()}, $logs[0]->content[$stub->getKeyName()]);
        $this->assertEquals(get_class($stub), $logs[0]->content['class']);
        $this->assertEquals([], $logs[0]->content['original']);
        $this->assertEquals($stub->id, $logs[0]->content['current']['id']);
        $this->assertEquals($stub->title, $logs[0]->content['current']['title']);
    }

    public function test_update()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->update(['title' => 'update value']);
        $stub->title = 'save value';
        $stub->save();

        $logs = $stub->logs(ModelActionEnum::UPDATE)->get();

        $this->assertEquals(2, $logs->count());
    }

    public function test_soft_delete()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->delete();

        $logs = $stub->logs(ModelActionEnum::SOFT_DELETE)->get();

        $this->assertEquals(1, $logs->count());
    }

    public function test_restore()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->delete();
        $stub->restore();

        $logs = $stub->logs(ModelActionEnum::RESTORE)->get();

        $this->assertEquals(1, $logs->count());
    }

    public function test_force_delete()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->forceDelete();

        $logs = $stub->logs(ModelActionEnum::FORCE_DELETE)->get();

        $this->assertEquals(1, $logs->count());
    }
}
