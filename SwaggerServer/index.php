<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/dbqueries.php';
require_once __DIR__ . '/api_key_check.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

$app = new Silex\Application();

// Enable PHP Error level
error_reporting(E_ALL);
ini_set('display_errors','On');

// Enable debug mode
$app['debug'] = true;

function convertDate($str) {	
	//Sat May 06 01:01:01 EDT 2017
	//return DateTime::createFromFormat('D M d H:i:s T Y', $str);	
	//$dt = new DateTime();
	//$dt->setTimeZone(new DateTimeZone('UTC'));	
	//$dt->createFromFormat('D M d H:i:s T Y', $str);
	//return $dt;
	//return DateTime::createFromFormat('T Y M d H:i:s', $str);
	$ar = explode(" ", $str);
	$month = 1;
	$m = strtoupper($ar[1]);
	if(strcmp($m, "JAN") === 0) {
		$month = "01";
	} else if(strcmp($m, "FEB") === 0) {
		$month = "02";
	} else if(strcmp($m, "MAR") === 0) {
		$month = "03";
	} else if(strcmp($m, "APR") === 0) {
		$month = "04";
	} else if(strcmp($m, "MAY") === 0) {
		$month = "05";
	} else if(strcmp($m, "JUN") === 0) {
		$month = "06";
	} else if(strcmp($m, "JUL") === 0) {
		$month = "07";
	} else if(strcmp($m, "AUG") === 0) {
		$month = "08";
	} else if(strcmp($m, "SEP") === 0) {
		$month = "09";
	} else if(strcmp($m, "OCT") === 0) {
		$month = "10";
	} else if(strcmp($m, "NOV") === 0) {
		$month = "11";
	} else if(strcmp($m, "DEC") === 0) {
		$month = "12";
	}
	$str = "{$ar[5]}-{$month}-{$ar[2]} {$ar[3]} {$ar[4]}";
	return DateTime::createFromFormat('Y-m-d H:i:s T', $str);
}

$app->BEFORE(function(Request $request){
	
	return;

	// Check api key
	if(!api_key_check($request->headers->get('apikey')))
		return new Response('Error: invalid credentials', 401, array('X-Status-Code' => 401));

	$paths = explode('/', $request->server->get("REQUEST_URI"));
	$cmd1 = $paths[sizeof($paths)-1];
	$cmd2 = $paths[sizeof($paths)-2] . "/" . $paths[sizeof($paths)-1];
	$method = $request->server->get("REQUEST_METHOD");
	if( strcmp($cmd2, "user/login") === 0 || (strcmp($method, "POST") === 0 && strcmp($cmd1, "user") === 0) )
		return;

	// TODO add extra route security by hashing the received "timestamp" + some salt with the local token and comparing it to the received "hash" key.
	// 'SALT' . timestamp as long since epoch 1970 . 'SALT'
	// the token is retrieved from a MEMORY sql table engine which maps it to a public, secondary API_KEY that should be added to the conversation

	// Prepare params
	$callerId = intval($request->headers->get('callerId'));
	$timestamp = strval($request->headers->get('timestamp'));
	$hash = $request->headers->get('hash');

	// Prepare query
	$query = "SELECT * FROM `usersauth` WHERE `userId`=?";
	// Query
	$result = dbSelectQuery($query, array($callerId), [T_INT, T_DATE, T_STR]);

	if(!is_array($result) || count($result) != 1)
		return new Response('Error: invalid credentials', 401, array('X-Status-Code' => 401));
	
	$token = $result[0]["token"];
	$validuntil = $result[0]["validuntil"];

	if(strToTime($validuntil) < strToTime("now")) {
		dbNonSelectQuery("DELETE FROM `usersauth` WHERE `userId`=?", [$callerId]);
		return new Response('Error: invalid credentials', 401, array('X-Status-Code' => 401));
	}

	$stamp = "SALT{$timestamp}SALT";
	$computedHash = hash_hmac('sha256', $stamp, $token, false);
	/*echo "\nGIVEN\n";
	echo "\ntimestamp: "; print_r($timestamp);
	echo "\nhash: "; print_r($hash);
	echo "\n\nCOMPUTED\n";
	echo "\nsql result: "; print_r($result);
	echo "\nstamp: "; print_r($stamp);
	echo "\nhash: "; print_r($computedHash);
	die;*/

	if(!hash_equals($hash, $computedHash))
		return new Response('Error: invalid credentials', 401, array('X-Status-Code' => 401));

	// if expire then error

	//var_dump($callerId, $timestamp, $hash, $query, $result);
	//die;
});

