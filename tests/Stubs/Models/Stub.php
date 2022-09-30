<?php

namespace Mika\Logger\Test\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mika\Architecture\Bases\BaseModel;

class Stub extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title'
    ];

    protected $casts = [];
}
