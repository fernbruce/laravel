<?php

use GuzzleHttp\Client;
use Tests\TestCase;

class SignTest extends TestCase
{
//    use DatabaseTransactions;
    // 签到请求的 URL
    private const SIGN_URL = 'https://gzccsh.cn/api/gift_sign';

    // 你的认证 Token（从浏览器复制）
    private const AUTH_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOjM1MjYsInNpZ24iOiI0ZTBlZDY1YzQyYWJjZGRjYmY0Y2E4ODU4ZWY0OGIwMSIsInJvbGUiOiJ1c2VyIiwiZXhwIjoxNzU0ODAwNTIwLCJuYmYiOjE3NTIxMjIxMjAsImlhdCI6MTc1MjEyMjEyMH0.pUIpThgfmDjQMtllZXSL4AZn14_ZBtAA9QezBUkILHQ';


    /**
     * 测试签到功能
     *
     * @return void
     */
    public function testGiftSignRequest()
    {
        // 构建请求头
        $headers = [
            'authority' => 'gzccsh.cn',
            'accept' => 'application/json, text/plain, */*',
            'accept-encoding' => 'gzip, deflate, br, zstd',
            'accept-language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
            'authorization' => self::AUTH_TOKEN,
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

        // 发送 POST 请求
        $client = new Client();
        $response = $client->post(
            self::SIGN_URL,
            [
                'headers' => $headers,
                'json' => [],
                'verify' => false // 禁用 SSL 验证
            ]
        );
        $content = json_decode($response->getBody()->getContents(), true);
        dd($content);

    }

    public function testNodeDemo(){
        $client = new Client();
        $response = $client->post(
            "https://api.inews.qq.com/newsqa/v1/query/inner/publish/modules/list?modules=statisGradeCityDetail",
            [
                'headers' => [],
                'json' => [],
                'verify' => false // 禁用 SSL 验证
            ]
        );
        $content = json_decode($response->getBody()->getContents(), true);
        dd($content);
    }


}
