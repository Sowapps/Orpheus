<?php
/**
 * The main SQL Select Request class
 * 
 * This class handles sql SELECT request to the DMBS server.
 */
class SQLSelectRequest extends SQLRequest {

	public function fields($fields=null) {
		return $this->sget('what', $fields);
	}

	public function where($condition=null) {
		return $this->sget('where', $condition);
	}

	public function orderby($fields=null) {
		return $this->sget('orderby', $fields);
	}

	public function groupby($field) {
		return $this->sget('groupby', $field);
	}

	public function limit($limit) {
		return $this->sget('number', $limit);
	}

	public function join($join) {
		$joins		= $this->get('join', array());
		$joins[]	= $join;
		return $this->sget('join', $joins);
	}

	public function asObject() {
// 		debug('SQLAdapter::OBJECT', SQLAdapter::OBJECT);
		return $this->output(SQLAdapter::OBJECT);
	}

	public function asObjectList() {
		return $this->output(SQLAdapter::ARR_OBJECTS);
	}
// 			'what'			=> '',//table.* => All fields
// 			'join'			=> '',//* => All fields
// 			'where'			=> '',//Additionnal Whereclause
// 			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
// 			'number'		=> -1,//-1 => All
// 			'offset'		=> 0,//0 => The start
// 			'output'		=> SQLAdapter::ARR_ASSOC,//Associative Array
	
	public function run() {
// 		if( !$this->instance ) {
// 			throw new Exception('noAvailableInstance');
// 		}
// 		if( $this->class && method_exists($this->class, 'prepareSelectRequest') ) {
// 			call_user_func($this->class.'::prepareSelectRequest', $this);
// 		}
// 		return SQLAdapter::doSelect($this->parameters, $this->instance, $this->idField);
		$options	= $this->parameters;
		$class		= $this->class;
		$onlyOne	= $objects = 0;
		if( in_array($options['output'], array(SQLAdapter::ARR_OBJECTS, SQLAdapter::OBJECT)) ) {
			if( $options['output'] == SQLAdapter::OBJECT ) {
				$options['number']	= 1;
				$onlyOne	= 1;
			}
			$options['output']	= SQLAdapter::ARR_ASSOC;
// 			$options['what'] = '*';// Could be * or something derived for order e.g
			$objects	= 1;
		}
		$r	= SQLAdapter::doSelect($options, $this->instance, $this->idField);
		if( empty($r) && in_array($options['output'], array(SQLAdapter::ARR_ASSOC, SQLAdapter::ARR_OBJECTS, SQLAdapter::ARR_FIRST)) ) {
			return $onlyOne && $objects ? null : array();
		}
		if( !empty($r) && $objects ) {
// 			if( isset($options['number']) && $options['number'] == 1 ) {
			if( $onlyOne ) {
				$r	= $class::load($r[0]);
			} else {
				foreach( $r as &$rdata ) {
					$rdata = $class::load($rdata);
				}
			}
		}
		return $r;
	}
	
}
