<?php

namespace App\Providers;

use App\Listeners\DBSqlListener;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *集中绑定-把事件跟监听器进行绑定
     * @var array
     */
    protected $listen = [
        // Registered::class => [
        //     SendEmailVerificationNotification::class,
        // ],
        // 一个事件可以被多个监听器监听
        //事件的发起者 事件的监听者
        //事件的发起者可以是业务也可以是系统本身
        //系统本身会自带一些类似QueryExecuted这样的事件
        QueryExecuted::class => [
            DBSqlListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //分别绑定
        // DB::listen(function (QueryExecuted $query) {
        //     $sql = $query->sql;
        //     $bindings = $query->bindings;
        //     $time = $query->time;

        //     $bindings = array_map(function ($binding) {
        //         if (is_string($binding)) {
        //             return "'{$binding}'";
        //         } else if ($binding instanceof \DateTime) {
        //             return $binding->format("'Y-m-d H:i:s'");
        //         } else {
        //             return $binding;
        //         }
        //     }, $bindings);
        //     $sql = str_replace('?', '%s', $sql);
        //     $sql = sprintf($sql, ...$bindings);
        //     // $sql = vsprintf($sql, $bindings);
        //     Log::info('sql log', ['sql' => $sql, 'time' => $time]);
        // });
    }
}