//-------------------------------------------------------------------------------------------------
// Roles
//-------------------------------------------------------------------------------------------------

$app->GET('/api/1.1.0/roles', function(Application $app, Request $request) {
	// Prepare params
	// Prepare query
	$query = "SELECT * FROM Roles";			
	// Query
	$result = dbSelectQuery($query, [], [T_STR, T_STR]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {	
		return $app->json($result);
	}
});

//-------------------------------------------------------------------------------------------------
// Goals
//-------------------------------------------------------------------------------------------------


// CREATE new Goal by object
// IN: url encoded object
// OUT: JSON for "id"
$app->POST('/api/1.1.0/goal', function(Application $app, Request $request) {
	// Prepare params
	$userId = intval($request->get("userId"));
	$title = strval($request->get("title"));
	$description = strval($request->get("description"));
	$date = convertDate($request->get("date"));
	$priority = strval($request->get("priority"));
	$photo = strval($request->get("photo"));
	// Prepare query
	$query = "INSERT INTO `Goals`(`userId`,`title`,`description`,`date`,`priority`,`photo`) VALUES(?,'?','?','?','?','?')";			
	// Query
	$result = dbNonSelectQuery($query, [$userId, $title, $description, $date, $priority, $photo]);	
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));
	} else {	
		return $app->json(["id" => intval($result)], 201);
	}
});

// Read Goal by id
$app->GET('/api/1.1.0/goal/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = intval($request->get("id"));
	// Prepare query
	$query = "SELECT * FROM Goals WHERE id=?";			
	// Query
	$result = dbSelectQuery($query, [$id], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_STR, T_STR]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {		
		return $app->json($result[0]);
	}
});

// Read Goal array by date range
$app->GET('/api/1.1.0/findGoalByDateRange/{dateFrom}/{dateTo}', function(Application $app, Request $request, $dateFrom, $dateTo) {
	// Prepare params
	$userId = intval($request->headers->get("userId"));	
	$dateFrom = convertDate($request->get("dateFrom"));
	$dateTo = convertDate($request->get("dateTo"));
	// Prepare query
	if($userId > 0) // normal users
		$query = "SELECT * FROM Goals WHERE `userId`=? AND `date`>='?' AND `date`<='?' ORDER BY `date` ASC";	
	else // superadmin
		$query = "SELECT * FROM Goals WHERE `date`>='?' AND `date`<='?' ORDER BY `date` ASC";	
	// Query
	if($userId > 0)
		$result = dbSelectQuery($query, [$userId, $dateFrom, $dateTo], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_STR, T_STR]);
	else
		$result = dbSelectQuery($query, [$dateFrom, $dateTo], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_STR, T_STR]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result);
	}
});

// Read Goal array by id range
$app->GET('/api/1.1.0/findGoalByIdGroup/{idFrom}/{rowCount}', function(Application $app, Request $request, $idFrom, $rowCount) {
	// Prepare params
	$userId = intval($request->headers->get("userId"));
	$idFrom = intval($idFrom);
	$rowCount = intval($rowCount);
	// Prepare query
	$query = "SELECT * FROM Goals WHERE `userId`=? AND `id`>=? LIMIT ?";	
	// Query
	$result = dbSelectQuery($query, [$userId, $idFrom, $rowCount], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_STR, T_STR]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result);
	}
});

// Update goal by object
$app->PUT('/api/1.1.0/goal', function(Application $app, Request $request) {

	// Prepare params
	$id = intval($request->get("id"));
	$userId = intval($request->get("userId"));	
	$title = strval($request->get("title"));
	$description = strval($request->get("description"));
	$date = convertDate($request->get("date"));
	$priority = strval($request->get("priority"));
	$photo = strval($request->get("photo"));
	// Prepare query
	$query = "UPDATE `Goals` SET `title`='?', `description`='?', `date`='?', `priority`='?', `photo`='?' WHERE `id`=? AND `userId`=?";
	// Query
	$result = dbNonSelectQuery($query, [$title, $description, $date, $priority, $photo, $id, $userId]);	
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));		
	} else {
		return new Response ("Ok");
	}		
});

// Delete goal by id
$app->DELETE('/api/1.1.0/goal/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = $request->get("id");
	// Prepare query
	$query = "DELETE FROM `Goals` WHERE `id`=?";
	// Query
	$result = dbNonSelectQuery($query, [$id]);
	// Response
	if($result === FALSE) {		
		return new Response('Error:', 400, array('X-Status-Code' => 400));		
	} else {
		return new Response ("Ok");	
	}		
});

