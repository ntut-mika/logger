<?php

namespace Mika\Logger\Listeners;

use Illuminate\Database\Eloquent\SoftDeletes;
use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Services\LogService;

class ModelListener
{
    /**
     * @var LogService $service
     */
    protected $service;

    public function __construct(LogService $service)
    {
        $this->service = $service;
    }

    public function handle($event, $models)
    {
        $action = $this->getAction($event, $models[0]);

        if ($this->shouldLog($models[0]) && $action != null) {
            foreach ($models as $model) {
                $this->service->create(collect([
                    'type' => LogTypeEnum::Model,
                    'content' => [
                        'class' => get_class($model),
                        $model->getKeyName() => $model->{$model->getKeyName()},
                        'action' => $action,
                        'original' => $model->getOriginal(),
                        'current' => $model->toArray(),
                        'changes' => $model->getDirty(),
                    ]
                ]));
            }
        }
    }

    private function getAction($event, $model)
    {
        preg_match('/\.(.*):/', $event, $matches);
        $action = $matches[1];

        if ($action === 'created') {
            return ModelActionEnum::CREATE;
        } elseif ($action === 'updated') {
            if (
                hasTrait($model, SoftDeletes::class)
                && $model->getOriginal('deleted_at') !== null
                && $model->deleted_at === null
            ) {
                return ModelActionEnum::RESTORE;
            } else {
                return ModelActionEnum::UPDATE;
            }
        } elseif ($action === 'deleted') {
            if (
                hasTrait($model, SoftDeletes::class)
                && ! $model->isForceDeleting()
            ) {
                return ModelActionEnum::SOFT_DELETE;
            } else {
                return ModelActionEnum::FORCE_DELETE;
            }
        } else {
            return null;
        }
    }

    private function shouldLog($model)
    {
        $ignoreModels = config('logger.ignore_models');

        return ! in_array(get_class($model), $ignoreModels);
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.*', [$this, 'handle']);
    }
}
