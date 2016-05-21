<?php
/**
 * The main SQL Request class
 * 
 * This class handles sql request to the DMBS server.
 */
abstract class SQLRequest {
	
	/* @var SQLAdapter $sqlAdapter */
	protected $sqlAdapter;
	protected $idField;
	protected $class;
	
	/**
	 * The SQL Query Parameters
	 * 
	 * @var string[]
	 */
	protected $parameters;
	
// 	protected function __construct($sqlAdapter, $idField, $class=null) {
	protected function __construct($sqlAdapter, $idField, $class=null) {
		$this->setSQLAdapter($sqlAdapter);
		$this->setIDField($idField);
		$this->class	= $class;
	}
	
	public function getSQLAdapter() {
		return $this->sqlAdapter;
	}
	
	public function setSQLAdapter($sqlAdapter) {
		$this->sqlAdapter	= $sqlAdapter;
	}
	
	public function setIDField($idField) {
		$this->idField	= $idField;
	}
	
	protected function set($parameter, $value) {
		$this->parameters[$parameter]	= $value;
		return $this;
	}
	
	protected function get($parameter, $default=null) {
		return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : $default;
	}
	
	protected function sget($parameter, $value=null) {
		return $value === null ? $this->get($parameter) : $this->set($parameter, $value);
	}
	
	public function from($table=null) {
		return $this->sget('table', $table);
	}
	
	public function output($output=null) {
		return $this->sget('output', $output);
	}

	public function getQuery() {
		$output	= $this->get('output');
		
		try  {
			$this->set('output', SQLAdapter::SQLQUERY);
			$result = $this->run();
		} catch( Excetion $e ) {
			
		}
		
		$this->set('output', $output);
		
		if( isset($e) ) {
			throw $e;
		}
		
		return $result;
	}
	
	protected abstract function run();
	
	public function escapeIdentifier($identifier) {
		return $this->sqlAdapter->escapeIdentifier($identifier);
	}
	
	public function escapeValue($value) {
		return $this->sqlAdapter->escapeValue($value);
	}
	
	/**
	 * 
	 * @param string $sqlAdapter
	 * @param string $idField
	 * @return SQLSelectRequest
	 */
	public static function select($sqlAdapter=null, $idField='id', $class=null) {
		return new SQLSelectRequest($sqlAdapter, $idField, $class);
	}
}
