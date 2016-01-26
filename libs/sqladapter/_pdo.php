<?php
/**
 * @file _pdo.php
 * @brief Library to easily use PDO
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 * 
 * Library of PDO functions to easily use ODBC.
 * 
 * Useful constants:
 * LOGSPATH
 * PDOLOGFILENAME
 * 
 * Required functions:
 * bintest() (Core lib)
*/


defifn('DBCONF'				, 'database');

//Constantes PDO
define('PDOQUERY'			, 0);//Simple Query (SELECT ...). Returns a result set.
define('PDOEXEC'			, 1);//Simple Execution (INSERT INTO, UPDATE, DELETE ...). Returns the number of affected lines.

define('PDONOSTMT'			, PDOQUERY | 0<<1);//Continue, can not be used alone.
define('PDOSTMT'			, PDOQUERY | 1<<1);//Returns the PDOStatement without any treatment but does NOT free the connection.
define('PDOFETCH'			, PDOQUERY | 0<<2);//Query and simple Fetch (only one result) - Default
define('PDOFETCHALL'		, PDOQUERY | 1<<2);//Query and Fetch All (Set of all results)
define('PDOFETCHALLCOL'		, PDOQUERY | 0<<3);//All columns
define('PDOFETCHFIRSTCOL'	, PDOQUERY | 1<<3);//Only the first column

// define('PDOERROR_EXCEP'		, 0<<9);
// define('PDOERROR_NOEXC'		, 1<<9);
define('PDOERROR_FATAL'		, 0<<10);
define('PDOERROR_MINOR'		, 1<<10);

// define('PDOERROR_SILENT'	, PDOERROR_MINOR);
// define('PDOERROR_SILENT'	, PDOERROR_MINOR | PDOERROR_EXCEP);


function pdo_getDefaultInstance() {
	global $DBS;
	if( defined('PDODEFINSTNAME') ) {
		// Default is constant PDODEFINSTNAME
		$instance = PDODEFINSTNAME;
		
	} else if( !empty($DBS) && is_array($DBS) ) {
		if( is_array(current($DBS)) ) {
			// Default is the first value of the multidimensional array DB Settings
			$instance = key($DBS);
		} else {
			// Default is 'default' and value is all the contents of DB Settings
			$instance = 'default';
			$DBS[$instance] = $DBS;
		}
	} else {
		pdo_error('Database configuration NOT FOUND and no Instance defined by default with constant "PDODEFINSTNAME".', 'Instance Definition');
	}
	return $instance;
}

