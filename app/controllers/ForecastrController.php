<?php


class ForecastrController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	// public function index()
	// {
	// 	// if(!empty(Session::get('zipcode')) ) {
	// 	// 	dd(Session::get('zipcode'));
	// 	// }

	// 	return View::make('forecastr.index');
	// }

	public function search()
	{

		$location = Input::get('location');

		if(!isset($location)) {
			$location = 'Louisville, KY';
		}

		//Register the Geocoder adapter and provider
		$adapter = new \Geocoder\HttpAdapter\CurlHttpAdapter;
		$provider = new \Geocoder\Provider\GoogleMapsProvider($adapter);
		$geocoder = new \Geocoder\Geocoder($provider);

		//Instantiate API key within the Forecast object
		$forecast = new Forecast('72cb4b66f2077387dafc4eeff3ac709e');

		$result = $geocoder->geocode($location);
		$result = $forecast->get($result["latitude"], $result["longitude"]);

		//Current Conditions Properties
		$currentTemp = $result->currently->temperature;
		$currentCondition = strtolower($result->currently->summary);
		$feelsLike = $result->currently->apparentTemperature;
		$icon = $result->currently->icon;
		$currentPrecip = $result->currently->precipProbability;

		$seticon = Forecastr::setIcons($icon);

		//Alerts Properties
		if(isset($result->alerts))
		{
			$alerts = $result->alerts;

			// foreach($alerts as $key => $value) {
			// 	$alertid = $key;
			// }
		}

		//Weekly Forecast Properties
		$daily = $result->daily->data;

		foreach($daily as $key => $value) {
			$value = $value->icon;
			$daily[$key]->icon = Forecastr::setIcons($value);
		}

        //Hourly rain
        $hourly = $result->hourly->data;
        $hourlyRain = array();
        foreach($hourly as $e) {
            array_push($hourlyRain, ($e->precipProbability * 100));
        }

        //Hourly time
        $hours = array();
        foreach($hourly as $h) {
            array_push($hours, gmdate('g:i A', $h->time));
        }

		//dd($hours);

		return View::make('forecastr.index', compact('location','result',
		'currentTemp', 'currentCondition', 'alerts', 'daily', 'currentPrecip', 'seticon', 'hours', 'hourlyRain'));

	}



}
