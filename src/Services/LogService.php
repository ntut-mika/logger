<?php

namespace Mika\Logger\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mika\Architecture\Bases\BaseService;
use Mika\Logger\Repositories\LogRepository;

class LogService extends BaseService
{
    /**
     * @var LogRepository $repo
     */
    protected $repo;

    protected static $batch_id = null;

    public function __construct(LogRepository $repo)
    {
        parent::__construct($repo);

        if (static::$batch_id === null) {
            static::$batch_id = Str::orderedUuid()->toString();
        }
    }

    public function create(Collection $input)
    {
        $user = auth()->user();

        $input->put('batch_id', static::$batch_id);
        $input->put('user_type', $user ? get_class($user) : null);
        $input->put('user_id', $user?->id);

        return parent::create($input);
    }
}
