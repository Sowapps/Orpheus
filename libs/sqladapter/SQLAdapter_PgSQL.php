<?php
/** The MYSQL Adapter class

	This class is the sql adapter for MySQL.
*/
class SQLAdapter_PgSQL extends SQLAdapter {
	
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
			'only'			=> false,//false => Do not ignore descendants
			'where'			=> '',//Additionnal Whereclause
			'output'		=> SQLAdapter::NUMBER,//Number of updated lines
	);
	
	//! Defaults for deleting
	protected static $deleteDefaults = array(
			'only'			=> false,//false => Do not ignore descendants
			'where'			=> '',//Additionnal Whereclause
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
	
	/** The function to use for SELECT queries
	 * @param $options The options used to build the query.
	 * @return Mixed return, depending on the 'output' option.
	 * @sa http://dev.mysql.com/doc/refman/5.0/en/select.html
	 * @sa SQLAdapter::select()
	 * 
	 * Using pdo_query(), It parses the query from an array to a SELECT query.
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
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.$options['number'] : '';
		$OFFSET = ( !empty($options['offset']) ) ? 'OFFSET '.$options['offset'] : '';// Not SQL:2008
		
		$QUERY = "SELECT {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT} {$OFFSET};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = $this->query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return (!empty($results) && $options['output'] == static::ARR_FIRST) ?  $results[0] : $results;
	}
	
	/** The function to use for UPDATE queries
	 * @param $options The options used to build the query.
	 * @return The number of affected rows.
	 * @sa http://dev.mysql.com/doc/refman/5.0/en/update.html
	 * 
	 * Using pdo_query(), It parses the query from an array to a UPDATE query.
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
		$OPTIONS .= (!empty($options['only'])) ? ' ONLY' : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
	
		$QUERY = "UPDATE {$OPTIONS} {$options['table']} SET {$WHAT} {$WC};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to use for DELETE queries
	 * @param $options The options used to build the query.
	 * @return The number of deleted rows.
	 * 
	 * It parses the query from an array to a DELETE query.
	*/
	public function delete(array $options=array()) {
		$options += self::$deleteDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['only'])) ? ' ONLY' : '';
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		
		$QUERY = "DELETE {$OPTIONS} FROM {$options['table']} {$WC};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to use for INSERT queries
	 * @param $options The options used to build the query.
	 * @return The number of inserted rows.
	 * 
	 * It parses the query from an array to a INSERT query.
	 * Accept only the String syntax for what option.
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
		
		$COLS = $WHAT = '';
		//Is an array
		if( is_array($options['what']) ) {
			//Is associative fields Arrays
			if( empty($options['what'][0]) ) {
				$options['what'] = array($options['what']);
			}// Else it's an indexed array of fields Arrays
			// Quoted as escapeIdentifier()
			$COLS = '("'.implode('", "', array_keys($options['what'][0])).'")';
			foreach($options['what'] as $row) {
				$WHAT .= (!empty($WHAT) ? ', ' : '').'('.implode(', ', $row).')';
			}
			$WHAT = 'VALUES '.$WHAT;
			
		//Is a string
		} else {
			$WHAT = $options['what'];
		}
		
		$QUERY = "INSERT INTO {$options['table']} {$COLS} {$WHAT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to get the last inserted ID
	 * @param $table The table to get the last inserted id.
	 * @return The last inserted id value.
	 * 
	 * It requires a successful call of insert() !
	*/
	public function lastID($table) {
		return $this->query("SELECT currval('{$table}_{$this->IDFIELD}_seq'::regclass)", PDOFETCHFIRSTCOL);
	}
}