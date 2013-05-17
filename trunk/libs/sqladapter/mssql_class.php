<?php
//! The MSSQL Adapter class
/*!
	This class is the sql adapter for MSSQL.
	
	Install method for debian:
	http://atutility.com/2007/09/14/install-pdo-pdo_sqlite-pdo_dblib-pdo_mysql
*/
class SQLAdapter_MSSQL extends SQLAdapter {
	
	protected static $IDFIELD = 'id';
	
	//! Defaults for selecting
	protected static $selectDefaults = array(
			'what'			=> '*',//* => All fields
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'number_percent'=> false,// false => No Percent option
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
			'number_percent'=> false,// false => No Percent option
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
			'number_percent'=> false,// false => No Percent option
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
	 	\sa http://msdn.microsoft.com/en-us/library/aa259187%28v=sql.80%29.aspx
	 	\sa SQLAdapter::select()
	 	
		Using pdo_query(), It parses the query from an array to a SELECT query.
    */
	public function select(array $options=array()) {
		text('MS SQL Adapter SELECT');
		$options += self::$selectDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No selection');
		}
		$OPTIONS = ( $options['number'] > 0 ) ?
			' TOP ('.$options['number'].')'.( ($options['number_percent']) ? ' PERCENT' : '' ) : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		
		$QUERY = "SELECT {$OPTIONS} {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = $this->query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return (!empty($results) && $options['output'] == static::ARR_ASSOC && $options['number'] == 1) ? $results[0] : $results;
	}
	
	//! The function to use for UPDATE queries
	/*!
		\param $options The options used to build the query.
		\return The number of affected rows.
		\sa http://msdn.microsoft.com/en-us/library/ms177523.aspx
		
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
		$SUBOPTIONS = '';
		$SUBOPTIONS .= ( $options['number'] > 0 ) ?
			' TOP ('.$options['number'].')'.( ($options['number_percent']) ? ' PERCENT' : '' ) : '';
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		
		$QUERY = "WITH q AS (
			SELECT {$SUBOPTIONS} * FROM {$options['table']} {$WC} {$ORDERBY}
		)
		UPDATE {$OPTIONS} q SET {$WHAT};";
		
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	//! The function to use for DELETE queries
	/*!
		\param $options The options used to build the query.
		\return The number of deleted rows.
		\sa http://msdn.microsoft.com/en-us/library/ms189835.aspx
	
		It parses the query from an array to a DELETE query.
	*/
	public function delete(array $options=array()) {
		text('MS SQL Adapter DELETE');
		$options += self::$deleteDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		$OPTIONS = '';
		$SUBOPTIONS = '';
		$SUBOPTIONS .= ( $options['number'] > 0 ) ?
			' TOP ('.$options['number'].')'.( ($options['number_percent']) ? ' PERCENT' : '' ) : '';
		$WC = ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		
		$QUERY = "WITH q AS (
			SELECT {$SUBOPTIONS} * FROM {$options['table']} {$WC} {$ORDERBY}
		)
		DELETE {$OPTIONS} FROM q;";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	//! The function to use for INSERT queries
	/*!
		\param $options The options used to build the query.
		\return The number of inserted rows.
		\sa http://msdn.microsoft.com/en-us/library/ms174335.aspx
		
		It parses the query from an array to a INSERT query.
		Accept only the String syntax for what option.
	*/
	public function insert(array $options=array()) {
		text('MS SQL Adapter INSERT');
		$options += self::$insertDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No field');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['into'])) ? ' INTO' : '';
		
		$COLS = $WHAT = '';
		// Is an array
		if( is_array($options['what']) ) {
			// Is an associative array
			if( !isset($options['what'][0]) ) {
				$options['what'] = array(0=>$options['what']);
			}
			// Indexed array to values string
			$COLS = '('.implode(', ', array_keys($options['what'][0])).')';
			foreach($options['what'] as $row) {
				$WHAT .= (!empty($WHAT) ? ', ' : '').'('.implode(', ', $row).')';
			}
			$WHAT = 'VALUES '.$WHAT;
			
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
		return $this->query("SELECT SCOPE_IDENTITY();", PDOFETCHFIRSTCOL);
	}
}