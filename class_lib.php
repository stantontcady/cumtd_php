<?php
	/*
	    Copyright 2011 Stanton T. Cady
		
	    cumtd_php API v0.2 -- December 23, 2011
		
	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
	*/

	class cumtd {
		// Public properties
		public $_format;	// json or xml (currently only json is supported and is the default)
		public $_apiUrl; 	// url that points to the cumtd api (default is http://developer.cumtd.com/api/)
		public $_version;	// cumtd api version number as a string (default is 2.0)
					
		// Private properties
		private $_apiKey;	// your api key (signup for one at http://developer.cumtd.com/)
		private $_cacheDir;	// directory to store the cache (default is ./cache/)
		private $_useCache;	// boolean variable to enable/disable caching data (default is true)
		
		// Public methods
		/*
			Function: Default cumtd constructor
			
			Purpose: Creates a new instance of the cumtd class
			
			Parameters: 
				apiKey: 	(required) This is your developer api key
				apiUrl: 	(optional) URL that points to the cumtd developer api (default is http://developer.cumtd.com/api/)
				version: 	(optional) cumtd developer api version as a string. The first supported version is 2.0 (default is 2.0)
				cacheDir:	(optional) Directory to store cached data (default is ./cache/)
				useCache: 	(optional) Boolean variable to enable/disable caching data (default is true)
		*/
		function __construct($apiKey, $apiUrl = 'http://developer.cumtd.com/api/', $version = '2.0', $cacheDir = './cache/', $useCache = true) {
			$this->_apiKey = $apiKey;
			$this->_apiUrl = $apiUrl;
			$this->_version = $version;
			$this->_cacheDir = $cacheDir;
			$this->_useCache = $useCache;
			$this->_format = 'json';
		}
			
		/// Cache settings
		/*
			Function: setCacheDir
			
			Purpose: Sets the directory in which cached data is stored
			
			Parameters: 
				dir: 	(required) Directory you'd like to store the cache (e.g. '/tmp/cumtd')
		*/			
		function setCacheDir($dir) {
			$this->_cacheDir = $dir;
		}
		/*
			Function: disableCache
			
			Purpose: Turns caching off
			
		*/		
		function disableCache() {
			$this->_useCache = false;
		}
		/*
			Function: enableCache
			
			Purpose: Turns caching on
			
		*/			
		function enableCache() {
			$this->_useCache = true;
		}
		
		/// Calendar dates
		/*
			Function: getCalendarDatesByDate
			
			Purpose: Gets service ids that run on specified date
			
			Parameters: 
				date: 		(required) date of interest (YYYY-MM-DD)
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of calendar dates with the following keys:
						date:		a date this service operates
						service_id:	id for this service type
		*/			
		function getCalendarDatesByDate($date, $verbose = false) {
			$rsp = $this->getCachedData('GetCalendarDatesByDate',array(array("name"=>"date","value"=>$date)),true,$verbose);
			return $rsp["calendar_dates"];			
		}
		/*
			Function: getCalendarDatesByDate
			
			Purpose: Gets all the dates that a specified services runs on
			
			Parameters: 
				service_id:	(required) id of the service
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of calendar dates with the following keys:
						date:		a date this service operates
						service_id:	id for this service type
		*/		
		function getCalendarDatesByService($service_id, $verbose = false) {
			$rsp = $this->getCachedData('GetCalendarDatesByService',array(array("name"=>"service_id","value"=>$service_id)),true,$verbose);
			return $rsp["calendar_dates"];			
		} 
		/// Departures
		/*
			Function: getDeparturesByStop
			
			Purpose: Gets the departures based upon a specified stop
			
			Parameters: 
				stop_id: 	(required) id of the stop to get departures for (e.g. IU is the stop_id for Illini Union)
				route_id: 	(optional) string or array of stings of bus route id(s) (e.g. ILLINI is the route_id for the 22)
				pt: 		(optional) preview time in minutes between 0 and 60 (default is 30)
				count:		(optional) maximum number of departures you would like to receive
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of departures with the following keys:
						destination:	trip's destination stop
						expected:		expected departure time of the bus for the given stop
						expected_mins:	number of minutes before expected departure time
						headsign:		information usually shown on headsign
						location:		latitude and longitude of vehicle
						is_monitored:	whether the vehicle is communicating
						is_scheduled:	whether this trip was scheduled
						origin:			trip's origin stop
						route:			route information for the trip
						scheduled:		scheduled departure time of the bus for the given stop
						stop_id:		id of the stop the bus will be at
						trip:			trip information for the departure
						vehicle_id:		id associated with vehicle
		*/	
		function getDeparturesByStop($stop_id, $route_id = NULL, $pt = 30, $count = NULL, $verbose = false) {
			$parameters = array(array("name"=>"stop_id","value"=>$stop_id));
			if(!is_null($route_id)) {
				if(is_array($route_id))
					foreach($route_id as $route)
						$routes .= ';'.urlencode($route);
				else
					$routes = urlencode($route_id);
				array_push($parameters,array("name"=>"route_id","value"=>$routes));
			}
			if($pt != 30 && $pt >=0 && $pt <= 60)
				array_push($parameters,array("name"=>"pt","value"=>$pt));
			if(!is_null($count))
				array_push($parameters,array("name"=>"count","value"=>$count));			
			$rsp = $this->getCachedData('GetDeparturesByStop',$parameters,true,$verbose);
			return $rsp["departures"];
		}
		
		/// Routes		
		/*
			Function: getRoute
			
			Purpose: Gets the route(s) specified
			
			Parameters: 
				route_id: 	(required) string or array of stings of bus route ID(s) (e.g. ILLINI is the route_id for the 22)
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of routes matching the specified ID(s) with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
		*/			
		function getRoute($route_id, $verbose = false) {
			if(is_array($route_id))
				foreach($route_id as $route)
					$routes .= ';'.urlencode($route);
			else
				$routes = urlencode($route_id);
			$parameters = array(array("name"=>"route_id","value"=>$routes));
			$rsp = $this->getCachedData('GetRoute',$parameters,true,$verbose);
			return $rsp["routes"];
		}
		/*
			Function: getRoute
			
			Purpose: Gets the entire list of routes 
			
			Parameters: 
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of routes with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
		*/			
		function getRoutes($verbose = false) {
			$rsp = $this->getCachedData('GetRoutes',array(),true,$verbose);
			return $rsp["routes"];
		}
		/*
			Function: getRoutesByStop
			
			Purpose: Gets the routes that travel to the specified stop
			
			Parameters: 
				stop_id: 	(required) id of the stop to get routes for (e.g. IU is the stop_id for Illini Union)
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of routes with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
		*/			
		function getRoutesByStop($stop_id, $verbose = false) {
			$rsp = $this->getCachedData('GetRoutesByStop',array(array("name"=>"stop_id","value"=>$stop_id)),true,$verbose);
			return $rsp["routes"];
		}
		
		/// Shapes
		/*
			Function: getShape
			
			Purpose: Gets a list of points that describe the path of the route on a map and how far a bus travels along that path
			
			Parameters: 
				shape_id: 	(required) id of the shape
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of shapes with the following keys:
						shape_dist_traveled:	total distance traveled to this point
						shape_pt_lat:			latitude of point
						shape_pt_lon:			longitude of point
						stop_id:				stop id associeated with the shape point
						shape_pt_sequence:		sequence of point in GTFS feed
		*/			
		function getShape($shape_id, $verbose = false) {
			$rsp = $this->getCachedData('GetShape',array(array("name"=>"shape_id","value"=>$shape_id)),true,$verbose);
			return $rsp["shapes"];
		}
		/*
			Function: getShapeBetweenStops
			
			Purpose: Gets a list of points that describe the path of the route on a map and how far a bus travels along that path
					 limited by the beginning and ending stop_ids
			
			Parameters: 
				begin_stop_id	(required) id of the beginning stop
				end_stop_id		(required) id of ending stop
				shape_id: 		(required) id of the shape
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of shapes with the following keys:
						shape_dist_traveled:	total distance traveled to this point
						shape_pt_lat:			latitude of point
						shape_pt_lon:			longitude of point
						stop_id:				stop id associeated with the shape point
						shape_pt_sequence:		sequence of point in GTFS feed
		*/			
		function getShapeBetweenStops($begin_stop_id, $end_stop_id, $shape_id, $verbose = false) {
			$parameters = array();
			array_push($parameters,array("name"=>"begin_stop_id","value"=>$begin_stop_id));
			array_push($parameters,array("name"=>"end_stop_id","value"=>$end_stop_id));
			array_push($parameters,array("name"=>"shape_id","value"=>$shape_id));
			$rsp = $this->getCachedData('GetShapeBetweenStops',$parameters,true,$verbose);
			return $rsp["shapes"];
		}
		
		/// Stops
		/*
			Function: getStop
			
			Purpose: Gets the specified stop
			
			Parameters: 
				stop_id:		(required) id of the stop
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
		*/
		function getStop($stop_id, $verbose = false) {
			if(is_array($stop_id))
				foreach($stop_id as $stop)
					$stops .= ';'.$stop;
			else
				$stops = $stop_id;
			$rsp = $this->getCachedData('GetStop',array("name"=>"stop_id","value"=>$stops),true,$verbose);
			return $rsp["stops"];
		}
		/*
			Function: getStops
			
			Purpose: Gets the entire list of stops (2500+)
			
			Parameters: 
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
		*/			
		function getStops($verbose = false) {
			$rsp = $this->getCachedData('GetStops',array(),true,$verbose);
			return $rsp["stops"];
		}
		/*
			Function: getStopsByLatLon
			
			Purpose: Gets the 20 stops closest to the specified latitude and longitude 
			
			Parameters:
				lat:			(required) latitude
				lon:			(required) longitude
				count:			(optional) number of stops to return (default is 20)
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
		*/			
		function getStopsByLatLon($lat, $lon, $count = 20, $verbose = false) {
			$parameters = array(array("name"=>"lat","value"=>$lat),array("name"=>"lon","value"=>$lon));
			if($count != 20) {
				if(is_numeric($count))
					array_push($parameters,array("name"=>"count","value"=>$count));
				else
					echo ($verbose) ? "Invalid count." : "";
			}
			$rsp = $this->getCachedData('GetStopsByLatLon',$parameters,true,$verbose);
			return $rsp["stops"];
		}
		/*
			Function: getStopsByLatLonWithinRadius
			
			Purpose: Gets the stops closest to the specified latitude and longitude within a specified radius
			
			Parameters:
				lat:			(required) latitude
				lon:			(required) longitude
				radius:			(required) radius in distance to limit the search for stops
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
		*/
		function getStopsByLatLonWithinRadius($lat, $lon, $radius, $verbose = false) {
			$maxNum = 25;
			$parameters = array(array("name"=>"lat","value"=>$lat),array("name"=>"lon","value"=>$lon),array("name"=>"count","value"=>$maxNum));
			$rsp = $this->getCachedData('GetStopsByLatLon',$parameters,true,$verbose);
			$stops = $rsp["stops"];
			$radius *= 5280; // convert miles to feet
			foreach($stops as $index => $stop) {
				if($stop["distance"] > $radius) {
					array_splice($stops,$index);
					return $stops;
				}
			}
			echo ($verbose) ? "Radial limit not reached." : "";
			return $stops;
		}
		/*
			Function: getStopsBySearch
			
			Purpose: Gets stops that match the query (can be string or a stop code (eg. MTD3121))
			
			Parameters:
				query:			(required) search query
				count:			(optional) number of stops to return (default is 10)
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
		*/			
		function getStopsBySearch($query, $count = 10, $verbose = false) {
			$parameters = array(array("name"=>"query","value"=>urlencode($query)));
			if($count != 10) {
				if(is_numeric($count) && $count >=1 && $count <= 100)
					array_push($parameters,array("name"=>"count","value"=>$count));
				else
					echo ($verbose) ? "Invalid count." : "";
			}
			$rsp = $this->getCachedData('GetStopsBySearch',$parameters,true,$verbose);
			return $rsp["stops"];
		}	
		
		/// Stop Times
		/*
			Function: getStopTimesByTrip
			
			Purpose: Gets the stops a trip will service as well as the scheduled times
			
			Parameters:
				trip_id:		(required) id of the trip
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stop times with the following keys:
						arrival_times:	scheduled time of arrival (HH:mm:ss)
						departure_time:	scheduled time of departure (HH:mm:ss)
						stop_id:		id of stop
						stop_sequence:	sequence of stop
						trip_id:		id of trip
		*/		
		function getStopTimesByTrip($trip_id, $verbose = false) {
			$rsp = $this->getCachedData('GetStopTimesByTrip',array(array("name"=>"trip_id","value"=>$trip_id)),true,$verbose);
			return $rsp["stop_times"];			
		} 	
		/*
			Function: getStopTimesByStop
			
			Purpose: Gets the stops for a specific stop
			
			Parameters:
				stop_id: 		(required) id of the stop (e.g. IU is the stop_id for Illini Union)
				route_id: 		(optional) string or array of stings of bus route id(s) (e.g. ILLINI is the route_id for the 22)
				date:			(optional) scheduled date (YYYY-MM-DD)
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of stop times with the following keys:
						arrival_times:	scheduled time of arrival (HH:mm:ss)
						departure_time:	scheduled time of departure (HH:mm:ss)
						stop_id:		id of stop
						stop_sequence:	sequence of stop
						trip_id:		id of trip
		*/	
		function getStopTimesByStop($stop_id, $route_id = NULL, $date = NULL, $verbose = false) {
			$parameters = array(array("name"=>"stop_id","value"=>$stop_id));
			if(!is_null($route_id)) {
				if(is_array($route_id))
					foreach($route_id as $route)
						$routes .= ';'.urlencode($route);
				else
					$routes = urlencode($route_id);
				array_push($parameters,array("name"=>"route_id","value"=>$routes));
			}
			if(!is_null($date))
				array_push($parameters,array("name"=>"date","value"=>$date));
			$rsp = $this->getCachedData('GetStopTimesByStop',$parameters,true,$verbose);
			return $rsp["stop_times"];			
		}
		
		/// Trip Planner
		/*
			Function: getPlannedTripsByLatLon
			
			Purpose: Provides up to three itineraries for completing the requested trip
			
			Parameters:
				origin_lat: 		(required) latitude of the origin
				origin_lon: 		(required) longitude of the origin
				destination_lat: 	(required) latitude of the destination
				destination_lon: 	(required) longitude of the destination
				date:				(optional) date (YYYY-MM-DD)
				time:				(optional) time (HH:MM)
				max_walk:			(optional) maximum allowed walking distance in miles (default is 0.5)
				minimize:			(optional) minimize walking, transfers or time (default is time)
				arrive_depart:		(optional) whether to plan the trip to arrive or depart at the specified time (default is depart)
				verbose: 			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of itineraries with the following keys:
						itinerary:	a single itinerary to complete the requested trip
						leg:		a single leg in an itinerary. this can be either riding or walking.
						walk:		a leg of the journey that requires walking.
						service:	a leg of the journey that requires riding. (see remarks for multiple services in a single leg)
						begin:		the starting point for a leg
						end:		the ending point for a leg
		*/		
		function getPlannedTripsByLatLon($origin_lat, $origin_lon, $destination_lat, $destination_lon, $date = NULL, $time = NULL, $max_walk = 0.5, $minimize = 'time', $arrive_depart = 'depart', $verbose = false) {
			$parameters = array(array("name"=>"origin_lat","value"=>$origin_lat),array("name"=>"origin_lon","value"=>$origin_lon),array("name"=>"destination_lat","value"=>$destination_lat),array("name"=>"destination_lon","value"=>$destination_lon));
			if(!is_null($date))
				array_push($parameters,array("name"=>"date","value"=>$date));
			if(!is_null($time))
				array_push($parameters,array("name"=>"time","value"=>$time));
			if($max_walk != 0.5 && $max_walk >= 0.1 && $max_walk <= 1)
				array_push($parameters,array("name"=>"max_walk","value"=>$max_walk));
			if($minimize != 'time' || $minimize == 'walking' || $minimize == 'transfers')
				array_push($parameters,array("name"=>"minimize","value"=>$minimize));
			if($arrive_depart != 'depart' || $arrive_depart == 'arrive')
				array_push($parameters,array("name"=>"arrive_depart","value"=>$arrive_depart));	
			$rsp = $this->getCachedData('GetPlannedTripsByLatLon',$parameters,true,$verbose);
			return $rsp["itineraries"];					
		}
		/*
			Function: getPlannedTripsByStops
			
			Purpose: Provides up to three itineraries for completing the requested trip
			
			Parameters:
				origin_stop_id: 		(required) stop id of the origin
				destination_stop_id: 	(required) stop id of the destination
				date:					(optional) date (YYYY-MM-DD)
				time:					(optional) time (HH:MM)
				max_walk:				(optional) maximum allowed walking distance in miles (default is 0.5)
				minimize:				(optional) minimize walking, transfers or time (default is time)
				arrive_depart:			(optional) whether to plan the trip to arrive or depart at the specified time (default is depart)
				verbose: 				(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of itineraries with the following keys:
						itinerary:	a single itinerary to complete the requested trip
						leg:		a single leg in an itinerary. this can be either riding or walking.
						walk:		a leg of the journey that requires walking.
						service:	a leg of the journey that requires riding. (see remarks for multiple services in a single leg)
						begin:		the starting point for a leg
						end:		the ending point for a leg
		*/			
		function getPlannedTripsByStops($origin_stop_id, $destination_stop_id, $date = NULL, $time = NULL, $max_walk = 0.5, $minimize = 'time', $arrive_depart = 'depart', $verbose = false) {
			$parameters = array(array("name"=>"origin_stop_id","value"=>$origin_stop_id),array("name"=>"destination_stop_id","value"=>$destination_stop_id));
			if(!is_null($date))
				array_push($parameters,array("name"=>"date","value"=>$date));
			if(!is_null($time))
				array_push($parameters,array("name"=>"time","value"=>$time));
			if($max_walk != 0.5 && $max_walk >= 0.1 && $max_walk <= 1)
				array_push($parameters,array("name"=>"max_walk","value"=>$max_walk));
			if($minimize != 'time' || $minimize == 'walking' || $minimize == 'transfers')
				array_push($parameters,array("name"=>"minimize","value"=>$minimize));
			if($arrive_depart != 'depart' || $arrive_depart == 'arrive')
				array_push($parameters,array("name"=>"arrive_depart","value"=>$arrive_depart));	
			$rsp = $this->getCachedData('GetPlannedTripsByStops',$parameters,true,$verbose);
			return $rsp["itineraries"];			
		}
		
		/// Trips
		/*
			Function: getTrip
			
			Purpose: Gets information about the specified trip
			
			Parameters:
				trip_id: 			(required) string or array of stings of trip id(s)
				verbose:			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
		*/		
		function getTrip($trip_id, $verbose = false) {
			$parameters = array();
			if(is_array($trip_id))
				foreach($trip_id as $trip)
					$trips .= ';'.urlencode($trip);
			else
				$trips = urlencode($trip_id);
			array_push($parameters,array("name"=>"trip_id","value"=>$trips));
			$rsp = $this->getCachedData('GetTrip',$parameters,true,$verbose);
			return $rsp["trips"];
		}
		/*
			Function: getTripsByBlock
			
			Purpose: Gets information about the specified trip given a block id
			
			Parameters:
				block_id: 			(required) id of block you would like trips for
				verbose:			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
		*/			
		function getTripsByBlock($block_id, $verbose = false) {
			$rsp = $this->getCachedData('GetTripsByBlock',array(array("name"=>"block_id","value"=>$block_id)),true,$verbose);
			return $rsp["trips"];		
		}
		/*
			Function: getTripsByRoute
			
			Purpose: Gets information about the specified trip given a route id
			
			Parameters:
				route_id: 			(required) id of route you would like trips for
				verbose:			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
		*/			
		function getTripsByRoute($route_id, $verbose = false) {
			$rsp = $this->getCachedData('GetTripsByRoute',array(array("name"=>"route_id","value"=>$route_id)),true,$verbose);
			return $rsp["trips"];		
		}
		
		/// Metadata
		/*
			Function: getLastFeedUpdate
			
			Purpose: Gets the time the feed was last updated
			
			Parameters:
				verbose:			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: An associative array the following keys:
						last_updated:	the date the feed was last updated
		*/		
		function getLastFeedUpdate($verbose = false) {
			$rsp = $this->getResponse('GetLastFeedUpdate',array(),true,$verbose);
			return $rsp["last_updated"];
		}
		
		/// Private functions
		private function getResponse($command, $parameters, $decode = true, $verbose = false) {
			if(isset($command)) {
				// check if file_get_contents is enabled and can open remote urls
				if(file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
					$url = $this->_apiUrl.'v'.$this->_version.'/'.$this->_format.'/'.$command.'?key='.$this->_apiKey;
					if(isset($parameters))
						foreach($parameters as $parameter)
							$url .= '&'.$parameter["name"].'='.$parameter["value"];
					// get json response from cumtd server
					$rsp = file_get_contents($url);
					// decode json into associative array
					$rspArray = json_decode($rsp,true);
					// check status of response
					$status = $this->checkStatus($rspArray["status"],$verbose);
					// response successful
					if($status == 200)
						return ($decode) ? $rspArray : $rsp;
					else
						return $status;
				} else {
					// cannot use file_get_contents method for retreiving response from cumtd
					echo $verbose ? 'Cannot load data from remote server.' : '';
					return false;
				}
				// add cURL later
			} else
				echo $verbose ? 'Invalid command parameter.' : '';
		}
		
		private function checkStatus($status, $verbose = false) {
			switch($status["code"]) {
				case 200:
					echo $verbose ? "The request was completed successfully: ".$status["msg"] : "";
					return 200;
					break;
				case 202:
					echo $verbose ? "The dataset has not been modified: ".$status["msg"] : "";
					return 202;
					break;
				case 400:
					echo $verbose ? "A parameter was invalid: ".$status["msg"] : "";
					return 400;
					break;
				case 401:
					echo $verbose ? "The API key provided is invalid: ".$status["msg"] : "";
					return 401;
					break;
				case 403:
					echo $verbose ? "The hourly request limit on the given key has been reached: ".$status["msg"] : "";
					return 403;
					break;
				case 404:
					echo $verbose ? "The requested method does not exist: ".$status["msg"] : "";
					return 404;
					break;
				case 500:
					echo $verbose ? "The server encountered an error: ".$status["msg"] : "";
					return 500;
					break;
				default:
					echo $verbose ? "Cannot process status: ".$status["msg"] : "";
					return 0;
					break;
			}
		}
		
		private function getCachedData($command, $parameters, $decode = true, $verbose = false) {
			// check if cache is enabled (default)
			if($this->_useCache === true) {
				// get data from cache
				$cache_json = $this->getDataFromCache($command,$parameters,false,$verbose);
				// check if cache was retrieved successfully
				if($cache_json !== false) {
					// decode cache to get changeset_id
					$cache = json_decode($cache_json,true);
					// check if any parameters were passed in and make new parameter array if none were
					if(!is_array($parameters))
						$parameters = array();
					// put changeset id in parameter array
					array_push($parameters,array("name"=>"changeset_id","value"=>$cache["changeset_id"]));
					// get dataset from server with changeset id from cached data
					$server_json = $this->getResponse($command,$parameters,false,$verbose);
					// remove changeset id from parameters array in case it is used later
					array_pop($parameters);
					// check if server data matches cached data and return cache if it does
					if($server_json == 202)
						return ($decode) ? $cache : $cache_json;
				}
			}
			// check if data retrieved in previous section and get data if not
			if(!isset($server_json))
				$server_json = $this->getResponse($command,$parameters,false,$verbose);
			// cache data from server
			$this->cacheData($server_json,$command,$parameters,$verbose);
			// decode response from server
			return ($decode) ? json_decode($server_json,true) : $server_json;		
		}
		
		private function cacheData($data, $command, $parameters, $verbose = false) {
			if(isset($data)) {
				$filename = $command;
				if(is_array($parameters)) {
					foreach($parameters as $parameter)
						$filename .= '&'.$parameter["name"].'='.$parameter["value"];
				}
				if(!is_dir($this->_cacheDir))
					mkdir($this->_cacheDir);
				return file_put_contents("$this->_cacheDir$filename.json",$data);
			} else {
				echo ($verbose) ? "Data list empty." : "";
				return false;
			}
		}
		
		private function getDataFromCache($command, $parameters, $decode = true, $verbose = false) {
			$filename = $command;
			if(is_array($parameters)) {
				foreach($parameters as $parameter)
					$filename .= '&'.$parameter["name"].'='.$parameter["value"];
			}
			// check if file can be found and opened
			if(($cache = @file_get_contents("$this->_cacheDir$filename.json")) !== false) {
				// cache file exists and was opened succesfully, check if it is empty
				if(!empty($cache)) {
					return ($decode) ? json_decode($cache,true) : $cache;				
				}			
			}
			echo ($verbose) ? "Could not open cache file or cache file was empty." : "";
			return false;	
		}
	}
?>