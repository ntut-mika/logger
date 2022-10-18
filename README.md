# Logger
Laravel Logger Package

## Environment Requirement
| Laravel | Logger |
|---------|--------|
|   9.x   |   1.x  |

## LogList
- Request
- Model

## Install
```
composer require mika/logger
```

## Publish
```
php artisan vendor:publish --provider="Mika\Logger\Providers\LoggerServiceProvider"
```

## Tutorial

you can use HasLogs trait in your model
```php
use Mika\Logger\triats\Models\HasLogs;

class YourModel extends Model
{
    use HasLogs;
}
```

```php
YourModel::first()->getLastExecutor(); // get last execute user
YourModel::first()->getLastLog(); // get this record last log

YourModel::first()->getLastExecutor(ModelActionEnum::UPDATE); // get last update user
YourModel::first()->getLastLog(ModelActionEnum::UPDATE); // get last update log

YourModel::first()->logs(); // get this record all logs query
YourMOdel::first()->logs(ModelActionEnum::UPDATE); // get this record all update logs query
```
