<?php

namespace Mika\Logger\Test\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mika\Architecture\Bases\BaseModel;
use Mika\Logger\triats\Models\HasLogs;

class Stub extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use HasLogs;

    protected $fillable = [
        'title'
    ];

    protected $casts = [];
}
