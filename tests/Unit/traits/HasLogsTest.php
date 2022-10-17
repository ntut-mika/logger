<?php

namespace Mika\Logger\Test\Unit\traits;

use Illuminate\Foundation\Auth\User;
use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Test\Stubs\Models\Stub;
use Mika\Logger\Test\TestCase;

class HasLogsTest extends TestCase
{
    public function test_getLastLog()
    {
        $stub = Stub::create(['title' => 'create']);
        $stub->update(['title' => 'update']);

        $this->assertEquals(ModelActionEnum::UPDATE->value, $stub->getLastLog()->content['action']);
        $this->assertEquals(ModelActionEnum::CREATE->value, $stub->getLastLog(ModelActionEnum::CREATE)->content['action']);
        $this->assertEquals(ModelActionEnum::UPDATE->value, $stub->getLastLog(ModelActionEnum::UPDATE)->content['action']);
    }

    public function test_getLastExecutor()
    {
        $user1 = new User();
        $user1->name = 'mika1';
        $user1->email = 'mika1@example.test';
        $user1->password = bcrypt('test');
        $user1->save();

        $user2 = new User();
        $user2->name = 'mika2';
        $user2->email = 'mika2@example.test';
        $user2->password = bcrypt('test');
        $user2->save();


        auth()->setUser($user1);
        $stub = Stub::create(['title' => 'create']);

        auth()->setUser($user2);
        $stub->update(['title' => 'update']);
        $this->assertEquals($user2->name, $stub->getLastExecutor()->name);
        $this->assertEquals($user1->name, $stub->getLastExecutor(ModelActionEnum::CREATE)->name);
        $this->assertEquals($user2->name, $stub->getLastExecutor(ModelActionEnum::UPDATE)->name);
    }
}
