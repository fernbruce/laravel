<?php

namespace Tests\Feature;

use App\Models\Address;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndex()
    {

        $this->assertLitemallApiGet('wx/catalog/index');
        $this->assertLitemallApiGet('wx/catalog/index?id=1005000');
        $this->assertLitemallApiGet('wx/catalog/index?id=30050005');
    }


    public function testCurrent()
    {
        $this->assertLitemallApiGet('wx/catalog/current', ['errmsg']);
        $this->assertLitemallApiGet('wx/catalog/current?id=1005000');
        $this->assertLitemallApiGet('wx/catalog/current?id=10050001');
    }
}