//-------------------------------------------------------------------------------------------------
// Tasks
//-------------------------------------------------------------------------------------------------


// CREATE new Task by object
// IN: url encoded object
// OUT: JSON for "id"
$app->POST('/api/1.1.0/task', function(Application $app, Request $request) {
	// Prepare params
	$title = strval($request->get("title"));
	$description = strval($request->get("description"));
	$date = convertDate($request->get("date"));
	$userId = intval($request->get("userId"));
	$duration = intval($request->get("duration"));
	$latitude = doubleval($request->get("latitude"));
	$longitude = doubleval($request->get("longitude"));
	// Prepare query
	$query = "INSERT INTO `Tasks`(`userId`,`title`,`description`,`date`,`duration`,`latitude`,`longitude`) VALUES(?,'?','?','?',?,?,?)";			
	// Query
	$result = dbNonSelectQuery($query, [$userId, $title, $description, $date, $duration, $latitude, $longitude]);	
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));
	} else {	
		return $app->json(["id" => intval($result)], 201);
	}
});

// Read Task by id
$app->GET('/api/1.1.0/task/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = intval($request->get("id"));
	// Prepare query
	$query = "SELECT * FROM Tasks WHERE id=?";			
	// Query
	$result = dbSelectQuery($query, [$id], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_INT, T_DOUBLE, T_DOUBLE]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {		
		return $app->json($result[0]);
	}
});

// Read Task array by date range
$app->GET('/api/1.1.0/findTaskByDateRange/{dateFrom}/{dateTo}', function(Application $app, Request $request, $dateFrom, $dateTo) {
	// Prepare params
	$userId = intval($request->headers->get("userId"));	
	$dateFrom = convertDate($request->get("dateFrom"));
	$dateTo = convertDate($request->get("dateTo"));
	// Prepare query
	if($userId > 0) // normal users
		$query = "SELECT * FROM Tasks WHERE `userId`=? AND `date`>='?' AND `date`<='?' ORDER BY `date` ASC";	
	else // superadmin
		$query = "SELECT * FROM Tasks WHERE `date`>='?' AND `date`<='?' ORDER BY `date` ASC";	
	// Query
	if($userId > 0)
		$result = dbSelectQuery($query, [$userId, $dateFrom, $dateTo], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_INT, T_DOUBLE, T_DOUBLE]);
	else
		$result = dbSelectQuery($query, [$dateFrom, $dateTo], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_INT, T_DOUBLE, T_DOUBLE]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result);
	}
});

// Read Task array by id range
$app->GET('/api/1.1.0/findTaskByIdGroup/{idFrom}/{rowCount}', function(Application $app, Request $request, $idFrom, $rowCount) {
	// Prepare params
	$userId = intval($request->headers->get("userId"));
	$idFrom = intval($idFrom);
	$rowCount = intval($rowCount);
	// Prepare query
	$query = "SELECT * FROM Tasks WHERE `userId`=? AND `id`>=? LIMIT ?";	
	// Query
	$result = dbSelectQuery($query, [$userId, $idFrom, $rowCount], [T_INT, T_INT, T_STR, T_STR, T_DATE, T_INT, T_DOUBLE, T_DOUBLE]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result);
	}
});

// Update task by object
$app->PUT('/api/1.1.0/task', function(Application $app, Request $request) {

	// Prepare params
	$id = intval($request->get("id"));
	$userId = intval($request->get("userId"));	
	$title = strval($request->get("title"));
	$description = strval($request->get("description"));
	$date = convertDate($request->get("date"));
	$duration = intval($request->get("duration"));
	$latitude = doubleval($request->get("latitude"));
	$longitude = doubleval($request->get("longitude"));
	// Prepare query
	$query = "UPDATE `Tasks` SET `title`='?', `description`='?', `date`='?', `duration`=?, `latitude`=?, `longitude`=? WHERE `id`=? AND `userId`=?";
	// Query
	$result = dbNonSelectQuery($query, [$title, $description, $date, $duration, $latitude, $longitude, $id, $userId]);	
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));		
	} else {
		return new Response ("Ok");
	}		
});

// Delete task by id
$app->DELETE('/api/1.1.0/task/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = $request->get("id");
	// Prepare query
	$query = "DELETE FROM `Tasks` WHERE `id`=?";
	// Query
	$result = dbNonSelectQuery($query, [$id]);
	// Response
	if($result === FALSE) {		
		return new Response('Error:', 400, array('X-Status-Code' => 400));		
	} else {
		return new Response ("Ok");	
	}		
});

