<?php

namespace App\Console\Commands;

use App\Service\WeatherForecastService;
use Illuminate\Console\Command;
use Symfony\Component\HttpKernel\Exception\HttpException;

class generateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:generate-report {cities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates weather forecast report for comma separated list of cities';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param WeatherForecastService $forecastService
     */
    public function handle(WeatherForecastService $forecastService)
    {
        $cities = explode(',', trim($this->argument('cities')));

        $headers = ['Day', 'Min', 'Max', 'Summary'];

        foreach ($cities as $city) {
            $data = [];

            try {
                $forecastData = $forecastService->getForecast($city);

                $location = $forecastData['location'];
                $this->info(sprintf(
                    'Forecast for %s, %s is:',
                    $location['name'],
                    $location['country']
                ));

                foreach ($forecastData['forecast'] as $date => $forecast) {
                    $timestamp = strtotime($date);
                    $data[] = [
                        date('l jS \of F Y', $timestamp),
                        sprintf('%s°C', $forecast['min']),
                        sprintf('%s°C', $forecast['max']),
                        $forecast['condition']['text']
                    ];
                }

                $this->table($headers, $data);
                $this->newLine();
            } catch (HttpException $exception) {
                $this->info(sprintf('No location was found for %s', $city));
                $this->newLine();
                continue;
            }
        }
    }
}
