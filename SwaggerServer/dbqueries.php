<?php

use Symfony\Component\HttpFoundation\Response;

function is_base64($s) {
      return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
}

define("T_STR", 0);
define("T_INT", 1);
define("T_DATE", 2);
define("T_BLOB", 3);
define("T_FLOAT", 4);
define("T_DOUBLE", 5);

// Returns either FALSE or an array of row (objects)
function dbSelectQuery($query, $params, $types) {	

	// Connect to db
	$mysqli = new mysqli(...);
	// Prepare result
	$res = FALSE;
	
	if ($mysqli->connect_errno === 0) { // Connection success		
		
		// Escape strings
		$pos = 0;		
		foreach($params as $param) {
			if(is_string($param)) {
				//if(is_base64($param))
				//	$param = base64_decode($param);
				$param = $mysqli->real_escape_string($param);
			} else if($param instanceof DateTime) {
				$param = $param->format(DATE_ATOM);
			}
			$pos = strpos($query, "?");
			if ($pos !== false) {
				$query = substr_replace($query, $param, $pos, 1);
			}
		}
		unset($param);	
		
		//die($query);
		
		// Perform query
		$qr = $mysqli->query($query);		
		
		if($qr !== FALSE) {
			// Data query success
			$rows = array();
			while($row = mysqli_fetch_assoc($qr)) {
				$i = 0;
				foreach($row as $key => $val) {
					switch($types[$i++]) {
						case T_INT:
							$row[$key] = intval($val);
							break;
						case T_BLOB:
							$row[$key] = strval($val);
							break;	
						case T_FLOAT:							
							$row[$key] = floatval($val);
							break;
						case T_DOUBLE:						
							$row[$key] = doubleval($val);
							break;
						case T_DATE:							
						case T_STR:					
						default:						
							break;
					}
				}
				array_push($rows, $row);
			}			
			$res = $rows;
		}
		if(is_object($qr))
			$qr->close();
	}
	//die($query);
	// Close mysql connection
	mysqli_close($mysqli);
	// Return result
	return $res;
}

// Returns either TRUE or FALSE
function dbNonSelectQuery($query, $params) {	

	// Connect to db
	$mysqli = new mysqli(...);
	// Prepare result
	$res = FALSE;
	if ($mysqli->connect_errno === 0) { // Connection success
		// Escape strings
		$pos = 0;		
		foreach($params as $param) {
			if(is_string($param)) {
				//if(is_base64($param))
				//	$param = base64_decode($param);
				$param = $mysqli->real_escape_string($param);				
			} else if($param instanceof DateTime) {
				$param = $param->format(DATE_ATOM);
			} else if(is_numeric($param)) {								
				if(is_double($param))
					$param = doubleval($param);
				else if(is_float($param))
					$param = floatval($param);
				else
					$param = intval($param);
			}
			$pos = strpos($query, "?");
			if ($pos !== false) {
				$query = substr_replace($query, $param, $pos, 1);
			}
		}
		unset($param);
		
		//die($query);
	
		// Perform query
		$qr = $mysqli->query($query);		
							
		if($qr === TRUE) {
			$inserted = $mysqli->insert_id;
			$updated = mysqli_affected_rows($mysqli);
			if($inserted === 0 && $updated === 0)
				$res = FALSE;
			else {
				// Query succeeded, return last insert/update id
				$res = ($inserted !== 0 ? $inserted : $updated);
			}
		}
		if(is_object($qr))
			$qr->close();
	}
	// Close mysql connection
	mysqli_close($mysqli);
	// Return result
	return $res;
}

?>