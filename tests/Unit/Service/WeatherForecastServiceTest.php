<?php


namespace Tests\Unit\Service;

use App\Service\WeatherForecastService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WeatherForecastServiceTest extends TestCase
{
    /** @var WeatherForecastService */
    private $service;

    /** @var Client|Mock */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(Client::class);
        $this->service = new WeatherForecastService($this->httpClient);
    }

    public function testGetForecastWithNoCity(): void
    {
        try {
            $this->service->getForecast(null);
        } catch (\Exception $e) {
            self::assertInstanceOf(HttpException::class, $e);
            self::assertEquals(400, $e->getStatusCode());
            self::assertEquals(
                'Invalid request. Please provide city as a query string parameter',
                $e->getMessage()
            );
        }
    }

    public function testGetForecastWhenLocationNotFound(): void
    {
        $this->httpClient->shouldReceive('get')->withAnyArgs()->andThrow(
            new ClientException('not found', new Request('GET', 'test'), new Response(400))
        );

        try {
            $this->service->getForecast('city');
        } catch (\Exception $e) {
            self::assertInstanceOf(HttpException::class, $e);
            self::assertEquals(404, $e->getStatusCode());
            self::assertEquals(
                'location not found',
                $e->getMessage()
            );
        }
    }

    public function testGetForecastWhenAPIThrowsServerError(): void
    {
        $this->httpClient->shouldReceive('get')->withAnyArgs()->andThrow(
            new ClientException(
                'server error',
                new Request('GET', 'test'),
                new Response(500)
            )
        );

        try {
            $this->service->getForecast('city');
        } catch (\Exception $e) {
            self::assertInstanceOf(HttpException::class, $e);
            self::assertEquals(500, $e->getStatusCode());
            self::assertEquals(
                'server error',
                $e->getMessage()
            );
        }
    }

    public function testGetForecastSuccessfully(): void
    {
        $apiData = [
            'location' => [
                'name' => 'brisbane'
            ],
            'forecast' => [
                'forecastday' => [
                    [
                        'date' => '2021-07-07',
                        'day' => [
                            'maxtemp_c' => 22,
                            'mintemp_c' => 10,
                            'condition' => [
                                'text' => 'Sunny',
                                'icon' => 'some icon'
                            ]
                        ]
                    ],
                    [
                        'date' => '2021-07-08',
                        'day' => [
                            'maxtemp_c' => 20,
                            'mintemp_c' => 14,
                            'condition' => [
                                'text' => 'Sunny',
                                'icon' => 'some icon'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = Mockery::mock(ResponseInterface::class);
        $body = Mockery::mock(StreamInterface::class);

        $this->httpClient->shouldReceive('get')->withAnyArgs()->andReturn($response);
        $response->shouldReceive('getBody')->andReturn($body);
        $body->shouldReceive('getContents')->andReturn(json_encode($apiData));

        $result = $this->service->getForecast('city');

        $expected = [
            'location' => [
                'name' => 'brisbane'
            ],
            'forecast' => [
                '2021-07-07' => [
                    'min' => 10,
                    'max' => 22,
                    'condition' => [
                        'text' => 'Sunny',
                        'icon' => 'some icon'
                    ]
                ],
                '2021-07-08' => [
                    'min' => 14,
                    'max' => 20,
                    'condition' => [
                        'text' => 'Sunny',
                        'icon' => 'some icon'
                    ]
                ]
            ]
        ];

        self::assertEquals($expected, $result);
    }
}
