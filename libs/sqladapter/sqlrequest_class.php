<?php
/**
 * The main SQL Request class
 * 
 * This class handles sql request to the DMBS server.
 */
abstract class SQLRequest {
	
	protected $instance;
	protected $idField;
	protected $class;
	
	/**
	 * The SQL Query Parameters
	 * 
	 * @var string[]
	 */
	protected $parameters;
	
	protected function __construct($instance, $idField, $class=null) {
		$this->setInstance($instance);
		$this->setIDField($idField);
		$this->class	= $class;
	}
	
	public function setInstance($instance) {
		$this->instance	= $instance;
	}
	
	public function setIDField($idField) {
		$this->idField	= $idField;
	}
	
	protected function set($parameter, $value) {
		$this->parameters[$parameter]	= $value;
		return $this;
	}
	
	protected function get($parameter, $default=null) {
		return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : null;
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
	
	protected abstract function run();
	
	/**
	 * 
	 * @param string $instance
	 * @param string $idField
	 * @return SQLSelectRequest
	 */
	public static function select($instance=null, $idField='id', $class=null) {
		return new SQLSelectRequest($instance, $idField, $class);
	}
}
