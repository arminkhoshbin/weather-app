import React, { Fragment, useState, useEffect } from 'react';
import { Listbox, Transition } from '@headlessui/react';

const WEATHER_API_BASE_URI = 'http://localhost:8000/api';
const cities = [
    'Brisbane',
    'Melbourne',
    'Perth',
    'Adelaide',
    'Canberra',
    'Sydney',
    '123'
];

function App() {
    const [weatherState, setWeatherState] = useState({
        loading: false,
        forecast: null,
        error: null
    });
    const [city, selectCity] = useState(cities[0]);

    useEffect(() => {
        setWeatherState({ loading: true });
        fetch(WEATHER_API_BASE_URI + '/weather-forecast?city=' + city)
            .then(res => {
                if (res.status === 404) {
                    throw new Error('Location not found');
                } else if (res.status === 500) {
                    throw new Error('We are having trouble fetching the weather forecast. Please try again Later.');
                } else {
                    return res.json();
                }
            })
            .then(data => {
                setWeatherState({ loading: false, forecast: data, error: null })
            })
            .catch(err => {
                setWeatherState({ loading: false, forecast: null, error: err.message });
            });
    }, [city, setWeatherState])

    const renderForecastEntries = (forecastData) => {
        let result = [];
        let index = 0;
        for (const [date, data] of Object.entries(forecastData)) {
            result.push(
                <div key={index}>
                    <p><strong>{date}</strong></p>
                    {`${data.min}°C - ${data.max}°C`} - {data.condition.text}
                    <img className="inline w-10 h-10" src={data.condition.icon} alt={data.condition.text} />
                </div>
            );
            index++;
        }

        return result;
    }

    const renderForecast = () => {
        if (weatherState.loading) {
            return <div><h1>Loading...</h1></div>
        }

        if (weatherState.error !== null) {
            return <div>
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-10" role="alert">
                    <strong className="font-bold">{weatherState.error}</strong>
                </div>
            </div>
        }

        if (weatherState.forecast === null) {
            return;
        }

        return (
            <div>
                <h1 className="text-3xl mt-2 mb-5">
                    Forecast for {weatherState.forecast.location.name}, {weatherState.forecast.location.country} is:
                </h1>
                <ul className="list-disc">
                    {renderForecastEntries(weatherState.forecast.forecast)}
                </ul>
            </div>
        );
    }

  return (
      <div className="container mx-auto">
          <div className="grid grid-cols-2 gap-9">
              <div className="w-72 top-16">
                  <Listbox value={city} onChange={selectCity} disabled={weatherState.loading}>
                      <div className="relative mt-10">
                          <Listbox.Label className="mb-5">Select City</Listbox.Label>
                          <Listbox.Button className="relative w-full py-2 pl-3 pr-10 text-left bg-white rounded-lg shadow-md cursor-default focus:outline-none focus-visible:ring-2 focus-visible:ring-opacity-75 focus-visible:ring-white focus-visible:ring-offset-orange-300 focus-visible:ring-offset-2 focus-visible:border-indigo-500 sm:text-sm">
                              <span className="block truncate">{city}</span>
                          </Listbox.Button>
                          <Transition
                              as={Fragment}
                              leave="transition ease-in duration-100"
                              leaveFrom="opacity-100"
                              leaveTo="opacity-0"
                          >
                              <Listbox.Options className="absolute w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                  {cities.map((option, index) => (
                                      <Listbox.Option
                                          key={index}
                                          className={({ active }) =>
                                              `${active ? 'text-amber-900 bg-amber-100' : 'text-gray-900'}
                          cursor-default select-none relative py-2 pl-10 pr-4`
                                          }
                                          value={option}
                                      >
                                          {({ selected, active }) => (
                                              <>
                      <span
                          className={`${
                              selected ? 'font-medium' : 'font-normal'
                          } block truncate`}
                      >
                        {option}
                      </span>
                                                  {selected ? (
                                                      <span
                                                          className={`${
                                                              active ? 'text-amber-600' : 'text-amber-600'
                                                          }
                                absolute inset-y-0 left-0 flex items-center pl-3`}
                                                      >
                        </span>
                                                  ) : null}
                                              </>
                                          )}
                                      </Listbox.Option>
                                  ))}
                              </Listbox.Options>
                          </Transition>
                      </div>
                  </Listbox>
              </div>
              { renderForecast() }
          </div>
      </div>

  );
}

export default App;
