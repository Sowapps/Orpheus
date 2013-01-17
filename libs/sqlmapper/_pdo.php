<?php
/*!
	\file _pdo.php
	\brief Library to easily use PDO
	\author Florent Hazard
	\copyright The MIT License, see LICENSE.txt
	
Library of PDO functions to easily use ODBC.

Useful constants:
LOGSPATH
PDOLOGFILENAME

Required functions:
bintest() (_core.php)
*/

if( !defined("INSIDE") ) {
	return;
}

//Constantes PDO
define('PDOQUERY', 0);//Simple Query (SELECT ...). Returns a result set.
define('PDOEXEC', 1);//Simple Execution (INSERT INTO, UPDATA, DELETE ...). Returns the number of affected lines.

define('PDONOSTMT', PDOQUERY | 0<<1);//Continue, can not be used alone.
define('PDOSTMT', PDOQUERY | 1<<1);//Returns the PDOStatement without any treatment but does NOT free the connection.
define('PDOFETCH', PDOQUERY | 0<<2);//Query and simple Fetch (only one result) - Default
define('PDOFETCHALL', PDOQUERY | 1<<2);//Query and Fetch All (Set of all results)
define('PDOFETCHALLCOL', PDOQUERY | 0<<3);//All columns
define('PDOFETCHFIRSTCOL', PDOQUERY | 1<<3);//Only the first column

