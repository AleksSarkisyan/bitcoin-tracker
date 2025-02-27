<?php

namespace Tests\Unit\Services;

use App\Services\BitfinexService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BitfinexServiceTest extends TestCase
{
    protected $config;
    protected $fakeApiUrl = 'https://api.example.com/test';

    protected function setUp(): void
    {
        parent::setUp();

        config(['errors.api.notFound' => 'Not found error message']);
        config(['errors.api.generic' => 'Generic error message']);
        config(['bitfinex.v2.formattedEndpoint.responseFields' => [
            0 => 'Field1',
            1 => 'Field2'
        ]]);

        $this->config = [
            'formattedEndpoint' => [
                'endpoint' => $this->fakeApiUrl,
                'autoFormatResponse' => true
            ],
            'notFormattedEndpoint' => [
                'endpoint' => 'https://api.example.com/noformat',
                'autoFormatResponse' => false
            ]
        ];
    }

    public function testNotFoundError()
    {
        $service = new BitfinexService([]);
        $error = $service->notFoundError();

        $this->assertEquals(404, $error['code']);
        $this->assertEquals('Not found error message', $error['error']);
    }

    public function testResponseError()
    {
        $service = new BitfinexService([]);
        $error = $service->responseError(500);

        $this->assertEquals(500, $error['code']);
        $this->assertEquals('Generic error message', $error['error']);
    }

    public function testBuildApiUrlWithoutQuery()
    {
        $service = new BitfinexService($this->config);
        $url = $service->buildApiUrl([], 'formattedEndpoint');

        $this->assertEquals($this->fakeApiUrl, $url);
    }

    public function testBuildApiUrlWithQuery()
    {
        $service = new BitfinexService($this->config);
        $url = $service->buildApiUrl(['query' => '?param=value'], 'formattedEndpoint');

        $this->assertEquals($this->fakeApiUrl . '?param=value', $url);
    }

    public function testFormatResponse()
    {
        $service = new BitfinexService($this->config);

        $apiResponse = [
            0 => 'value1',
            1 => 'value2'
        ];
        $formatted = $service->formatResponse('formattedEndpoint', $apiResponse);

        $this->assertArrayHasKey('field1', $formatted);
        $this->assertArrayHasKey('field2', $formatted);
        $this->assertEquals('value1', $formatted['field1']);
        $this->assertEquals('value2', $formatted['field2']);
    }

    public function testGetWithInvalidEndpointType()
    {
        $service = new BitfinexService($this->config);
        $result = $service->get('invalid_endpoint', []);

        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Not found error message', $result['error']);
    }

    public function testGetWithNonOkResponse()
    {
        $service = new BitfinexService($this->config);

        Http::fake([
            $this->fakeApiUrl .'*' => Http::response(null, 500)
        ]);

        $result = $service->get('formattedEndpoint', []);
        $this->assertEquals(500, $result['code']);
        $this->assertEquals('Generic error message', $result['error']);
    }

    public function testGetWithOkResponseAndAutoFormat()
    {
        $service = new BitfinexService($this->config);

        $apiResponse = [
            0 => 'testValue1',
            1 => 'testValue2'
        ];

        Http::fake([
            $this->fakeApiUrl .'*' => Http::response($apiResponse, 200)
        ]);

        $result = $service->get('formattedEndpoint', []);

        $this->assertArrayHasKey('field1', $result);
        $this->assertArrayHasKey('field2', $result);
        $this->assertEquals('testValue1', $result['field1']);
        $this->assertEquals('testValue2', $result['field2']);
    }

    public function testGetWithOkResponseWithoutAutoFormat()
    {
        $service = new BitfinexService($this->config);

        $apiResponse = [
            0 => 'raw',
            1 => 'raw2'
        ];

        Http::fake([
            'https://api.example.com/noformat*' => Http::response($apiResponse, 200)
        ]);

        $result = $service->get('notFormattedEndpoint', []);

        $this->assertEquals($apiResponse, $result);
    }
}
