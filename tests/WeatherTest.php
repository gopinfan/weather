<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2018/11/17
 * Time: 16:27
 */

namespace Pinfankeji\Weather\Tests;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;
use Pinfankeji\Weather\Exceptions\HttpException;
use Pinfankeji\Weather\Exceptions\InvalidArgumentException;
use Pinfankeji\Weather\Weather;

class WeatherTest extends TestCase
{

    public function testGetWeather()
    {

        // json
        $response = new Response(200, [], '{"success": true}');

        $client = \Mockery::mock(Client::class);

        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'output' => 'json',
                'extensions' => 'base',
            ]
        ])->andReturn($response);

        $weather = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $weather->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $weather->getWeather('深圳'));

        // xml
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'extensions' => 'all',
                'output' => 'xml',
            ],
        ])->andReturn($response);

        $weather = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $weather->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $weather->getWeather('深圳', 'all', 'xml'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()->get(new AnyArgs())
            ->andThrow(new HttpException('request timeout'));

        $weather = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $weather->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $weather->getWeather('深圳');
    }

    public function testGetHttpClient()
    {
        $weather = new Weather('mock-key');
        $this->assertInstanceOf(ClientInterface::class, $weather->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $weather = new Weather('mock-key');

        $this->assertNull($weather->getHttpClient()->getConfig('timeout'));

        $weather->setGuzzleOptions(['timeout'=>5000]);

        $this->assertSame(5000, $weather->getHttpClient()->getConfig('timeout'));
    }

    public function testGetWeatherWithInvalidType()
    {
        $weather = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid type value(base/all): foo');

        $weather->getWeather('深圳', 'foo');

        $this->fail('Failed to assert getWeather throw exception with invalid argument');
    }

    public function testGetWeatherWithInvalidFormat()
    {
        $weather = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid response format: array');

        $weather->getWeather('深圳', 'base', 'array');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }
}