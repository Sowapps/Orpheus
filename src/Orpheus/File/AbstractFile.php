<?php

abstract class AbstractFile {
	
	protected ?string $path;
	
	protected ?string $mode;
	
	protected $handle;
	
	public function __construct($path = null) {
		$this->setPath($path);
	}
	
	abstract function open();
	
	abstract function getNextLine();
	
	abstract function write($data);
	
	abstract function getContents();
	
	abstract function close();
	
	public function __destruct() {
		$this->ensureClosed();
	}
	
	public function ensureOpen(?string $mode = null) {
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
	
	public function isCompressible(): bool {
		return true;
	}
	
	public function isOpen(): bool {
		return $this->handle !== null;
	}
	
	public function exists(): bool {
		return file_exists($this->getPath());
	}
	
	public function isReadable(): bool {
		return is_readable($this->getPath());
	}
	
	public function isWritable(): bool {
		return is_writable($this->getPath());
	}
	
	public function getPath(): ?string {
		return $this->path;
	}
	
	public function setPath(string $path): AbstractFile {
		if( $this->path ) {
			throw new Exception('This file already has a path');
		}
		$this->path = $path;
		
		return $this;
	}
	
	public function getMode(): ?string {
		return $this->mode;
	}
	
	protected function setMode(?string $mode): AbstractFile {
		$this->mode = $mode;
		
		return $this;
	}
	
	public function getHandle() {
		return $this->handle;
	}
	
}