//-------------------------------------------------------------------------------------------------
// Users
//-------------------------------------------------------------------------------------------------

// Create new User by object
// IN: url encoded object
// OUT: JSON for "id"
$app->POST('/api/1.1.0/user', function(Application $app, Request $request) {
	// Prepare params
	$username = $request->get("username");
	$password = $request->get("password");	
	$role = intval($request->get("role"));
	// Prepare query
	$query = "INSERT INTO `Users`(`username`,`password`,`role`) VALUES('?','?',?)";			
	// Query
	$result = dbNonSelectQuery($query, [$username, $password, $role]);
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));
	} else {	
		return $app->json(["id" => intval($result)], 201);
	}
});
		
// Read user by id
$app->GET('/api/1.1.0/user/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = intval($request->get('id'));	
	// Prepare query
	$query = "SELECT * FROM Users WHERE id=?";
	// Query
	$result = dbSelectQuery($query, [$id], [T_INT, T_STR, T_STR, T_INT, T_STR, T_DATE]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {		
		return $app->json($result[0]);
	}
});

// Read User array by id range
$app->GET('/api/1.1.0/findUserByIdGroup/{idFrom}/{rowCount}', function(Application $app, Request $request, $idFrom, $rowCount) {
	// Prepare params
	$idFrom = intval($idFrom);
	$rowCount = intval($rowCount);
	// Prepare query
	if($rowCount > 0) {
		$query = "SELECT * FROM Users WHERE `id`>=? LIMIT ?";
	} else {
		$query = "SELECT * FROM Users WHERE `id`>=?";
	}		
	// Query
	if($rowCount > 0) {
		$result = dbSelectQuery($query, [$idFrom, $rowCount], [T_INT, T_STR, T_STR, T_INT, T_STR, T_DATE]);
	} else {
		$result = dbSelectQuery($query, [$idFrom], [T_INT, T_STR, T_STR, T_INT, T_STR, T_DATE]);
	}
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result);
	}
});

// Update user
$app->PUT('/api/1.1.0/user', function(Application $app, Request $request) {
	// Prepare params
	$id = intval($request->get("id"));
	$username = $request->get("username");
	$password = $request->get("password");
	$role = intval($request->get("role"));
	// Prepare query
	$query = "UPDATE `Users` SET `username`='?', `password`='?', `role`=? WHERE `id`=?";	
	// Query
	$result = dbNonSelectQuery($query, [$username, $password, $role, $id]);
	// Response
	if($result === FALSE) {
		return new Response('Error', 400, array('X-Status-Code' => 400));		
	} else {	
		return new Response ("Ok");
	}
});
		
// Delete user by id
$app->DELETE('/api/1.1.0/user/{id}', function(Application $app, Request $request, $id) {
	// Prepare params
	$id = $request->get("id");
	// Prepare query
	$query = "DELETE FROM `Users` WHERE `id`=?";
	// Query
	$result = dbNonSelectQuery($query, [$id]);
	// Response
	if($result === FALSE) {		
		return new Response('Error:', 400, array('X-Status-Code' => 400));		
	} else {		
		// Prepare params
		$id = $request->get("id");
		// Prepare query
		$query = "DELETE FROM `Tasks` WHERE `userId`=?";
		// Query
		$result = dbNonSelectQuery($query, [$id]);
		return new Response ("Ok");
	}		
});

// Perform user login
$app->POST('/api/1.1.0/user/login', function(Application $app, Request $request) {
	// Prepare params
	$username = $request->get('username');
	$password = $request->get('password');
	// Prepare query
	$query = "SELECT * FROM `Users` WHERE `username`='?' AND `password`='?'";	
	// Query
	$result = dbSelectQuery($query, array($username, $password), [T_INT, T_STR, T_STR, T_INT]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Invalid username/password supplied', 401, array('X-Status-Code' => 401));
	} else {
		// Create token
		$token = bin2hex(openssl_random_pseudo_bytes(32));	
		// Make token expiration date
		$validuntil = new DateTime();
		$validuntil->modify("+7 day");	
		$date = $validuntil->format(DateTime::ATOM);
		// Save token and data to user
		$query = "INSERT `usersauth` SET `userId`=?, `token`='?', `validuntil`='?' ON DUPLICATE KEY UPDATE `token`='?', `validuntil`='?'";
		$user = $result[0];
		$userId = intval($user["id"]);
		$result2 = dbNonSelectQuery($query, array($userId, $token, $validuntil, $token, $validuntil));
		if($result2 === FALSE) {	
			return new Response('Error:', 400, array('X-Status-Code' => 400));					
		} else {
			// Objectify			
			return $app->json([
				"id" => intval($user["id"]),
				"username" => $user["username"],
				"password" => $user["password"],
				"role" => intval($user["role"]),
				"token" => $token,
				"valid_until" => $date
			]);
		}			
	}
});

