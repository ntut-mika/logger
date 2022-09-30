<?php

namespace Mika\Logger\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mika\Architecture\Bases\BaseModel;
use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Presenters\LogPresenter;

class Log extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'type',
        'content',
        'user_type',
        'user_id'
    ];

    protected $casts = [
        'type' => LogTypeEnum::class,
        'content' => AsArrayObject::class,
    ];

    protected $presenter = LogPresenter::class;
}
