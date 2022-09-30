<?php

namespace Mika\Logger\Test\Feature;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Services\LogService;
use Mika\Logger\Test\TestCase;

class RequestListenerTest extends TestCase
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

    public function test_user_is_login()
    {
        Route::post('auth', function () {
            $user = new User();
            $user->name = 'mika';
            $user->email = 't107590003@ntut.org.tw';
            $user->password = bcrypt('test');
            $user->save();
            auth()->setUser($user);
        });

        $this->post('/auth');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(User::class, $item->user_type);
        $this->assertEquals(1, $item->user_id);
    }

    public function test_user_is_not_login()
    {
        Route::post('auth', function () {
        });

        $this->post('/auth');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertNull($item->user_type);
        $this->assertNull($item->user_id);
    }

    public function test_ip_address()
    {
        $this->get('/test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(request()->ip(), $item->content['ip_address']);
    }

    public function test_method()
    {
        $actions = [
            'get',
            'post',
            'put',
            'patch',
            'delete',
            'options'
        ];

        foreach ($actions as $index => $action) {
            $this->$action('/test');
            [$status, $item] = $this->service->getItem(collect([
                'query' => function ($query) use ($index) {
                    $query->where('type', LogTypeEnum::Request)->offset($index);
                }
            ]));

            $this->assertEquals(request()->method(), $item->content['method']);
        }
    }

    public function test_request_uri()
    {
        $this->get("/test?page=1");

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(request()->getPathInfo(), $item->content['request_uri']);
    }

    public function test_route_match()
    {
        Route::get('/{name}', function () {});

        $this->get("/test");

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(request()->route()->uri(), $item->content['route_match']);
    }

    public function test_controller_action()
    {
        Route::get('/{name}', function () {});

        $this->get("/test");

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(request()->route()->getActionName(), $item->content['controller_action']);
    }

    public function test_middleware()
    {
        Route::get('/{name}', function () {})->middleware(['api']);

        $this->get("/test");

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(request()->route()->gatherMiddleware(), $item->content['middleware']);
    }

    public function test_headers()
    {
        $this->get("/test", [
            'Authorization' => 'Bearer EnvyapdLNPOIR2',
        ]);

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals('********', $item->content['headers']['authorization']);
    }

    public function test_queries()
    {
        $this->get("/test?password=test");

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals('********', $item->content['queries']['password']);
    }

    public function test_posts()
    {
        $image = UploadedFile::fake()->image('avatar.jpg');
        $name = $image->getClientOriginalName();
        $size = $image->getSize() / 1000 . 'KB';

        $this->post('test', [
            'image' => $image,
            'files' => [
                $image,
                $image
            ],
            'password' => 'test;'
        ]);

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals($name, $item->content['posts']['files'][0]['name']);
        $this->assertEquals($name, $item->content['posts']['files'][1]['name']);
        $this->assertEquals($name, $item->content['posts']['image']['name']);
        $this->assertEquals($size, $item->content['posts']['files'][0]['size']);
        $this->assertEquals($size, $item->content['posts']['files'][1]['size']);
        $this->assertEquals($size, $item->content['posts']['image']['size']);
        $this->assertEquals('********', $item->content['posts']['password']);
    }

    public function test_sessions()
    {
        Route::get('test', function () {})->middleware(['web']);

        $this->withSession(['data' => 'test'])->get('/test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals('test', $item->content['sessions']['data']);
    }

    public function test_404()
    {
        $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(404, $item->content['response_status_code']);
        $this->assertEquals('HTML Response', $item->content['response_body']);
    }

    public function test_empty_response()
    {
        Route::get('/test', function () {
            return '';
        });

        $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(200, $item->content['response_status_code']);
        $this->assertEquals('Empty Response', $item->content['response_body']);
    }

    public function test_text_plain_response()
    {
        Route::get('/test', function () {
            return Response::make(
                'text plain resopnse', 200, ['Content-Type' => 'text/plain']
            );
        });

        $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(200, $item->content['response_status_code']);
        $this->assertEquals('text plain resopnse', $item->content['response_body']);
    }

    public function test_json_response()
    {
        Route::get('/test', function () {
            return response()->json([
                'data' => 'test'
            ]);
        });

        $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(200, $item->content['response_status_code']);
        $this->assertEquals('test', $item->content['response_body']['data']);
    }

    public function test_redirect_response()
    {
        Route::get('/test', function () {
            return redirect('/dashboard');
        });

        $response = $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(302, $item->content['response_status_code']);
        $this->assertEquals('Redirected to ' . $response->getTargetUrl(), $item->content['response_body']);
    }

    public function test_view_response()
    {
        $user = new User();
        $user->id = 1;

        $path = __DIR__ . '/../Stubs/Views/test.blade.php';
        $data = [
            'params1' => 'test1',
            'params2' => (object) [
                'id' => '1'
            ],
            'params3' => $user
        ];

        Route::get('/test', function () use ($path, $data) {
            return View::file($path, $data);
        });

        $response = $this->get('test');

        [$status, $item] = $this->service->getItem(collect([
            'query' => function ($query) {
                $query->where('type', LogTypeEnum::Request);
            }
        ]));

        $this->assertEquals(200, $item->content['response_status_code']);
        $this->assertEquals('test1', $item->content['response_body']['data']['params1']);
        $this->assertEquals('stdClass', $item->content['response_body']['data']['params2']['class']);
        $this->assertEquals('1', $item->content['response_body']['data']['params2']['properties']['id']);
        $this->assertEquals(get_class($user) . ':1', $item->content['response_body']['data']['params3']);
    }
}
