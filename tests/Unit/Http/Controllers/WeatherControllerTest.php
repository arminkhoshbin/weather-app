<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\WeatherController;
use App\Service\WeatherForecastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    /** @var WeatherForecastService|Mock */
    private $forecastService;

    /** @var WeatherController */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastService = Mockery::mock(WeatherForecastService::class);

        $this->controller = new WeatherController($this->forecastService);
    }

    public function testGetForecastForCitySuccess(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('query')->with('city')->andReturn('someCity');

        $this->forecastService->shouldReceive('getForecast')->with('someCity')->andReturn([
            'forecast1' => 'data1',
            'forecast2' => 'data2'
        ]);

        /** @var JsonResponse $result */
        $result = $this->controller->getForecastForCity($request);

        $this->forecastService->shouldReceive('getForecast')->with('someCity');
        self::assertInstanceOf(JsonResponse::class, $result);
        self::assertEquals(200, $result->getStatusCode());
        self::assertEquals(json_encode([
            'forecast1' => 'data1',
            'forecast2' => 'data2'
        ]), $result->getContent());
    }

    public function testGetForecastForCityWhenHTTPExceptionIsThrown(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('query')->with('city')->andReturn('someCity');
        $this->forecastService->shouldReceive('getForecast')->withAnyArgs()->andThrow(
            new HttpException(500, 'this is an error')
        );

        $result = $this->controller->getForecastForCity($request);

        $this->forecastService->shouldReceive('getForecast')->with('someCity');
        self::assertEquals(500, $result->getStatusCode());
        self::assertEquals(json_encode('this is an error'), $result->getContent());
    }
}
