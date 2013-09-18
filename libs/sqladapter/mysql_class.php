<?php
//! The MYSQL Adapter class
/*!
	This class is the sql adapter for MySQL.
*/
class SQLAdapter_MySQL extends SQLAdapter {
	
	//! Defaults for selecting
	protected static $selectDefaults = array(
			'what'			=> '*',//* => All fields
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::ARR_ASSOC,//Associative Array
	);
	
	//! Defaults for updating
	protected static $updateDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'ignore'		=> false,//false => Not ignore errors
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::NUMBER,//Number of updated lines
	);
	
	//! Defaults for deleting
	protected static $deleteDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'quick'			=> false,//false => Not merge index leaves
			'ignore'		=> false,//false => Not ignore errors
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::NUMBER,//Number of deleted lines
	);
	
	//! Defaults for inserting
	protected static $insertDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'delayed'		=> false,//false => Not delayed
			'ignore'		=> false,//false => Not ignore errors
			'into'			=> true,//true => INSERT INTO
			'output'		=> SQLAdapter::NUMBER,//Number of inserted lines
	);
	
	//! The function to use for SELECT queries
    /*!
		\param $options The options used to build the query.
		\return Mixed return, depending on the 'output' option.
	 	\sa http://dev.mysql.com/doc/refman/5.0/en/select.html
	 	\sa SQLAdapter::select()
	 	
		Using pdo_query(), It parses the query from an array to a SELECT query.
    */
	public function select(array $options=array()) {
		$options += self::$selectDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No selection');
		}
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "SELECT {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = $this->query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return $options['output'] == static::ARR_FIRST ? $results[0] : $results;
// 		return (!empty($results) && $options['output'] == static::ARR_ASSOC && $options['number'] == 1) ? $results[0] : $results;
	}
	
	//! The function to use for UPDATE queries
	/*!
		\param $options The options used to build the query.
		\return The number of affected rows.
		\sa http://dev.mysql.com/doc/refman/5.0/en/update.html
		
		Using pdo_query(), It parses the query from an array to a UPDATE query.
	*/
	public function update(array $options=array()) {
		$options += self::$updateDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No field');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
	
		$QUERY = "UPDATE {$OPTIONS} {$options['table']} SET {$WHAT} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	//! The function to use for DELETE queries
	/*!
		\param $options The options used to build the query.
		\return The number of deleted rows.
	
		It parses the query from an array to a DELETE query.
	*/
	public function delete(array $options=array()) {
		$options += self::$deleteDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS .= (!empty($options['quick'])) ? ' QUICK' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
			( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "DELETE {$OPTIONS} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	//! The function to use for INSERT queries
	/*!
		\param $options The options used to build the query.
		\return The number of inserted rows.
		
		It parses the query from an array to a INSERT query.
		Accept only the String syntax for what option.
	*/
	public function insert(array $options=array()) {
		$options += self::$insertDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No field');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : (!empty($options['delayed'])) ? ' DELAYED' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$OPTIONS .= (!empty($options['into'])) ? ' INTO' : '';
		
		$COLS = $WHAT = '';
		//Is an array
		if( is_array($options['what']) ) {
			//Is an indexed array of fields Arrays
			if( !empty($options['what'][0]) ) {
				$COLS = '('.implode(', ', array_keys($options['what'][0])).')';
				foreach($options['what'] as $row) {
					$WHAT .= (!empty($WHAT) ? ', ' : '').'('.implode(', ', $row).')';
				}
				$WHAT = 'VALUES '.$WHAT;
			//Is associative fields Arrays
			} else {
				$WHAT = 'SET '.parseFields($options['what']);
			}
			
		//Is a string
		} else {
			$WHAT = $options['what'];
		}
		
		$QUERY = "INSERT {$OPTIONS} {$options['table']} {$COLS} {$WHAT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	//! The function to get the last inserted ID
	/*!
		\param $table The table to get the last inserted id.
		\param $idfield The field id name.
		\return The last inserted id value.
		
		It requires a successful call of insert() !
	*/
	public function lastID($table, $idfield='id') {
		return $this->query("SELECT LAST_INSERT_ID();", PDOFETCHFIRSTCOL);
	}
}