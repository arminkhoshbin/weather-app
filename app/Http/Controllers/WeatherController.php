<?php

namespace App\Http\Controllers;

use App\Service\WeatherForecastService;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeatherController extends Controller
{
    use ValidatesRequests;

    /**
     * @var WeatherForecastService
     */
    private $forecastService;

    /**
     * @param WeatherForecastService $forecastService
     */
    public function __construct(WeatherForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getForecastForCity(Request $request): JsonResponse
    {
        try {
            $forecastData = $this->forecastService->getForecast($request->query('city'));

            return response()->json($forecastData);
        } catch (HttpException $exception) {
            return response()->json($exception->getMessage(), $exception->getStatusCode());
        }
    }
}
