<?php

namespace Mika\Logger\Test\Unit\Models;

use Illuminate\Foundation\Auth\User;
use Mika\Logger\Test\Stubs\Models\Stub;
use Mika\Logger\Test\TestCase;

class LogTest extends TestCase
{
    public function test_user_relation()
    {
        $user = new User();
        $user->name = 'mika';
        $user->email = 't107590003@ntut.org.tw';
        $user->password = bcrypt('test');
        $user->save();
        auth()->setUser($user);

        $stub = Stub::create([
            'title' => 'create'
        ]);

        $this->assertInstanceOf(get_class($user), $stub->logs()->first()->user);
    }
}
