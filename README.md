## How to setup the backend for weather app

In the project root directory, run:

### `composer install`

You will then need to update the .env file and populate `WEATHER_API_KEY` with an
API key from https://www.weatherapi.com/

### `php artisan serve`

This should start the server on port 8000 and the API should be accessible
via [http://localhost:8000/api/weather-forecast](http://localhost:8000/api/weather-forecast)

Refer to the README file in `/frontend` directory for instructions on
how to setup the frontend.

## Running the console command

### `php artisan weather:generate-report brisbane,melbourne`

## Running Unit Tests

In the project root directory, run:

### `./vender/bin/phpunit tests/Unit`


## Assumptions and Notes
- The API does not have any form of authentication. It is simply querying
the weather API, does some sanitizition and expose the data for the frontend
to consume.
- Unfortunately with the weather API I was using, there was only forecast
for the next 3 days available for some reason. So the app will be showing
the weather forecast for the next 3 days rather than 5.
- There's no frontend test as this was only a simple React application. In case of
bigger applications that will deal with multiple stores and states, I would
usually add tests around the redux store. Or if there are more API calls
involved, then I would create utility classes to handle the API calls and
unit test those classes. In the case of this simple React app, I found it
to be overkill.
