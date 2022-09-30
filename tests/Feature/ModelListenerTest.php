<?php

namespace Mika\Logger\Test\Feature;

use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Services\LogService;
use Mika\Logger\Test\Stubs\Models\Stub;
use Mika\Logger\Test\TestCase;

class ModelListenerTest extends TestCase
{
    /**
     * @var LogService $service
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LogService::class);
    }

    public function test_create()
    {
        $stub = Stub::create(['title' => 'init value']);

        [$status, $items] = $this->service->search(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Model);
            }
        ]));

        $this->assertEquals(1, $items->count());
        $this->assertEquals(ModelActionEnum::CREATE->value, $items[0]->content['action']);
        $this->assertEquals($stub->{$stub->getKeyName()}, $items[0]->content[$stub->getKeyName()]);
        $this->assertEquals(get_class($stub), $items[0]->content['class']);
        $this->assertEquals([], $items[0]->content['original']);
        $this->assertEquals($stub->id, $items[0]->content['current']['id']);
        $this->assertEquals($stub->title, $items[0]->content['current']['title']);
    }

    public function test_update()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->update(['title' => 'update value']);
        $stub->title = 'save value';
        $stub->save();

        [$status, $items] = $this->service->search(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Model)
                    ->where('content->action', ModelActionEnum::UPDATE);
            }
        ]));

        $this->assertEquals(2, $items->count());
    }

    public function test_soft_delete()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->delete();

        [$status, $items] = $this->service->search(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Model)
                    ->where('content->action', ModelActionEnum::SOFT_DELETE);
            }
        ]));

        $this->assertEquals(1, $items->count());
    }

    public function test_restore()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->delete();
        $stub->restore();

        [$status, $items] = $this->service->search(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Model)
                    ->where('content->action', ModelActionEnum::RESTORE);
            }
        ]));

        $this->assertEquals(1, $items->count());
    }

    public function test_force_delete()
    {
        $stub = Stub::create(['title' => 'init value']);
        $stub->forceDelete();

        [$status, $items] = $this->service->search(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Model)
                    ->where('content->action', ModelActionEnum::FORCE_DELETE);
            }
        ]));

        $this->assertEquals(1, $items->count());
    }
}
