<?php 

include_once("class_lib.php");
$api = new cumtd('');

if(isset($_GET["stop_id"])) {
	$departures = $api->getDeparturesByStop($_GET["stop_id"]);
	if($departures !== false) {
		echo '<ul style="width: 300px;">';
		foreach($departures as $departure) {
			$route = $departure["route"];
			$trip = $departure["trip"];
			echo '<li style="padding: 5px; display:block; background: #',$route["route_color"],'; color: #',$route["route_text_color"],';"><a href="?route_id=',$route["route_id"],'">',$departure["headsign"],'</a> arrives in ',$departure["expected_mins"],' min</li>';
		}
		echo '</ul>';
	} else
		echo 'Error retreiving departures for that stop.';
} elseif(isset($_GET["route_id"])) {
	$routes = $api->getRoute($_GET["route_id"]);
	foreach($routes as $route) {
		echo '<li style="padding: 5px; display:block;"><a href="?stop_id=',$stop["stop_id"],'">', $route["route_long_name"] ,'</a></li>';
	}
	echo '</ul>';
} elseif(isset($_GET["lat"]) && isset($_GET["lon"])) {
	if(isset($_GET["limit"]) && is_numeric($_GET["limit"]))
		$stops = $api->getStopsByLatLon($_GET["lat"],$_GET["lon"],$_GET["limit"]);
	else
		$stops = $api->getStopsByLatLon($_GET["lat"],$_GET["lon"]);
	echo '<ul style="width: 300px;">';
	foreach($stops as $stop) {
		echo '<li style="padding: 5px; display:block;"><a href="?stop_id=',$stop["stop_id"],'">', $stop["stop_name"] ,'</a></li>';
	}
	echo '</ul>';
} elseif(isset($_GET["q"])) {
	$stops = $api->getStopsBySearch($_GET["q"]);
	echo '<ul style="width: 300px;">';
	foreach($stops as $stop) {
		echo '<li style="padding: 5px; display:block;"><a href="?stop_id=',$stop["stop_id"],'">', $stop["stop_name"] ,'</a></li>';
	}
	echo '</ul>';
} elseif(isset($_GET["show"])) {
	if($_GET["show"] == "stops") {
		echo '<form method="get"><input type="text" id="q" name="q" placeholder="Search for stop..." /><input type="submit" /></form>';
		$stops = $api->getStops();
		echo '<ul style="width: 1000px;">';
		foreach($stops as $index => $stop) {
			echo '<li style="padding: 5px; display:block;"><a href="?stop_id=',$stop["stop_id"],'">', $stop["stop_name"], '</a></li>';
		}
		echo '</ul>';
	} elseif($_GET["show"] == "routes") {
		$routes = $api->getRoutes();
		echo '<ul style="width: 1000px;">';
		foreach($routes as $index => $route) {
			echo '<li style="padding: 5px; display:block;"><a href="?route_id=',$route["route_id"],'">', $route["route_short_name"], ' ', $route["route_long_name"], '</a></li>';
		}
		echo '</ul>';	
	}
}

?>