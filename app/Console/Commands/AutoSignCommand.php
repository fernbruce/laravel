<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoSignCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sign:auto';
    protected $description = '自动执行签到任务 (8:00-12:00随机时间)';

    // 签到URL
    private const SIGN_URL = 'https://gzccsh.cn/api/gift_sign';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getAuthToken(){
        return env('SIGN_AUTH_TOKEN');
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = $this->getAuthToken();

        if (empty($token)) {
            $this->error('未配置签到令牌，请在 .env 设置 SIGN_AUTH_TOKEN');
            Log::error('自动签到失败: 未配置认证令牌');
            return;
        }

        $headers = [
            'authority' => 'gzccsh.cn',
            'accept' => 'application/json, text/plain, */*',
            'authorization' => $token,
            'cache-control' => 'no-cache',
            'origin' => 'https://gzccsh.cn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'referer' => 'https://gzccsh.cn/me/gift',
            'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
            'x-app-version' => '2.13.1',
        ];
        try {
            $this->info("正在发送签到请求...");
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->retry(3, 5000)
                ->post(self::SIGN_URL);

            $status = $response->status();
            $responseData = $response->json();
            $this->info($response);
            $this->info($response->successful());
//            $this->info($response->status());
//            $this->info(json_encode($response->json(),JSON_UNESCAPED_UNICODE));
            Log::info($response);
            Log::info($response->status());
            Log::info(json_encode($response->json(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

            if ($response->successful() && isset($responseData['code'])){
                if ($responseData['code'] === 0) {
                    $this->info("签到成功: uid:{$responseData['data']['uid']} integral:{$responseData['data']['integral']}");
                    Log::info("签到成功", ['response' => $responseData]);
                } else {
                    $this->error("签到失败");
                    Log::warning("签到失败", [
                        'status' => $status,
                        'response' => $responseData
                    ]);
                }
            } else {
                $this->error("请求失败，状态码: {$status}");
                Log::error("签到请求失败", [
                    'status' => $status,
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            $this->error("请求异常: " . $e->getMessage());
            Log::error("签到请求异常", [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }
}