/** Ensures to be connected to the database.
 * @param $instance If supplied, this is the ID of the instance to use to execute the query. Optional, PDODEFINSTNAME constant by default.
 * @return	Instance ID used.
 * 
 * Ensures to provide a valid and connected instance of PDO, here are the steps:
 * If it is not loaded, this function attempts to load the database configuration file.
 * If not supplied as a parameter, this function attempts to determine an existing instance name.
 * If the instance is not connected, this function attempts to connect.
*/
function ensure_pdoinstance($instance=null) {
	global $pdoInstances, $DBS;
	
	//Check DB Settings File and Get DB Settings
	if( empty($DBS) ) {
// 		debug('Build '.DBCONF.' config ');
		$DBS	= Config::build(DBCONF, true);
// 		debug('$DBS on build', $DBS);
		$DBS	= $DBS->all;
	}
	
	// Using default instance
	if( empty($instance) ) {
		// Get from default
		$instance	= pdo_getDefaultInstance();
		
	} else if( empty($DBS[$instance]) ) {
		pdo_error('Parameter Instance " '.$instance.' " is unknown.', 'Instance Setting Definition');
	}
	
	if( !empty($pdoInstances[$instance]) ) {
		// Instance is already checked and loaded
		return $instance;
	}
	
	// Loading instance
	$instanceSettings = $DBS[$instance];

	try {
		//If There is no driver given, it is an error.
		if( empty($instanceSettings['driver']) ) {
			pdo_error('Database setting "driver" should have the driver name (not empty)', 'Driver Definition');
			
		//If driver is mysql
		} else if( $instanceSettings['driver'] == 'mysql' ) {
			//If Instance does not exist yet, it is not connected, we create it & link it.
			$instanceSettings["host"]	= ( empty($instanceSettings["host"])	) ? '127.0.0.1'	: $instanceSettings["host"];
			$instanceSettings["user"]	= ( empty($instanceSettings["user"])	) ? 'root'		: $instanceSettings["user"];
			$instanceSettings["passwd"]	= ( empty($instanceSettings["passwd"])	) ? ''			: $instanceSettings["passwd"];
			if( empty($instanceSettings["dbname"]) ) {
				pdo_error('Database setting "dbname" should have the database\'s name (not empty)', 'DB Name Definition');
			}
			$pdoInstances[$instance] = new PDO(
				"mysql:dbname={$instanceSettings["dbname"]};host={$instanceSettings["host"]}",
				$instanceSettings["user"], $instanceSettings["passwd"],
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::MYSQL_ATTR_DIRECT_QUERY=>true)
			);
			$pdoInstances[$instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
		//If driver is mssql
		} else if( $instanceSettings['driver'] == 'mssql' ) {
			//If Instance does not exist yet, it is not connected, we create it & link it.
			$instanceSettings["host"]	= ( empty($instanceSettings["host"])	) ? '127.0.0.1'	: $instanceSettings["host"];
			$instanceSettings["user"]	= ( empty($instanceSettings["user"])	) ? 'root'		: $instanceSettings["user"];
			$instanceSettings["passwd"]	= ( empty($instanceSettings["passwd"])	) ? ''			: $instanceSettings["passwd"];
			if( empty($instanceSettings["dbname"]) ) {
				pdo_error('Database setting "dbname" should have the database\'s name (not empty)', 'DB Name Definition');
			}
			$pdoInstances[$instance] = new PDO(
				"dblib:dbname={$instanceSettings["dbname"]};host={$instanceSettings["host"]}",
				$instanceSettings["user"], $instanceSettings["passwd"]
			);
			$pdoInstances[$instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
		} else if( $instanceSettings['driver'] == 'pgsql' ) {
			//If Instance does not exist yet, it is not connected, we create it & link it.
			$instanceSettings["host"]	= ( empty($instanceSettings["host"])	) ? '127.0.0.1'	: $instanceSettings["host"];
			$instanceSettings["user"]	= ( empty($instanceSettings["user"])	) ? 'root'		: $instanceSettings["user"];
			$instanceSettings["passwd"]	= ( empty($instanceSettings["passwd"])	) ? ''			: $instanceSettings["passwd"];
			if( empty($instanceSettings["dbname"]) ) {
				pdo_error('Database setting "dbname" should have the database\'s name (not empty)', 'DB Name Definition');
			}
			$pdoInstances[$instance] = new PDO(
				"pgsql:dbname={$instanceSettings["dbname"]};host={$instanceSettings["host"]};user={$instanceSettings["user"]};password={$instanceSettings["passwd"]}"
			);
			$pdoInstances[$instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
		} else if( $instanceSettings['driver'] == 'sqlite' ) {
			//If Instance does not exist yet, it is not connected, we create it & link it.
			$instanceSettings["path"]	= ( empty($instanceSettings["path"])	) ? ':memory:'	: $instanceSettings["path"];
			$pdoInstances[$instance] = new PDO(
				"sqlite:{$instanceSettings["path"]}"
			);
			$pdoInstances[$instance]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
	} catch( PDOException $e ) {
		pdo_error('PDO Exception: '.$e->getMessage(), 'DB Connection', 0, $e);
// 		throw $e;
	}
	return $instance;
}

function pdo_instance($instance) {
	global $pdoInstances;
	$instance	= ensure_pdoinstance($instance);
	return $pdoInstances[$instance];
}

/** Execute $Query
 * @param $Query The query to execute.
 * @param $Fetch See PDO constants above. Optional, default is PDOQUERY.
 * @param $instance The instance to use to execute the query. Optional, default is defined by ensure_pdoinstance().
 * @return The result of the query, of type defined by $Fetch.
 * 
 * Execute $Query on the instanciated database.
*/
function pdo_query($Query, $Fetch=PDOQUERY, $instance=null) {
	global $pdoInstances, $DBS;
	// Checks connection
	$instance		= ensure_pdoinstance($instance);
	if( empty($pdoInstances[$instance]) ) { return; }
	$instanceSettings	= $DBS[$instance];
	$pdoInstance	= $pdoInstances[$instance];
		
		
	if( in_array($instanceSettings['driver'], array('mysql', 'mssql', 'pgsql', 'sqlite')) ) {

		try {
			$ERR_ACTION	= 'BINTEST';
			if( bintest($Fetch, PDOEXEC) ) {// Exec
				$ERR_ACTION	= 'EXEC';
				return $pdoInstance->exec($Query);
			}
			$ERR_ACTION	= 'QUERY';
			$PDOSQuery	= $pdoInstance->query($Query);
			if( bintest($Fetch, PDOSTMT) ) {
				return $PDOSQuery;
			
			} else if( bintest($Fetch, PDOFETCHALL) ) {
				$ERR_ACTION	= 'FETCHALL';
				if( bintest($Fetch, PDOFETCHFIRSTCOL) ) {
					$returnValue = $PDOSQuery->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_COLUMN, 0);
				} else {
					$returnValue = $PDOSQuery->fetchAll(PDO::FETCH_ASSOC);
				}
				
			} else if( bintest($Fetch, PDOFETCH) ) {
				$ERR_ACTION	= 'FETCH';
				if( bintest($Fetch, PDOFETCHFIRSTCOL) ) {
					$returnValue = $PDOSQuery->fetchColumn(0);
				} else {
					$returnValue = $PDOSQuery->fetch(PDO::FETCH_ASSOC);
				}
				$PDOSQuery->fetchAll();
			}
			$PDOSQuery->closeCursor();
			unset($PDOSQuery);
			return $returnValue;
		} catch( PDOException $e ) {
			pdo_error($ERR_ACTION.' ERROR: '.$e->getMessage(), 'Query: '.$Query, $Fetch, $e);
// 			pdo_error($ERR_ACTION.' ERROR: '.$e->getMessage(), 'Query: '.$Query, $Fetch);
// 			throw $e;
			return false;
		}
	}
	//Unknown Driver
	pdo_error('Driver "'.$instanceSettings['driver'].'" does not exist or is not implemented yet.', 'Driver Definition');
}

/** Gets the last inserted ID
 * @param $instance The instance to use to get the last inserted id. Optional, default is defined by ensure_pdoinstance().
 * @return The last inserted id.
 * 
 * Gets the last inserted ID for this instance
 */
function pdo_lastInsertId($instance=null) {
	global $pdoInstances;
	$instance		= ensure_pdoinstance($instance);
	$pdoInstance	= $pdoInstances[$instance];
	$r = $pdoInstance->lastInsertId();
	return $r;
}

/** Log a PDO error
 * @param $report The report to save.
 * @param $Action Optional information about what the script was doing.
 * @param $Fetch The fetch flags, if PDOERROR_MINOR, this function does nothing. Optional, default value is 0.
 * @param $Original The original exception. Optional, default value is null.
 * 
 * Save the error report $report in the log file and throw an exception.
 */
function pdo_error($report, $Action='', $Fetch=0, $Original=null) {
	if( bintest($Fetch, PDOERROR_MINOR) ) { return; }
	sql_error($report, $Action);
	throw new SQLException($report, $Action, $Original);
}

/** Quotes and Escapes
 * @param $String The value to escape.
 * @return The quoted and escaped value.
 * 
 * Places quotes around the input string and escapes special characters within the input string, using the current instance.
 */
function pdo_quote($String) {
	//Old version, does not protect against SQL Injection.
	global $pdoInstances;
	$instance = ensure_pdoinstance();
	return $pdoInstances[$instance]->quote($String);
}
