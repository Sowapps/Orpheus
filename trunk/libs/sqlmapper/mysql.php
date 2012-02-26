<?php
class SQLMapper_MySQL {
	
	protected static $IDFIELD = 'id';
	
	//Defaults for selecting
	protected static $selectDefaults = array(
			'what'			=> '*',//* => All fields
			'whereclause'	=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> '2',//2 => ARR_ASSOC
	);
	//Defaults for updating
	protected static $updateDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'ignore'		=> false,//false => Not ignore errors
			'whereclause'	=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> '2',//2 => ARR_ASSOC
	);
	
	/*!
	 * \sa http://dev.mysql.com/doc/refman/5.0/en/select.html
	*/
	public static function select(array $options=array()) {
		$options += self::$selectDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No selection");
		}
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "SELECT {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return $results;
	}
	
	/*!
	 * \sa http://dev.mysql.com/doc/refman/5.0/en/update.html
	 */
	public static function update(array $options=array()) {
		$options += self::$updateDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No field");
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
			( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "UPDATE {$OPTIONS} {$options['table']} {$WHAT} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass 
			}
		}
		return $results;
	}
}