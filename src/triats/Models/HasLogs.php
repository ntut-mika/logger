<?php

namespace Mika\Logger\triats\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Mika\Logger\Enums\LogTypeEnum;
use Mika\Logger\Enums\ModelActionEnum;
use Mika\Logger\Models\Log;

trait HasLogs
{
    public function logs(ModelActionEnum $action = null)
    {
        $query = $this->hasMany(Log::class, 'content->' . $this->getKeyName())
                    ->where('type', LogTypeEnum::Model)
                    ->where('content->class', get_class($this))
                    ->orderBy('id', 'desc');

        if ($action !== null) {
            $query->where('content->action', $action);
        }

        return $query;
    }
}
