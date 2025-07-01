<?php

namespace Tests;

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
}
