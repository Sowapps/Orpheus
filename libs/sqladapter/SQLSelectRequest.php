<?php
/**
 * The main SQL Select Request class
 * 
 * This class handles sql SELECT request to the DMBS server.
 */
class SQLSelectRequest extends SQLRequest {
	
	protected $usingCache	= true;

	public function setUsingCache($usingCache) {
		$this->usingCache	= $usingCache;
		return $this;
	}

	public function disableCache() {
		return $this->setUsingCache(false);
	}

	public function fields($fields=null) {
		return $this->sget('what', $fields);
	}

	public function addField($field=null) {
		return $this->sget('what', $this->get('what', '*').','.$field);
	}

	public function having($condition) {
		$having		= $this->get('having', array());
		$having[]	= $condition;
		return $this->sget('having', $having);
	}

	public function where($condition, $equality=null, $value=null) {
// 		debug('SQLSelectRequest::where('.$condition.', '.$equality.', '.$value.')');
		if( $equality ) {
			if( !$value ) {
				$value		= $equality;
				$equality	= is_array($value) ? 'IN' : '=';
			}
			$condition = $this->escapeIdentifier($condition).' '.$equality.' '.(is_array($value) ?
				'('.$this->sqlAdapter->formatValueList($value).')' :
				$this->escapeValue(is_object($value) ? id($value) : $value));
		}
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

	public function asArray() {
		return $this->output(SQLAdapter::ARR_FIRST);
	}

	public function exists() {
		return $this->count(1);
	}

	public function count($max='') {
		$countKey	= '0rpHeus_Count';
		$what	= $this->get('what');
		$output	= $this->get('output');
		$number	= $this->get('number');
		$offset	= $this->get('offset');
		
		try  {
			$this->set('what', ($what ? $what.', ' : '').'SUM(1) '.$countKey);
			$this->set('number', $max);
			$this->set('offset', '');
// 			$this->set('output', SQLAdapter::SQLQUERY);
// 			debug('Query : '.$this->run());
// 			$this->set('output', SQLAdapter::ARR_FIRST);
			$this->asArray();
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

	/**
	 * @var PDOStatement
	 */
	protected $fetchLastStatement;
	protected $fetchIsObject;
	public function fetch() {
		if( !$this->fetchLastStatement ) {
			$this->fetchIsObject = $this->get('output', SQLAdapter::ARR_OBJECTS) === SQLAdapter::ARR_OBJECTS;
			$this->set('output', SQLAdapter::STATEMENT);
			$this->fetchLastStatement	= $this->run();
		}
		$row	= $this->fetchLastStatement->fetch(PDO::FETCH_ASSOC);
		if( !$row ) {
			// Last return false, we return null, same effect
			return null;
		}
		if( !$this->fetchIsObject ) {
			return $row;
		}
		$class		= $this->class;
		return $class::load($row, true, $this->usingCache);
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
// 		$r	= SQLAdapter::doSelect($options, $this->instance, $this->idField);
		$r = $this->sqlAdapter->select($options);
		if( is_object($r) ) {
			return $r;
		}
		if( empty($r) && in_array($options['output'], array(SQLAdapter::ARR_ASSOC, SQLAdapter::ARR_OBJECTS, SQLAdapter::ARR_FIRST)) ) {
			return $onlyOne && $objects ? null : array();
		}
		$class		= $this->class;
		if( !empty($r) && $objects ) {
// 			if( isset($options['number']) && $options['number'] == 1 ) {
			if( $onlyOne ) {
				$r	= $class::load($r[0], true, $this->usingCache);
			} else {
				foreach( $r as &$rdata ) {
					$rdata = $class::load($rdata, true, $this->usingCache);
				}
			}
		}
		return $r;
	}
	
}
