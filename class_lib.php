<?php
	/*
		Copyright 2011, 2012 Stanton T. Cady
		
		cumtd_php API v0.7 -- January 26, 2012
		
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
		public $_version;	// cumtd api version number as a string (default is 2.1)
					
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
				version: 	(optional) cumtd developer api version as a string. The first supported version is 2.0 (default is 2.1)
				cacheDir:	(optional) Directory to store cached data (default is ./cache/)
				useCache: 	(optional) Boolean variable to enable/disable caching data (default is true)
		*/
		function __construct($apiKey, $apiUrl = 'http://developer.cumtd.com/api/', $version = '2.1', $cacheDir = 'cache/', $useCache = true) {
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
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of calendar dates with the following keys:
						date:		a date this service operates
						service_id:	id for this service type
					 Otherwise:
					 	false
		*/			
		function getCalendarDatesByDate($date, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetCalendarDatesByDate',array(array("name"=>"date","value"=>$date)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["calendar_dates"] : json_encode($rsp["calendar_dates"]);			
		}
		/*
			Function: getCalendarDatesByDate
			
			Purpose: Gets all the dates that a specified services runs on
			
			Parameters: 
				service_id:	(required) id of the service
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of calendar dates with the following keys:
						date:		a date this service operates
						service_id:	id for this service type
					 Otherwise:
					 	false
		*/		
		function getCalendarDatesByService($service_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetCalendarDatesByService',array(array("name"=>"service_id","value"=>$service_id)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["calendar_dates"] : json_encode($rsp["calendar_dates"]);			
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
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of departures with the following keys:
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
					 Otherwise:
					 	false
		*/	
		function getDeparturesByStop($stop_id, $route_id = NULL, $pt = 30, $count = NULL, $decode = true, $verbose = false) {
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
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["departures"] : json_encode($rsp["departures"]);
		}
		
		/// Routes		
		/*
			Function: getRoute
			
			Purpose: Gets the route(s) specified
			
			Parameters: 
				route_id: 	(required) string or array of stings of bus route ID(s) (e.g. ILLINI is the route_id for the 22)
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success an associative array (default) or json string of routes matching the specified ID(s) with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
					 Otherwise:
					 	false
		*/			
		function getRoute($route_id, $decode = true, $verbose = false) {
			if(is_array($route_id))
				foreach($route_id as $route)
					$routes .= ';'.urlencode($route);
			else
				$routes = urlencode($route_id);
			$parameters = array(array("name"=>"route_id","value"=>$routes));
			$rsp = $this->getCachedData('GetRoute',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["routes"] : json_encode($rsp["routes"]);
		}
		/*
			Function: getRoute
			
			Purpose: Gets the entire list of routes 
			
			Parameters: 
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of routes with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
					 Otherwise:
					 	false
		*/			
		function getRoutes($decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetRoutes',array(),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["routes"] : json_encode($rsp["routes"]);
		}
		/*
			Function: getRoutesByStop
			
			Purpose: Gets the routes that travel to the specified stop
			
			Parameters: 
				stop_id: 	(required) id of the stop to get routes for (e.g. IU is the stop_id for Illini Union)
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of routes with the following keys:
						route_color:		hex color of route
						route_id:			id of route
						route_long_name:	long name
						route_short_name:	short name
						route_text_color:	hex color of text for route
					 Otherwise:
					 	false
		*/			
		function getRoutesByStop($stop_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetRoutesByStop',array(array("name"=>"stop_id","value"=>$stop_id)),true,$verbose);
			if($rsp === false)
				return false;
			else			
				return ($decode) ? $rsp["routes"] : json_encode($rsp["routes"]);
		}
		
		/// Shapes
		/*
			Function: getShape
			
			Purpose: Gets a list of points that describe the path of the route on a map and how far a bus travels along that path
			
			Parameters: 
				shape_id: 	(required) id of the shape
				decode:		(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of shapes with the following keys:
						shape_dist_traveled:	total distance traveled to this point
						shape_pt_lat:			latitude of point
						shape_pt_lon:			longitude of point
						stop_id:				stop id associeated with the shape point
						shape_pt_sequence:		sequence of point in GTFS feed
					 Otherwise:
					 	false
		*/			
		function getShape($shape_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetShape',array(array("name"=>"shape_id","value"=>$shape_id)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["shapes"] : json_encode($rsp["shapes"]);
		}
		/*
			Function: getShapeBetweenStops
			
			Purpose: Gets a list of points that describe the path of the route on a map and how far a bus travels along that path
					 limited by the beginning and ending stop_ids
			
			Parameters: 
				begin_stop_id	(required) id of the beginning stop
				end_stop_id		(required) id of ending stop
				shape_id: 		(required) id of the shape
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of shapes with the following keys:
						shape_dist_traveled:	total distance traveled to this point
						shape_pt_lat:			latitude of point
						shape_pt_lon:			longitude of point
						stop_id:				stop id associeated with the shape point
						shape_pt_sequence:		sequence of point in GTFS feed
					 Otherwise:
					 	false
		*/			
		function getShapeBetweenStops($begin_stop_id, $end_stop_id, $shape_id, $decode = true, $verbose = false) {
			$parameters = array();
			array_push($parameters,array("name"=>"begin_stop_id","value"=>$begin_stop_id));
			array_push($parameters,array("name"=>"end_stop_id","value"=>$end_stop_id));
			array_push($parameters,array("name"=>"shape_id","value"=>$shape_id));
			$rsp = $this->getCachedData('GetShapeBetweenStops',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["shapes"] : json_encode($rsp["shapes"]);
		}
		
		/// Stops
		/*
			Function: getStop
			
			Purpose: Gets the specified stop
			
			Parameters: 
				stop_id:		(required) id of the stop
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
					 Otherwise:
					 	false
		*/
		function getStop($stop_id, $decode = true, $verbose = false) {
			if(is_array($stop_id))
				foreach($stop_id as $stop)
					$stops .= ';'.$stop;
			else
				$stops = $stop_id;
			$rsp = $this->getCachedData('GetStop',array("name"=>"stop_id","value"=>$stops),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stops"] : json_encode($rsp["stops"]);
		}
		/*
			Function: getStops
			
			Purpose: Gets the entire list of stops (2500+)
			
			Parameters: 
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
					 Otherwise:
					 	false
		*/			
		function getStops($decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetStops',array(),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stops"] : json_encode($rsp["stops"]);
		}
		/*
			Function: getStopsByLatLon
			
			Purpose: Gets the 20 stops closest to the specified latitude and longitude 
			
			Parameters:
				lat:			(required) latitude
				lon:			(required) longitude
				count:			(optional) number of stops to return (default is 20)
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
					 Otherwise:
					 	false
		*/			
		function getStopsByLatLon($lat, $lon, $count = 20, $decode = true, $verbose = false) {
			$parameters = array(array("name"=>"lat","value"=>$lat),array("name"=>"lon","value"=>$lon));
			if($count != 20) {
				if(is_numeric($count))
					array_push($parameters,array("name"=>"count","value"=>$count));
				else
					echo ($verbose) ? "Invalid count." : "";
			}
			$rsp = $this->getCachedData('GetStopsByLatLon',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stops"] : json_encode($rsp["stops"]);
		}
		/*
			Function: getStopsByLatLonWithinRadius
			
			Purpose: Gets the stops closest to the specified latitude and longitude within a specified radius
			
			Parameters:
				lat:			(required) latitude
				lon:			(required) longitude
				radius:			(required) radius in distance to limit the search for stops
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
					 Otherwise:
					 	false
		*/
		function getStopsByLatLonWithinRadius($lat, $lon, $radius, $decode = true, $verbose = false) {
			$maxNum = 30;
			$parameters = array(array("name"=>"lat","value"=>$lat),array("name"=>"lon","value"=>$lon),array("name"=>"count","value"=>$maxNum));
			$rsp = $this->getCachedData('GetStopsByLatLon',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else {
				$stops = $rsp["stops"];	// get stops array
				$oC = count($stops);	// get original number of stops found
				$radius *= 5280; 		// convert miles to feet
				foreach($stops as $index => $stop) {
					if($stop["distance"] > $radius) {
						array_splice($stops,$index);
						break;
					}
				}
				if(empty($stops)) {
					echo ($verbose) ? "No stops found within specified radius." : "";
					return ($decode) ? "Error: no stops found." : json_encode(array("error"=>"No stops found."));
				} else {
					if(count($stops) == $oC && $verbose)
						echo "Radial limit not reached.";
					return ($decode) ? $stops : json_encode($stops);
				}
			}
		}
		/*
			Function: getStopsBySearch
			
			Purpose: Gets stops that match the query (can be string or a stop code (eg. MTD3121))
			
			Parameters:
				query:			(required) search query
				count:			(optional) number of stops to return (default is 10)
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stops with the following keys:
						code:		text message code
						point:		stop points that compose a parent stop (v2.0)
						stop_point: stop points that compose a parent stop (v2.1)
						rank:		(v2.0)
						percent:	how closely the the stop matches the query (v2.1)
						stop_id:	id of stop
						stop_lat:	latitude of stop
						stop_lon:	longitude of stop
						stop_name:	name of stop
					 Otherwise:
					 	false
		*/			
		function getStopsBySearch($query, $count = 10, $decode = true, $verbose = false) {
			$parameters = array(array("name"=>"query","value"=>urlencode($query)));
			if($count != 10) {
				if(is_numeric($count) && $count >=1 && $count <= 100)
					array_push($parameters,array("name"=>"count","value"=>$count));
				else
					echo ($verbose) ? "Invalid count." : "";
			}
			$rsp = $this->getCachedData('GetStopsBySearch',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stops"] : json_encode($rsp["stops"]);
		}	
		
		/// Stop Times
		/*
			Function: getStopTimesByTrip
			
			Purpose: Gets the stops a trip will service as well as the scheduled times
			
			Parameters:
				trip_id:		(required) id of the trip
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stop times with the following keys:
						arrival_times:	scheduled time of arrival (HH:mm:ss)
						departure_time:	scheduled time of departure (HH:mm:ss)
						stop_id:		id of stop
						stop_sequence:	sequence of stop
						trip_id:		id of trip
					 Otherwise:
						false
		*/		
		function getStopTimesByTrip($trip_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetStopTimesByTrip',array(array("name"=>"trip_id","value"=>$trip_id)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stop_times"] : json_encode($rsp["stop_times"]);			
		} 	
		/*
			Function: getStopTimesByStop
			
			Purpose: Gets the stops for a specific stop
			
			Parameters:
				stop_id: 		(required) id of the stop (e.g. IU is the stop_id for Illini Union)
				route_id: 		(optional) string or array of stings of bus route id(s) (e.g. ILLINI is the route_id for the 22)
				date:			(optional) scheduled date (YYYY-MM-DD)
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of stop times with the following keys:
						arrival_times:	scheduled time of arrival (HH:mm:ss)
						departure_time:	scheduled time of departure (HH:mm:ss)
						stop_id:		id of stop
						stop_sequence:	sequence of stop
						trip_id:		id of trip
					 Otherwise:
					 	false
		*/	
		function getStopTimesByStop($stop_id, $route_id = NULL, $date = NULL, $decode = true, $verbose = false) {
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
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["stop_times"] : json_encode($rsp["stop_times"]);			
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
				decode:				(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 			(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of itineraries with the following keys:
						start_time:		the time the itinerary will begin (v2.1)
						end_time:		the time the itinerary will end (v2.1)
						travel_time:	the total travel time in minutes (v2.1)
						itinerary:		a single itinerary to complete the requested trip
						leg:			a single leg in an itinerary. this can be either riding or walking.
						walk:			a leg of the journey that requires walking.
						service:		a leg of the journey that requires riding. (see remarks for multiple services in a single leg)
						begin:			the starting point for a leg
						end:			the ending point for a leg
					 Otherwise:
					 	false
		*/		
		function getPlannedTripsByLatLon($origin_lat, $origin_lon, $destination_lat, $destination_lon, $date = NULL, $time = NULL, $max_walk = 0.5, $minimize = 'time', $arrive_depart = 'depart', $decode = true, $verbose = false) {
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
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["itineraries"] : json_encode($rsp["itineraries"]);					
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
				decode:					(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose: 				(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of itineraries with the following keys:
						start_time:		the time the itinerary will begin (v2.1)
						end_time:		the time the itinerary will end (v2.1)
						travel_time:	the total travel time in minutes (v2.1)
						itinerary:		a single itinerary to complete the requested trip
						leg:			a single leg in an itinerary. this can be either riding or walking.
						walk:			a leg of the journey that requires walking.
						service:		a leg of the journey that requires riding. (see remarks for multiple services in a single leg)
						begin:			the starting point for a leg
						end:			the ending point for a leg
					 Otherwise:
					 	false
		*/			
		function getPlannedTripsByStops($origin_stop_id, $destination_stop_id, $date = NULL, $time = NULL, $max_walk = 0.5, $minimize = 'time', $arrive_depart = 'depart', $decode = true, $verbose = false) {
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
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["itineraries"] : json_encode($rsp["itineraries"]);			
		}
		
		/// Trips
		/*
			Function: getTrip
			
			Purpose: Gets information about the specified trip
			
			Parameters:
				trip_id: 		(required) string or array of stings of trip id(s)
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose:		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success, an associative array (default) or json string of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
					 Otherwise:
					 	false
		*/		
		function getTrip($trip_id, $decode = true, $verbose = false) {
			$parameters = array();
			if(is_array($trip_id))
				foreach($trip_id as $trip)
					$trips .= ';'.urlencode($trip);
			else
				$trips = urlencode($trip_id);
			array_push($parameters,array("name"=>"trip_id","value"=>$trips));
			$rsp = $this->getCachedData('GetTrip',$parameters,true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["trips"] : json_encode($rsp["trips"]);
		}
		/*
			Function: getTripsByBlock
			
			Purpose: Gets information about the specified trip given a block id
			
			Parameters:
				block_id: 		(required) id of block you would like trips for
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose:		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success an associative array (default) or json string of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
					 Otherwise:
					 	false
		*/			
		function getTripsByBlock($block_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetTripsByBlock',array(array("name"=>"block_id","value"=>$block_id)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["trips"] : json_encode($rsp["trips"]);		
		}
		/*
			Function: getTripsByRoute
			
			Purpose: Gets information about the specified trip given a route id
			
			Parameters:
				route_id: 		(required) id of route you would like trips for
				decode:			(optional) if set to true the function will return an associative array, otherwise a json string will be returned
				verbose:		(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success an associative array (default) or json string of trips with the following keys:
						route_id:		id of route
						service_id:		id of service
						shape_id:		id of shape
						trip_headsign:	information usually shown on headsign
						trip_id:		id of trip
					 Otherwise:
					 	false
		*/			
		function getTripsByRoute($route_id, $decode = true, $verbose = false) {
			$rsp = $this->getCachedData('GetTripsByRoute',array(array("name"=>"route_id","value"=>$route_id)),true,$verbose);
			if($rsp === false)
				return false;
			else
				return ($decode) ? $rsp["trips"] : json_encode($rsp["trips"]);		
		}
		
		/// Metadata
		/*
			Function: getLastFeedUpdate
			
			Purpose: Gets the time the feed was last updated
			
			Parameters:
				verbose:	(optional) boolean variable to enable/disable printing responses (useful for debugging)
				
			Returns: On success an associative array the following keys:
						last_updated:	the date the feed was last updated
					 Otherwise:
					 	false
		*/		
		function getLastFeedUpdate($verbose = false) {
			$rsp = $this->getResponse('GetLastFeedUpdate',array(),true,$verbose);
			if($rsp === false)
				return false;
			else
				return $rsp["last_updated"];
		}
		
		/// Private functions
		private function getResponse($command, $parameters, $decode = true, $verbose = false) {
			if(isset($command)) {
				// prepare url
				$url = $this->_apiUrl.'v'.$this->_version.'/'.$this->_format.'/'.$command.'?key='.$this->_apiKey;
				// append parameters to url
				if(isset($parameters))
					foreach($parameters as $parameter)
						$url .= '&'.$parameter["name"].'='.$parameter["value"];
				// Check if cURL is available
				if(extension_loaded("curl")) {
					echo ($verbose) ? "Using cURL to get response from cumtd.\n" : "";
					// Create new cURL session
					$ch = curl_init();
					// set URL and option to return result on success
					curl_setopt_array($ch,array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => 1));
					// get response from server
					$rsp = curl_exec($ch);
					// close cURL session
					curl_close($ch);					
				} elseif(file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
					echo ($verbose) ? "Using file_get_contents to get response from cumtd.\n" : "";
					// set context to allow server to close connection after download is complete
					$context = stream_context_create(array('http' => array('header'=>'Connection: close')));
					// get json response from cumtd server
					$rsp = file_get_contents($url,false,$context);
				} else {
					// both methods failed to retrieve response from cumtd
					echo ($verbose) ? "Cannot load data from remote server.\n" : "";
					return false;
				}
				if(isset($rsp) && $rsp !== false) {
					// decode json into associative array
					$rspArray = json_decode($rsp,true);
					// check status of response
					$status = $this->checkStatus($rspArray["status"],$verbose);
					// response successful
					if($status == 200)
						return ($decode) ? $rspArray : $rsp;
					else {
						if($this->_version == "2.0")
							return $status;
						else
							return false;
					}
				} else {
					echo ($verbose) ? "There was an error getting the response from the server.\n" : "";
					return false;
				}
			} else {
				echo $verbose ? "Invalid command parameter.\n" : "";
				return false;
			}
		}
		
		private function checkStatus($status, $verbose = false) {
			switch($status["code"]) {
				case 200:
					echo ($verbose) ? "The request was completed successfully: ".$status["msg"].".\n" : "";
					return 200;
					break;
				case 202:
					if($this->_version == "2.0") {
						echo ($verbose) ? "The dataset has not been modified: ".$status["msg"].".\n" : "";
						return 202;
					} else {
						echo ($verbose) ? "A 202 response code should not be returned in this version of the API.\n" : "";
						return 0;
					}						
					break;
				case 400:
					echo ($verbose) ? "A parameter was invalid: ".$status["msg"].".\n" : "";
					return 400;
					break;
				case 401:
					echo ($verbose) ? "The API key provided is invalid: ".$status["msg"].".\n" : "";
					return 401;
					break;
				case 403:
					echo ($verbose) ? "The hourly request limit on the given key has been reached: ".$status["msg"].".\n" : "";
					return 403;
					break;
				case 404:
					echo ($verbose) ? "The requested method does not exist: ".$status["msg"].".\n" : "";
					return 404;
					break;
				case 500:
					echo ($verbose) ? "The server encountered an error: ".$status["msg"].".\n" : "";
					return 500;
					break;
				default:
					echo ($verbose) ? "Cannot process status: ".$status["msg"].".\n" : "";
					return 0;
					break;
			}
		}
		
		private function getCachedData($command, $parameters, $decode = true, $verbose = false) {
			// check if cache is enabled (default)
			if($this->_useCache === true) {
				echo ($verbose) ? "Cache enabled.\n" : "";
				// get data from cache
				$cache_json = $this->getDataFromCache($command,$parameters,false,$verbose);
				// check if cache was retrieved successfully
				if($cache_json !== false) {
					echo ($verbose) ? "Cache file retrieved successfully.\n" : "";
					// decode cache to get changeset_id
					$cache = json_decode($cache_json,true);
					if(is_array($cache)) {
						// check if the changeset_id key exists in the cache data...dataset could be empty in which case there will be no changeset_id or it could be a dataset with no changeset_id
						if(array_key_exists("changeset_id",$cache)) {
							echo ($verbose) ? "Changeset_id exists, comparing cached data to data from server.\n" : "";
							// check if any parameters were passed in and make new parameter array if none were
							if(!is_array($parameters))
								$parameters = array();
							// put changeset id in parameter array
							array_push($parameters,array("name"=>"changeset_id","value"=>$cache["changeset_id"]));
							// get dataset from server with changeset id from cached data
							$server_json = $this->getResponse($command,$parameters,false,$verbose);
							// make sure there was no error getting the response
							if($server_json !== false) {
								// remove changeset id from parameters array in case it is used later
								array_pop($parameters);
								// check if server data matches cached data and return cache if it does
								if($this->_version == "2.0") {
									if($server_json == 202) {
										echo ($verbose) ? "Using cached data.\n" : "";
										return ($decode) ? $cache : $cache_json;
									}
								} else {
									$server = json_decode($server_json,true);
									if($server["new_changeset"] === false) {
										echo ($verbose) ? "Using cached data.\n" : "";
										return ($decode) ? $cache : $cache_json;
									}
								}
							} else
								// reset server_json variable so another attempt to get data from the server can be made
								unset($server_json);
						// If no changeset_id exists, check if the data contains a timestamp
						} elseif(array_key_exists("time",$cache)) {
							echo ($verbose) ? "Timestamp exists, comparing to last feed update timestamp.\n" : "";
							// compare the timestamp of the cached data to the last feed update timestamp
							if($cache["time"] == $this->getLastFeedUpdate($verbose)) {
								// feed has not been updated since cached data was retrieved
								echo ($verbose) ? "Using cached data.\n" : "";
								return ($decode) ? $cache : $cache_json;
							}
							echo ($verbose) ? "Feed has been updated since the dataset was cached.\n" : "";
						} else
							echo ($verbose) ? "Dataset contained in cache was empty and/or did not contain a changeset_id or data was stale.\n" : "";
					} else
						echo ($verobse) ? "Dataset was not decoded into array.\n" : "";
				} else
					echo ($verbose) ? "Cache file could not be accessed.\n" : "";
			} else
				echo ($verbose) ? "Cache is disabled.\n" : "";
			// check if data retrieved in previous section and get data if not
			if(!isset($server_json)) {
				echo ($verbose) ? "Getting new data from server.\n" : "";
				$server_json = $this->getResponse($command,$parameters,false,$verbose);
			}
			// check response from server
			if($server_json !== false) {
				// cache data from server
				if($this->cacheData($server_json,$command,$parameters,$verbose))
					echo ($verbose) ? "Data cached successfully.\n" : "";
				else
					echo ($verbose) ? "Data could not be cached.\n" : "";
				// decode response from server
				return ($decode) ? json_decode($server_json,true) : $server_json;
			} else
				return false;
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
				echo ($verbose) ? "Attempting to save cache to $this->_cacheDir$filename.json.\n" : "";
				// set context to allow server to close connection after upload is complete
				$context = stream_context_create(array('http' => array('header'=>'Connection: close')));
				return file_put_contents("$this->_cacheDir$filename.json",$data,0,$context);
			} else {
				echo ($verbose) ? "Data list empty." : "";
				return false;
			}
		}
		
		private function getDataFromCache($command, $parameters, $decode = true, $verbose = false) {
			// base of filename of cache file is the command name
			$filename = $command;
			// append parameters to filename
			if(is_array($parameters)) {
				foreach($parameters as $parameter)
					$filename .= '&'.$parameter["name"].'='.$parameter["value"];
			}
			// construct full filename
			$filename = "$this->_cacheDir$filename.json";
			// Check if cURL is available
			if(extension_loaded("curl")) {
				$filename = $_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/".$filename;
				echo ($verbose) ? "Using cURL to open cache file.\n" : "";
				// Create new cURL session
				$ch = curl_init();
				// set URL and option to return result on success
				curl_setopt_array($ch,array(CURLOPT_URL => $filename, CURLOPT_RETURNTRANSFER => 1));
				// get cache file
				$cache = curl_exec($ch);
				// close cURL session
				curl_close($ch);
			// check if file_get_contents works for local files				
			} elseif(file_get_contents(__FILE__)) {
				echo ($verbose) ? "Using file_get_contents to open cache file.\n" : "";
				// set context to allow server to close connection after download is complete
				$context = stream_context_create(array('http' => array('header'=>'Connection: close')));
				// get cache file
				$cache = file_get_contents($filename,false,$context);
			} else
				// both methods failed to retrieve the cache file
				echo ($verbose) ? "Cannot load data from cache file.\n" : "";
			if(isset($cache) && $cache !== false) {
				// cache file exists and was opened succesfully, check if it is empty
				if(!empty($cache)) {
					echo ($verbose) ? "Cache file opened successfully and is not empty.\n" : "";
					return ($decode) ? json_decode($cache,true) : $cache;				
				}
				echo ($verbose) ? "Cache file empty.\n" : "";
			} else
				echo ($verbose) ? "There was an error getting the cache file.\n" : "";
			return false;
		}
	}
?>