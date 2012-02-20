<?php
class SQLMapper_MySQL {
	
	protected static $IDFIELD = 'id';
	
	//Defaults for getting list
	protected static $defaults = array(
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'what'			=> '*',//* => All fields
			'output'		=> '2',//2 => ARR_ASSOC
			'whereclause'	=> '',//Additionnal Whereclause
	);
	//Defaults for selecting
	protected static $selectDefaults = array();
	//Defaults for updating
	protected static $updateDefaults = array();
	
	public static function select(array $options=array()) {
		$options += self::$defaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No selection");
		}
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
			( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$QUERY = "SELECT {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = new static($r);//TODO: May use stdObject class 
			}
		}
		return $results;
	}
}