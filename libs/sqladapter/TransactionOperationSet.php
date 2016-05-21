<?php
/** The Transaction Object Set class

	This class is about a transaction with multiple operation for an adapter
 */
class TransactionOperationSet implements IteratorAggregate {

	/**
	 * @var TransactionOperation[] $operations
	 */
	protected $operations	= array();
	/**
	 * @var SQLAdapter $sqlAdapter
	 */
	protected $sqlAdapter;
	
	public function __construct(SQLAdapter $sqlAdapter) {
		$this->sqlAdapter	= $sqlAdapter;
	}
	
	public function add(PermanentObject $operation) {
		$this->operations[] = $operation;
	}
	
	public function getSQLAdapter() {
		return $this->sqlAdapter;
	}
	
	public function save() {
		if( !$this->operations ) {
			return;
		}
		// Validate all operations before saving it
		$this->validateOperations();
		// Then operations are valids, so we save it
		$this->runOperations();
	}
	
	protected function validateOperations() {
		$errors	= 0;
		foreach( $this->operations as $operation ) {
			$operation->setTransactionOperationSet($this);
			$operation->validate($errors);
		}
	}
	
	protected function runOperations() {
		foreach( $this->operations as $operation ) {
			$operation->setTransactionOperationSet($this);
			$operation->runIfValid();
		}
	}
	
	public function getIterator() {
		return new ArrayIterator($this->operations);
	}
	
}

abstract class TransactionOperation {
	
	protected $class;
	/**
	 * @var TransactionOperationSet $transactionOperationSet
	 */
	protected $transactionOperationSet;

	/**
	 * @var SQLAdapter $sqlAdapter
	 */
	protected $sqlAdapter;
	
	protected $isValid;
	
	public function __construct($class) {
		$this->class	= $class;
	}
	
	public function isValid() {
		return $this->isValid;
	}
	
	protected function setIsValid($valid) {
		$this->isValid	= $valid;
	}
	
	protected function setValid() {
		$this->setIsValid(true);
	}
	
	protected function setInvalid() {
		$this->setIsValid(false);
	}
	
	public abstract function validate(&$errors);
	
	public abstract function run();
	
	public function runIfValid() {
		return $this->isValid ? $this->run() : 0;
// 		return $this->isValid ? $this->run() : null;
	}
	
	public function getSQLAdapter() {
		return $this->sqlAdapter ? $this->sqlAdapter :
			($this->transactionOperationSet ? $this->transactionOperationSet->getSQLAdapter() : null);
	}
	
	public function setSQLAdapter(SQLAdapter $sqlAdapter) {
		$this->sqlAdapter = $sqlAdapter;
		return $this;
	}
	
	public function getTransactionOperationSet() {
		return $this->transactionOperationSet;
	}
	
	public function setTransactionOperationSet(TransactionOperationSet $transactionOperationSet) {
		$this->transactionOperationSet = $transactionOperationSet;
		return $this;
	}
	
}

class CreateTransactionOperation extends TransactionOperation {

	protected $data;
	protected $fields;
	
	protected $insertID;
	
	public function __construct($class, array $data, $fields) {
		parent::__construct($class);
		$this->data		= $data;
		$this->fields	= $fields;
	}
	
	public function validate(&$errors) {
		$class = $this->class;
// 		$class::checkUserInput($input, $fields, $this, $errCount);
		$newErrors = 0;
		$this->data = $class::checkUserInput($this->data, $this->fields, null, $newErrors);
		
		$class::onValidCreate($this->data, $newErrors);
		
		$errors	+= $newErrors;
		
		$this->setValid();
	}
	
	public function run() {
		// TODO Developer and use an SQLCreateRequest class
		$class = $this->class;
		$queryOptions = $class::extractCreateQuery($this->data);

// 		$sqlAdapter	= $this->getTransactionOperationSet()->getSQLAdapter();
		$sqlAdapter	= $this->getSQLAdapter();
		
		$r = $sqlAdapter->insert($queryOptions);
		
		if( $r ) {
			$this->insertID = $sqlAdapter->lastID($queryOptions['table']);
			
			$class::onSaved($this->data, $this->insertID);
			
			return $this->insertID;
		}
		return 0;
		
// 		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
// 		$LastInsert	= SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
		// To do after insertion
// 		static::applyToObject($data, $LastInsert);
// 		static::onSaved($data, $LastInsert);
	}
	
	public function getInsertID() {
		return $this->insertID;
	}
}

class UpdateTransactionOperation extends TransactionOperation {

	protected $data;
	protected $fields;
	protected $object;

	public function __construct($class, array $data, $fields, PermanentObject $object) {
		parent::__construct($class);
		$this->data		= $data;
		$this->fields	= $fields;
		$this->object	= $object;
		
// 		debug('UpdateTransactionOperation - $this->data', $this->data);
	}
	
	public function validate(&$errors=0) {
		$class = $this->class;
		$newErrors = 0;
		
// 		debug('validate() - $this->data before check', $this->data);
		$this->data = $class::checkUserInput($this->data, $this->fields, $this->object, $newErrors);
// 		debug('validate() - $this->data after check ['.$newErrors.']', $this->data);
	
		$this->setIsValid($class::onValidUpdate($this->data, $newErrors));
		
		$errors	+= $newErrors;
	}
	
	public function run() {
		// TODO Developer and use an SQLUpdateRequest class
		$class = $this->class;
		$queryOptions = $class::extractUpdateQuery($this->data, $this->object);

		$sqlAdapter	= $this->getSQLAdapter();
	
		$r = $sqlAdapter->update($queryOptions);
		if( $r ) {
			// Success
			$this->object->reload();
			$class::onSaved($this->data, $this);
			return 1;
// 			static::runForDeletion($in);
		}
		return 0;
	
// 		static::onSaved(array_filterbykeys($this->all, $modFields), $this);
// 		$class::onSaved($this->data, $this->insertID);
	}
}

class DeleteTransactionOperation extends TransactionOperation {

	protected $object;

	public function __construct($class, PermanentObject $object) {
		parent::__construct($class);
		$this->object	= $object;
	}
	
	public function validate(&$errors) {
		
		$this->setIsValid(!$this->object->isDeleted());
		
	}
	
	public function run() {
		// Testing generating query in this class
		$class = $this->class;
		
		$options	= array(
			'table'		=> $class::getTable(),
			'where'		=> $class::getIDField().'='.$this->object->id(),
			'number'	=> 1,
		);
		
		$sqlAdapter	= $this->getSQLAdapter();
		
		$r = $sqlAdapter->delete($options);
// 		$r = SQLAdapter::doDelete($options, static::$DBInstance, static::$IDFIELD);
		if( $r ) {
			// Success
			$this->object->markAsDeleted();
			return 1;
// 			static::runForDeletion($in);
		}
		return 0;
		
	}
}