// Perform user logout
$app->POST('/api/1.1.0/user/logout', function(Application $app, Request $request) {
	// Prepare params
	$userId = intval($request->headers->get("callerId"));
	// Prepare query
	$query = "DELETE FROM `usersauth` WHERE `userId`=?";
	// Query
	$result = dbNonSelectQuery($query, [$userId]);
	// Response
	if($result === FALSE) {
		return new Response('Error:', 400, array('X-Status-Code' => 400));					
	} else {
		return new Response ("Ok");
	}
});

//-------------------------------------------------------------------------------------------------
// Statistics
//-------------------------------------------------------------------------------------------------

$app->GET("/api/1.1.0/statistics/global/{userId}", function(Application $app, Request $request) {
	// Prepare params
	$userId = intval($request->get("userId"));
	// Prepare query	
	$query = "SELECT A.date, B.distanceTotal, B.timeTotal FROM ";
	$query .= " (";
	$query .= " SELECT date(`date`) as `date`, SUM(`duration`) as `sumduration`";
	$query .= " FROM Tasks";
	$query .= " WHERE `userId`=?";
	$query .= " GROUP BY `date`";
	$query .= " ORDER BY `sumduration` DESC";
	$query .= " LIMIT 0, 1";
	$query .= " ) AS A";
	$query .= " CROSS JOIN";
	$query .= " (";
	$query .= " SELECT SUM(`distance`) AS `distanceTotal`, SUM(`duration`) AS `timeTotal` FROM Tasks WHERE `userId`=?";
	$query .= " ) AS B";
	
	// Query
	$result = dbSelectQuery($query, [$userId, $userId], [T_DATE, T_FLOAT, T_FLOAT]);
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result[0]);
	}	
});

$app->GET("/api/1.1.0/statistics/periodic/{userId}/{dateFrom}/{dateTo}", function(Application $app, Request $request) {
	// Prepare params
	$userId = intval($request->get("userId"));
	$dateFrom = convertDate($request->get("dateFrom"));
	$dateTo = convertDate($request->get("dateTo"));	
	// Prepare query
	// ------------ SQL 
	//SELECT CONCAT(YEAR(`date`), '/', MONTH(`date`), '/', FLOOR(DAY(`date`)/7)) as sdate,
	//MAX(`distance`/`duration`) as speedMax,
    //MAX(`distance`) as distanceMax,
    //AVG(`distance`/`duration`) as speedAvg,
    //AVG(`distance`) as distanceAvg,
    //MIN(`distance`/`duration`) as speedMin,
    //MIN(`distance`) as distanceMin,
    //SUM(`duration`) as durationTotal,
    //SUM(`distance`) as distanceTotal
	//FROM Tasks
	//WHERE `userId`=1 AND `date`>='2017-06-02 00:00:00' AND `date`<'2017-07-09 00:00:00'
	// SQL --------------	
	$query = "SELECT '?' as dateFrom, '?' as dateTo,";
	$query .= "	MAX(`distance`/`duration`) as speedMax,";
    $query .= " MAX(`distance`) as distanceMax,";
    $query .= " AVG(`distance`/`duration`) as speedAvg,";
    $query .= " AVG(`distance`) as distanceAvg,";
	$query .= " MIN(`distance`/`duration`) as speedMin,";
    $query .= " MIN(`distance`) as distanceMin,";
    $query .= " SUM(`duration`) as durationTotal,";
    $query .= " SUM(`distance`) as distanceTotal";
	$query .= " FROM Tasks";
	$query .= " WHERE `userId`=? AND";
	$query .= " `date`>='?' AND `date`<'?'";
	// Query
	$result = dbSelectQuery($query, [$dateFrom, $dateTo, $userId, $dateFrom, $dateTo], [T_STR, T_STR, T_FLOAT, T_FLOAT, T_FLOAT, T_FLOAT, T_FLOAT, T_FLOAT, T_FLOAT, T_FLOAT]);	
	// Response
	if(FALSE === $result || !is_array($result) || count($result) == 0) {
		return new Response('Error', 404, array('X-Status-Code' => 404));
	} else {
		return $app->json($result[0]);
	}	
});

$app->run();

?>