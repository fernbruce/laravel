<?php

namespace Tests;

use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    protected $token;
    public function getAuthHeader()
    {
        $response = $this->post('/wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123'
        ]);
        $this->token = $response->getOriginalContent()['data']['token'] ?? '';

        return ['Authorization' => 'Bearer ' . $this->token];
    }


    public function assertLitemallApiGet($uri, $ignore = [])
    {
        $this->assertLitemallApi($uri, 'get', [], $ignore);
    }
    public function assertLitemallApiPost($uri,  $data = [], $ignore = [])
    {
        $this->assertLitemallApi($uri, 'post', $data, $ignore);
    }
    public function assertLitemallApi($uri, $method = 'get', $data = [], $ignore = [])
    {
        $client = new Client();
        if ($method == 'get') {
            // $response1 = $this->get($uri, $this->getAuthHeader());
            $response1 = $this->get($uri);
            $response2 = $client->get('http://47.99.102.217:8080/' . $uri, ['headers' => ['X-Litemall-Token' => $this->token]]);
        } else {
            $response1 = $this->post($uri, $data, $this->getAuthHeader());
            $response2 = $client->post(
                'http://47.99.102.217:8080/' . $uri,
                [
                    'headers' => ['X-Litemall-Token' => $this->token],
                    'json' => $data
                ]
            );
        }

        // $content = $response2->getBody()->getContents();
        // $content = json_decode($content, true);
        // $response1->assertJson($content);
        $content1 = json_decode($response1->getContent(), true);
        echo "laravel=>";
        print_r($content1);
        $content2 = json_decode($response2->getBody()->getContents(), true);
        echo "litemall=>";
        print_r($content2);

        foreach ($ignore as $key) {
            unset($content1[$key]);
            unset($content2[$key]);
        }
        $this->assertEquals($content2, $content1);
    }
}
