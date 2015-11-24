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

	public function addField($field=null) {
		return $this->sget('what', $this->get('what', '*').','.$field);
	}

	public function having($condition=null) {
		$having		= $this->get('having', array());
		$having[]	= $condition;
		return $this->sget('having', $having);
	}

	public function where($condition=null) {
		$where		= $this->get('where', array());
		$where[]	= $condition;
		return $this->sget('where', $where);
	}

	public function orderby($fields=null) {
		return $this->sget('orderby', $fields);
	}

	public function groupby($field) {
		return $this->sget('groupby', $field);
	}

	public function number($number) {
		return $this->sget('number', $number);
	}

	public function fromOffset($offset) {
		return $this->sget('offset', $offset);
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

	public function asArrayList() {
		return $this->output(SQLAdapter::ARR_ASSOC);
	}

	public function count() {
		$countKey	= '0rpHeus_Count';
		$what	= $this->get('what');
		$output	= $this->get('output');
		$number	= $this->get('number');
		$offset	= $this->get('offset');
		
		try  {
			$this->set('what', ($what ? $what.', ' : '').'SUM(1) '.$countKey);
			$this->set('number', '');
			$this->set('offset', '');
// 			$this->set('output', SQLAdapter::SQLQUERY);
// 			debug('Query : '.$this->run());
			$this->set('output', SQLAdapter::ARR_FIRST);
			$result = $this->run();
		} catch( Excetion $e ) {
			
		}
		
		$this->set('what', $what);
		$this->set('output', $output);
		$this->set('number', $number);
		$this->set('offset', $offset);
		
		if( isset($e) ) {
			throw $e;
		}
		
		return isset($result[$countKey]) ? $result[$countKey] : 0;
	}
	
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
