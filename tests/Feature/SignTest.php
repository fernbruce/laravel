<?php

use GuzzleHttp\Client;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SignTest extends TestCase
{
    //    use DatabaseTransactions;
    // 签到请求的 URL
    private const SIGN_URL = 'https://gzccsh.cn/api/gift_sign';

    // 你的认证 Token（从浏览器复制）
    private const AUTH_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOjM1MjYsInNpZ24iOiI0ZTBlZDY1YzQyYWJjZGRjYmY0Y2E4ODU4ZWY0OGIwMSIsInJvbGUiOiJ1c2VyIiwiZXhwIjoxNzU0ODAwNTIwLCJuYmYiOjE3NTIxMjIxMjAsImlhdCI6MTc1MjEyMjEyMH0.pUIpThgfmDjQMtllZXSL4AZn14_ZBtAA9QezBUkILHQ';



    public function testBasicTest()
    {
        $response = $this->get('/');
        //        print_r($response->getContent());

        $response->assertStatus(200);
    }

    /**
     * 测试签到功能
     *
     * @return void
     */
    //    public function testGiftSignRequest()
    //    {
    //
    //        // 构建请求头
    //        $headers = [
    //            'authority' => 'gzccsh.cn',
    //            'accept' => 'application/json, text/plain, */*',
    //            'accept-encoding' => 'gzip, deflate, br, zstd',
    //            'accept-language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
    //            'authorization' => self::AUTH_TOKEN,
    //            'cache-control' => 'no-cache',
    //            'origin' => 'https://gzccsh.cn',
    //            'pragma' => 'no-cache',
    //            'priority' => 'u=1, i',
    //            'referer' => 'https://gzccsh.cn/me/gift',
    //            'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
    //            'sec-ch-ua-mobile' => '?0',
    //            'sec-ch-ua-platform' => '"Windows"',
    //            'sec-fetch-dest' => 'empty',
    //            'sec-fetch-mode' => 'cors',
    //            'sec-fetch-site' => 'same-origin',
    //            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
    //            'x-app-version' => '2.13.1',
    //        ];
    //
    //        // 发送 POST 请求
    //        $client = new Client();
    //        $response = $client->post(
    //            self::SIGN_URL,
    //            [
    //                'headers' => $headers,
    //                'json' => [],
    //                'verify' => false // 禁用 SSL 验证
    //            ]
    //        );
    //        $content = json_decode($response->getBody()->getContents(), true);
    //        dd($content);
    //
    //    }
    //
    //    public function testNodeDemo(){
    //        $client = new Client();
    //        $response = $client->post(
    //            "https://api.inews.qq.com/newsqa/v1/query/inner/publish/modules/list?modules=statisGradeCityDetail",
    //            [
    //                'headers' => [],
    //                'json' => [],
    //                'verify' => false // 禁用 SSL 验证
    //            ]
    //        );
    //        $content = json_decode($response->getBody()->getContents(), true);
    //        dd($content);
    //    }

    /**
     * 测试京东签到功能
     *
     * @return void
     */
    public function test_jd_sign_request()
    {
        // 配置请求头
        $headers = [
            'authority' => 'api.m.jd.com',
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded',
            'cookie' => 'pinId=A_SeeL8l33Yu-xSBqVyPsg; pin=fenghua20000; unick=9l6v9i75wdss25; shshshfpa=323c80a4-041b-774c-b176-68e6e378c65d-1730546958; shshshfpx=323c80a4-041b-774c-b176-68e6e378c65d-1730546958; __jdu=17305468940211483775043; jcap_dvzw_fp=AChLVdby1nOkHBeZ8og9d_YWj7fgxs2YZjpBDdlQ0Kh0S6PcXuefAogEEhPdaI-kMUEtANyhV6L9uineREYSZJ4PWzA=; _tp=Jyt40eTI2drtpr09jFZSTg%3D%3D; _pst=fenghua20000; ipLoc-djd=12-904-905-57902.84009497; ipLocation=%u6c5f%u82cf; TrackID=1np6b1x0OBxWtFdIrHuFEPTGmte1Qqsh0_za8CXnPuL57Lzkf-0ZeIzSI3FUmMQVdzCA-wKbCuUkXXDx0uL8HQsCW7J5Z3-OExWXedDpI1KA; light_key=AASBKE7rOxgWQziEhC_QY6yavQ9r-plh4IM3E1aYG1rIcfXUmMRo_3dQ_-LyWShntPe7xZP7; __jdv=222648329|direct|-|none|-|1756529493265; TARGET_UNIT=bjcenter; 3AB9D23F7A4B3CSS=jdd03ZWZES5BIBP3DG2A2YXSKRONLK6I5DZ7WHFTPZ3TMNBYLMOP33NXSOBZTOZ4X6M5KX6UEGKTNPDKLEFGSNJCGYVF2XEAAAAMZCULCNMQAAAAADHEUENDFCVV75UX; _gia_d=1; thor=89C5779690F2C03850101CFC5BA3AE9E58F04041FF319BBC7FB8009DAF6FBBE702F76DE7D1C1228C3AC5B28EEBB951F1BC1D1F9133C5049FC9D29D55BBCA238962E36D77788805C9B655B9B0AB7A71937B82CC16C7EDEE9DF611AD87015C18B86FFCF0670A4173A7E2ED63D77C2AA12A95A42DEF1EF559F57065BAE0C85EA9A8AFF5C629716F84CE32A3142D3F6A658F; mail_times=4%2C1%2C1756995399398; cn=54; PCSYCityID=CN_320000_320100_0; umc_count=1; sdtoken=AAbEsBpEIOVjqTAKCQtvQu17-vX3x8fufNHv9KjJPocLctVwGAVR6_s37EztxjUzZeFFXSaAUcdLglG3Am4-kMJuI5c_IyZfVedjxiBFzDZqNa4gZ7rHYMxS9uZt; shshshfpb=BApXSTtweFvxAyi609lyZ-AO_AZFYvneHBnIVbnd69xJ1Mqk2O4G2; __jda=166561581.17305468940211483775043.1730546894.1756544844.1756995398.47; __jdc=166561581; flash=3_hwLRrLPiD8vKPEQaHTwp1vB5cJQ2ozXuK4vLDCNOQ0EN541TGl1-h0ak-jCQuzQPt77EJOf5TGyfIYERGI9978d2XwNZLn8AlmyOfS-QspZN7pL62bGGfkpkezj-BNfZCVVkYcEiPcjr87S7nYnhDk-qkIh3jLzYVHXs2lTthHTq5v4r; __jdb=166561581.3.17305468940211483775043|47.1756995398; source=PC; platform=pc; 3AB9D23F7A4B3C9B=ZWZES5BIBP3DG2A2YXSKRONLK6I5DZ7WHFTPZ3TMNBYLMOP33NXSOBZTOZ4X6M5KX6UEGKTNPDKLEFGSNJCGYVF2XE',
            'origin' => 'https://bean.jd.com',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'referer' => 'https://bean.jd.com/myJingBean/list',
            'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-site',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        ];

        // 配置请求体
        $body = [
            'h5st' => '20250904221724177;i33tgattat0dj3w8;73c2f;tk03wee2a1d5018ndbrPeK7zTUUVviLMpYlsdMnvKZ9pZzNdcCRaD_Rn8CvpYDXkvPpIPodGv9gZaGuExFPd3IUiLZ87;f021aacf68213e503e06f3f067cf3662;5.2;1756995442177;gt6f-BuEu5bIxRKJtQKJ6QqVu97ZB5_ZxI7ZBh-f1h_VB5_ZzUrJ-hfZXx-ZAcuVwd_IsJLJtd7UtNbTwdOUuReI7IuVxZOV7EuVwh_ZB5_ZxIdG6YLIqYfZB5hW-deUrR_IwhOVxZ7U_Y7Uq9OT8cuUuJuV8YOIuVbUoNOU-h-T-VKJroLJ_YfZB5hW-h_ZB5_ZtN6J-hfZXx-Zxp-VzN_ECMbG4IrKsB7ZB5_ZrYfZB5hW-RcDxZuI5ALUtYfZnZPGyQ7GAY6ZBh-f1Z-VupLHKYfZnZ-IxYfZB5hWkgfZXZeZnZPVwN6J-hfZBh-f1ROVB5_ZxdOE-YfZBhfZXxfT0h-T-ZOVsY7ZBhfZB5hW-19NM4rVO89V98uK-k8ZB5_Z0kbIzc7F-hfZBh-f1heZnZfTsY7ZBhfZB5hWxh-T-FOE-YfZBhfZXx-Vvh-T-JOE-YfZBhfZXxfVB5_ZsN6J-hfZBh-f1heZnZfUsY7ZBhfZB5hWtZeZnZvVsY7ZBhfZB5hW-R_WwpfV-h-T-dOE-YfZBhfZXxfVB5_Z2E6ZBhfZB5hWsh-T-VaG-hfZBh-f1heZnZfG-hfZBh-f1heZnZfIqYfZBhfZX1aZnZfIzMbEpM7ZBh-f1taZB5BZxheVxZOJsArIzM7I-h-T-ZeF-hfZBh-fmg-T-haF-hfZXx-ZtJeDB1eUrpLHKgvTxpfVwhfMTgvFqkbIz8rM-h-T-dLEuYfZB5xD;b987e35f95e745a2625f7270545ce70b;gRaW989Gy8bE_oLE7w-Gy8rFvM7MtoLI4wrJ1R6G88bG_wPD9k7J1RLHxgKJ',
            'uuid' => '17305468940211483775043',
            'loginType' => '3',
            'appid' => 'asset-h5',
            'clientVersion' => '1.0.0',
            'client' => 'pc',
            't' => '1756995442173',
            'body' => '{"type":5,"eaId":"4KpUNjgQZtanUeeqbhMYjT47b9Fo","itemId":"1","extraType":"sign"}',
            'functionId' => 'pc_interact_sign_execute',
            'x-api-eid-token' => 'jdd03ZWZES5BIBP3DG2A2YXSKRONLK6I5DZ7WHFTPZ3TMNBYLMOP33NXSOBZTOZ4X6M5KX6UEGKTNPDKLEFGSNJCGYVF2XEAAAAMZCULCNMQAAAAADHEUENDFCVV75UX',
            'area' => '12_904_905_57902',
        ];

        // 发送POST请求
        $response = Http::withHeaders($headers)
            ->withoutVerifying() // 禁用SSL证书验证
            ->asForm()
            ->post('https://api.m.jd.com/', $body);

        // 记录响应信息
        Log::info('JD Sign Response: ', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        // 断言请求成功
        $this->assertTrue($response->successful());

        // 断言响应中包含成功标志
        $responseData = $response->json();
        dd($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);

        // 如果有奖励信息，记录奖励详情
        if (isset($responseData['data']['assignmentRewardInfo']['jingDouRewards'])) {
            foreach ($responseData['data']['assignmentRewardInfo']['jingDouRewards'] as $reward) {
                Log::info('Received reward: ' . $reward['rewardName']);
            }
        }
    }
}
