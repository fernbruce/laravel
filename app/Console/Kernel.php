<?php

namespace App\Console;

use App\Services\Order\OrderService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // $schedule->call(function () {
        //     OrderService::getInstance()->autoConfirm();
        // })->dailyAt('3:00')->runInBackground()->name('auto_confirm')->onOneServer();

        $schedule->call(function () {
            Log::info('test schedule');
        })->dailyAt('3:00')->runInBackground()->name('log')->onOneServer();

        // 每天在 8:00-12:00 随机时间执行
        $schedule->command('sign:auto')
            // ->dailyAt('8:00') // 基准时间
            // ->between('8:00', '12:00') // 确保在时间范围内
            // ->when(function () {
            //     // 添加 0-4 小时的随机延迟（确保在8-12点之间）
            //     $randomDelay = random_int(0, 240); // 随机分钟数 (0-240分钟)
            //     sleep($randomDelay * 60);
            //     return true;
            // })
            ->withoutOverlapping() // 防止任务重叠
            ->appendOutputTo(storage_path('logs/sign.log')) // 记录日志
            ->name('sign_auto')
            ->onOneServer(); // 如果是多服务器部署

        //        $schedule->exec();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
