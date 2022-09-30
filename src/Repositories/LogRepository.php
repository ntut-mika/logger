<?php

namespace Mika\Logger\Repositories;

use Mika\Architecture\Bases\BaseRepository;
use Mika\Logger\Models\Log;

class LogRepository extends BaseRepository
{
    /**
     * @var Log $model
     */
    protected $model;

    public function __construct(Log $model)
    {
        parent::__construct($model);
    }
}
