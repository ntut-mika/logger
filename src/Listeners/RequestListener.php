<?php

namespace Mika\Logger\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Services\LogService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RequestListener
{
    /**
     * @var LogService $service
     */
    protected $service;

    public function __construct(LogService $service)
    {
        $this->service = $service;
    }

    public function handle(RequestHandled $event)
    {
        $this->service->create(collect([
            'type' => LogTypeEnum::Request,
            'content' => [
                'ip_address' => $event->request->getClientIp(),
                'method' => $event->request->getMethod(),
                'request_uri' => $event->request->getPathInfo(),
                'route_match' => $event->request->route()?->uri(),
                'controller_action' => $event->request->route()?->getActionName(),
                'middleware' => array_values($event->request->route()?->gatherMiddleware() ?? []),
                'headers' => $this->getHeaders($event),
                'queries' => $this->getQueries($event),
                'posts' => $this->getPosts($event),
                'sessions' => $this->getSessions($event),
                'response_status_code' => $event->response->getStatusCode(),
                'response_body' => $this->getResponseBody($event),
                'duration' => $this->getDuration($event),
                'memory' => $this->getMemoryUsage(),
            ]
        ]));
    }

    private function getHeaders(RequestHandled $event)
    {
        $headers = collect($event->request->headers->all())->map(function ($header) {
            return $header[0];
        })->toArray();

        return $this->hideParameters($headers);
    }

    private function getPosts(RequestHandled $event)
    {
        $post = $event->request->post();
        $files = $event->request->allFiles();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000) . 'KB' : '0',
            ];
        });

        return $this->hideParameters(array_merge($post, $files));
    }

    private function getQueries(RequestHandled $event)
    {
        return $this->hideParameters($event->request->query());
    }

    private function getSessions(RequestHandled $event)
    {
        $session = $event->request->hasSession() ? $event->request->session()->all() : [];

        return $this->hideParameters($session);
    }

    private function getDuration(RequestHandled $event)
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');

        return $startTime ? floor((microtime(true) - $startTime) * 1000) . 'ms' : null;
    }

    private function getMemoryUsage()
    {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'MB';
    }

    private function getResponseBody(RequestHandled $event)
    {
        $response = $event->response;
        $content = $response->getContent();

        if (is_string($content)) {
            if (
                is_array(json_decode($content, true))
                && json_last_error() === JSON_ERROR_NONE
            ) {
                return $this->contentWithinLimits($content)
                        ? $this->hideParameters(json_decode($content, true))
                        : 'Purged By Logger';
            }

            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Logger';
            }
        }

        if ($response instanceof RedirectResponse) {
            return 'Redirected to ' . $response->getTargetUrl();
        }

        if ($response instanceof Response && $response->getOriginalContent() instanceof View) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        if (is_string($content) && empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    private function hideParameters($data)
    {
        $hiddens = config('logger.hiddens', []);

        foreach ($hiddens as $hidden) {
            if (Arr::get($data, $hidden)) {
                Arr::set($data, $hidden, '********');
            }
        }

        return $data;
    }

    private function extractDataFromView($view)
    {
        return collect($view->getData())->map(function ($value) {
            if ($value instanceof Model) {
                return get_class($value) . ':' . implode('_', Arr::wrap($value->getKey()));
            } elseif (is_object($value)) {
                return [
                    'class' => get_class($value),
                    'properties' => json_decode(json_encode($value), true),
                ];
            } else {
                return json_decode(json_encode($value), true);
            }
        })->toArray();
    }

    public function contentWithinLimits($content)
    {
        $limit = config('logger.content_limit');

        return intdiv(mb_strlen($content), 1000) <= $limit;
    }

    public function subscribe($events)
    {
        $events->listen(RequestHandled::class, [$this, 'handle']);
    }
}
