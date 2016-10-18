<?php

abstract class AbstractFile {
	
	protected $path;

	protected $mode;
	protected $handle;
	
	abstract function open();
	abstract function getNextLine();
	abstract function write($data);
	abstract function getContents();
	abstract function close();
	
	public function __construct($path=null) {
		$this->setPath($path);
	}
	
	public function __destruct() {
		$this->ensureClosed();
	}
	
	public function getAnotherHandler($path) {
		return new static($path);
	}
	
	public function ensureOpen($mode=null) {
		if( $this->isOpen() ) {
			if( $mode && $mode !== $this->mode ) {
				$this->close();
			} else {
				return;
			}
		}
		if( $mode ) {
			$this->setMode($mode);
		}
		$this->open();
	}
	
	public function ensureClosed() {
		if( !$this->isOpen() ) {
			return;
		}
		$this->close();
	}
	
	public function remove() {
		$this->ensureClosed();
		unlink($this->getPath());
	}
	
	/**
	 * 
	 * @param string|AbstractFile $newFile
	 * @return boolean
	 */
	public function moveTo($newFile) {
		$this->ensureClosed();
		if( $newFile instanceof AbstractFile ) {
			if( $newFile->exists() ) {
				$newFile->remove();
			}
			$newFile = $newFile->getPath();
		}
		if( rename($this->getPath(), $newFile) ) {
			$this->path = $newFile;
			return true;
		} else {
			return false;
		}
	}

	public function isCompressible() {
		return true;
	}

	public function isOpen() {
		return $this->handle !== null;
	}

	public function exists() {
		return file_exists($this->getPath());
	}

	public function isReadable() {
		return is_readable($this->getPath());
	}

	public function isWritable() {
		return is_writable($this->getPath());
	}

	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		if( $this->path ) {
			throw new Exception('This file already has a path');
		}
		$this->path = $path;
		return $this;
	}

	public function getMode() {
		return $this->mode;
	}

	protected function setMode($mode) {
		$this->mode = $mode;
		return $this;
	}

	public function getHandle() {
		return $this->handle;
	}
}