//! Ensures to be connected to the database.
/*
	\param $Instance If supplied, this is the ID of the instance to use to execute the query. Optional, PDODEFINSTNAME constant by default.
	\return	Instance ID used.

	Ensures to provide a valid and connected instance of PDO, here are the steps:
	If it is not loaded, this function attempts to load the database configuration file.
	If not supplied as a parameter, this function attempts to determine an existing instance name.
	If the instance is not connected, this function attempts to connect.
*/
function ensure_pdoinstance($Instance=null) {
	global $pdoInstances, $DBS;
	
	//Check DB Settings File and Get DB Settings
	if( empty($DBS) ) {
		$config = Config::build('database', true);
		$DBS = $config->all;
	}
	
	//Checking instance and getting its settings
	if( empty($Instance) ) {
		if( defined('PDODEFINSTNAME') ) {
			$Instance = PDODEFINSTNAME;
		} else if( !empty($DBS) && is_array($DBS) ) {
			if( is_array(current($DBS)) ) {
				$Instance = key($DBS);
			} else {
				$Instance = 'default';
				$DBS[$Instance] = $DBS;
			}
		} else {
			pdo_error('No instance given in parameter and no Instance defined by default with constant "PDODEFINSTNAME".', 'Instance Definition');
		}
	} else if( empty($DBS[$Instance]) ) {
		pdo_error('Parameter Instance is unknown.', 'Instance Setting Definition');
	}
	$InstSettings = $DBS[$Instance];
	
	//If There is no driver given, it is an error.
	if( empty($InstSettings['driver']) ) {
		pdo_error('Data Base setting "driver" should have the driver name (not empty)', 'Driver Definition');
		
	//If driver is mysql
	} else if( $InstSettings['driver'] == 'mysql' ) {
		//If Instance does not exist yet, it is not connected, we create it & link it.
		if( empty($pdoInstances[$Instance]) ) {
			$InstSettings["host"]	= ( empty($InstSettings["host"])	) ? '127.0.0.1'	: $InstSettings["host"];
			$InstSettings["user"]	= ( empty($InstSettings["user"])	) ? 'root'		: $InstSettings["user"];
			$InstSettings["passwd"]	= ( empty($InstSettings["passwd"])	) ? ''			: $InstSettings["passwd"];
			if( empty($InstSettings["dbname"]) ) {
				pdo_error('Data Base setting "dbname" should have the database\'s name (not empty)', 'DB Name Definition');
			}
			
			try {
				$pdoInstances[$Instance] = new PDO(
					"mysql:dbname={$InstSettings["dbname"]};host={$InstSettings["host"]}",
					$InstSettings["user"], $InstSettings["passwd"],
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::MYSQL_ATTR_DIRECT_QUERY=>true)
				);
				$pdoInstances[$Instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				pdo_error('PDO Exception: '.$e->getMessage(), 'DB Connection');
			}
		}
		
	} else if( $InstSettings['driver'] == 'pgsql' ) {
		//If Instance does not exist yet, it is not connected, we create it & link it.
		if( empty($pdoInstances[$Instance]) ) {
			$InstSettings["host"]	= ( empty($InstSettings["host"])	) ? '127.0.0.1'	: $InstSettings["host"];
			$InstSettings["user"]	= ( empty($InstSettings["user"])	) ? 'root'		: $InstSettings["user"];
			$InstSettings["passwd"]	= ( empty($InstSettings["passwd"])	) ? ''			: $InstSettings["passwd"];
			if( empty($InstSettings["dbname"]) ) {
				pdo_error('Data Base setting "dbname" should have the database\'s name (not empty)', 'DB Name Definition');
			}
			
			try {
				$pdoInstances[$Instance] = new PDO(
					"pgsql:dbname={$InstSettings["dbname"]};host={$InstSettings["host"]};user={$InstSettings["user"]};password={$InstSettings["passwd"]}"
				);
				$pdoInstances[$Instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				pdo_error('PDO Exception: '.$e->getMessage(), 'DB Connection');
			}
		}
		
	} else if( $InstSettings['driver'] == 'sqlite' ) {
		//If Instance does not exist yet, it is not connected, we create it & link it.
		if( empty($pdoInstances[$Instance]) ) {
			$InstSettings["path"]	= ( empty($InstSettings["path"])	) ? ':memory:'	: $InstSettings["path"];
			
			try {
				$pdoInstances[$Instance] = new PDO(
					"sqlite:{$InstSettings["path"]}"
				);
				$pdoInstances[$Instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				pdo_error('PDO Exception: '.$e->getMessage(), 'DB Connection');
			}
		}
	}
	return $Instance;
}

//! Executes $Query
/*
	\param $Query The query to execute.
	\param $Fetch See PDO constants above. Optionnal, default is PDOQUERY.
	\param $Instance The instance to use to execute the query. Optionnal, default is defined by ensure_pdoinstance().
	\return The result of the query, of type defined by $Fetch.
	
	Executes $Query on the instanciated database.
*/
function pdo_query($Query, $Fetch = PDOQUERY, $Instance=null) {
	global $pdoInstances, $DBS;
	
	//Check connection
	$Instance = ensure_pdoinstance($Instance);
	$InstSettings = $DBS[$Instance];
	$pdoInstance = $pdoInstances[$Instance];
		
		
	if( in_array($InstSettings['driver'], array('mysql', 'pgsql', 'sqlite')) ) {
	
		if( bintest($Fetch, PDOEXEC) ) { //Exec
			try {
				$returnValue = $pdoInstance->exec($Query);
			} catch (PDOException $e) {
				pdo_error("EXEC ERROR: ".$e->getMessage(), "Query:".$Query);
				return 0;
			}
			return $returnValue;
		} else { //Query
			try {
				$PDOSQuery = $pdoInstance->query($Query);
			} catch (PDOException $e) {
				pdo_error("QUERY ERROR: ".$e->getMessage(), "Query:".$Query);
				return 0;
			}
			if( bintest($Fetch, PDOSTMT) ) {
				return $PDOSQuery;
			
			} else if( bintest($Fetch, PDOFETCHALL) ) {
				try {
					if( bintest($Fetch, PDOFETCHFIRSTCOL) ) {
						$returnValue = $PDOSQuery->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_COLUMN, 0);
					} else {
						$returnValue = $PDOSQuery->fetchAll(PDO::FETCH_ASSOC);
					}
				} catch (PDOException $e) {
					pdo_error("FETCHALL ERROR: ".$e->getMessage(), "Query:".$Query);
					return 0;
				}
				
			} else if( bintest($Fetch, PDOFETCH) ) {
				try {
					if( bintest($Fetch, PDOFETCHFIRSTCOL) ) {
						$returnValue = $PDOSQuery->fetchColumn(0);
					} else {
						$returnValue = $PDOSQuery->fetch(PDO::FETCH_ASSOC);
					}
					$PDOSQuery->fetchAll();
				} catch (PDOException $e) {
					pdo_error("FETCH ERROR: ".$e->getMessage(), "Query:".$Query);
					return 0;
				}
			}
			$PDOSQuery->closeCursor();
			unset($PDOSQuery);
		}
		return $returnValue;
		
	}
	
	//Unknown Driver
	pdo_error('Driver "'.$InstSettings['driver'].'" does not exist or it is not implemented yet.', 'Driver Definition');
}

//! Logs a PDO error
/*
	\param $PDOReport The PDO report to save.
	\param $Action Optionnal information about what the script was doing.

	Saves the error report $PDOReport in the log file and exit script.
*/
function pdo_error($PDOReport, $Action='') {
	// Let's system manage this error (> R400)
	if( function_exists('sql_error') ) {
		sql_error($PDOReport, $Action);
		return;
	}
	// Manage error by myself (compatibility with olds version)
	$Error = array("date" => date('c'), "report" => $PDOReport, "action" => $Action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').( (defined("PDOLOGFILENAME")) ? PDOLOGFILENAME : '.pdo_error');
	file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
	die("An error has occured with the database, retry later please.");
}

//! Quotes and Escapes
/*
	\param $String The value to escape.
	\return The quoted and escaped value.

	Places quotes around the input string and escapes special characters within the input string, using the current instance.
*/
function pdo_quote($String) {
	//Old version, does not protect against SQL Injection.
	global $pdoInstances;
	$Instance = ensure_pdoinstance();
	return $pdoInstances[$Instance]->quote($String);
}