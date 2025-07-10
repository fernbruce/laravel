<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class DBSqlListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(QueryExecuted $query)
    {
        if (!app()->environment(['testing', 'local'])) {
            return;
        }
        $sql = $query->sql;
        $bindings = $query->bindings;
        $time = $query->time;

        $bindings = array_map(function ($binding) {
            if (is_string($binding)) {
                return "'{$binding}'";
            } else if ($binding instanceof \DateTime) {
                return $binding->format("'Y-m-d H:i:s'");
            } else {
                return $binding;
            }
        }, $bindings);
        $sql = str_replace('?', '%s', $sql);
        $sql = sprintf($sql, ...$bindings);
        // $sql = vsprintf($sql, $bindings);
        Log::info('sql log', ['sql' => $sql, 'time' => $time]);
    }
}
