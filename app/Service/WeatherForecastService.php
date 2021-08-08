<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeatherForecastService
{
    /** @var Client */
    private $httpClient;

    /**
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $city
     * @return array
     */
    public function getForecast($city): array
    {
        if (!$city) {
            throw new HttpException(
                400, 'Invalid request. Please provide city as a query string parameter'
            );
        }

        try {
            $response = $this->httpClient->get(
                sprintf('?q=%s&key=%s&days=5', $city, Config::get('api.weather_api.key')));

            return $this->cleanupWeatherData(json_decode($response->getBody()->getContents(), true));
        } catch (GuzzleException $exception) {
            if ($exception->getCode() === Response::HTTP_BAD_REQUEST) {
                throw new HttpException(404, 'location not found');
            }

            throw new HttpException(500, $exception->getMessage());
        }
    }

    /**
     * @param $data
     * @return array
     */
    private function cleanupWeatherData($data): array
    {
        $forecast = [];
        $forecast['location'] = $data['location'];

        foreach ($data['forecast']['forecastday'] as $record) {
            $forecast['forecast'][$record['date']] = [
                'min' => $record['day']['mintemp_c'],
                'max' => $record['day']['maxtemp_c'],
                'condition' => $record['day']['condition']
            ];
        }

        return $forecast;
    }
}
