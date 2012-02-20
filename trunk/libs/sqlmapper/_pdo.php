<?php
/* sqlmapper/_pdo.php
 * PHP File for included functions: PDO
 * [EN] Library of PDO functions to use ODBC.
 *
 * Auteur: Florent Hazard.
 * Revision: 24
 * Last edition: 19/08/2011

Required constants
LOGSPATH
PDOLOGFILENAME

PDODEFDBSFILE	Required only if DBS file not included
INCPATH			Useful only if DBS file not included

Required functions:
bintest() (lib system)
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

/*
Description: [String] ensure_pdoinstance( [$Instance=null] )
Ensures to provide a valid and connected instance of PDO, here are the steps:
If it is not loaded, this function attempts to load the database configuration file.
If not supplied as a parameter, this function attempts to determine an existing instance name.
If the instance is not connected, this function attempts to connect.

List of parameters:
- $Instance: If supplied, this is the ID of the instance to use to execute the query. Optional, PDODEFINSTNAME constant by default.

Return values:
Instance ID used.
*/
function ensure_pdoinstance($Instance=null) {
	global $pdoInstances, $DBS;
	
	//Check DB Settings File and Get DB Settings
	if( empty($DBS) ) {
		if( !defined('PDODEFDBSFILE') ) {
			pdo_error('Constant "PDODEFDBSFILE" must be defined.', 'Getting DB Settings');
		} else {
			if( defined('INCPATH') && is_readable(INCPATH.PDODEFDBSFILE) ) {
				$DBsFile = INCPATH.PDODEFDBSFILE;
				require_once INCPATH.PDODEFDBSFILE;
			} elseif( is_readable(PDODEFDBSFILE) ) {
				$DBsFile = PDODEFDBSFILE;
				require_once PDODEFDBSFILE;
			} else {
				pdo_error('File '.PDODEFDBSFILE.' must exist and be readable.', 'Getting DB Settings');
			}
			if( empty($DBS) || !is_array($DBS) ) {
				pdo_error('$DBS is empty in '.$DBsFile.' or it is not an Array.', 'Getting DB Settings');
			}
		}
	}
	
	//Checking instance and getting its settings
	if( empty($Instance) ) {
		if( defined('PDODEFINSTNAME') ) {
			$Instance = PDODEFINSTNAME;
		} else if( !empty($DBS) && is_array($DBS) ) {
			$Instance = 'default';
			$DBS[$Instance] = $DBS;
		} else {
			pdo_error('No instance given in parameter #3 and no Instance defined by default with constant "PDODEFINSTNAME".', 'Instance Definition');
		}
	} else if( empty($DBS[$Instance]) ) {
		pdo_error('Parameter Instance is wrong.', 'Instance Setting Definition');
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
	}
	return $Instance;
}

/*
Description: [Array | PDOStatement Object] pdo_query( $Query[, $Fetch = PDOQUERY[, $Instance=null]] )
Execute la requete $Query sur la base de donnees instanciee.

Liste de parametres:
- $Query: La requete a executer.
- $Fetch: Voir les constantes PDO ci-dessus. Optionnel, PDOQUERY par defaut.
- $Instance: Si donnée, il s'agit de l'instance à utiliser pour exécuter la requête. Optionnel, constante PDODEFINSTNAME par défaut ou racine de $DBS.

Valeurs de retour:
Le resultat de la requete, du type defini par $Fetch.
*/
function pdo_query($Query, $Fetch = PDOQUERY, $Instance=null) {
	global $pdoInstances, $DBS;
	
	//Check connection
	$Instance = ensure_pdoinstance($Instance);
	$InstSettings = $DBS[$Instance];
	$pdoInstance = $pdoInstances[$Instance];
		
		
	if( $InstSettings['driver'] == 'mysql' ) {
	
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

/*
Description: void pdo_error( $PDOReport[, $Action=''] )
Enregistre le rapport d'erreur $PDOReport dans le fichier de log et termine l'exécution du script.

Liste de parametres:
- $PDOReport: Le rapport à enregistrer dans le fichier.
- $Action: Note d'action en cours à enregistrer. Optionnel, Vide par defaut.

Valeurs de retour:
Rien, le script est termine.
*/
function pdo_error($PDOReport, $Action='') {
	if( function_exists('log') ) {
		log_error($PDOReport, (defined("PDOLOGFILENAME")) ? PDOLOGFILENAME : '.pdo_error', $Action);
	}
	$Error = array("time" => TIME, "pdo_report" => $PDOReport, "Action" => $Action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').( (defined("PDOLOGFILENAME")) ? PDOLOGFILENAME : '.pdo_error');
	file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
	die("An error has occured with the database, retry later please.");
}

/*
Description: String pdo_select( String $String )
Securise $String a la sauce PDO avec la methode quote().

Liste de parametres:
- $String: La chaine de caractere a securiser.

Valeurs de retour:
La chaine protegee.
*/
function pdo_quote($String) {
	global $pdoInstances;
	$Instance = ensure_pdoinstance();
	return $pdoInstances[$Instance]->quote($String);
}