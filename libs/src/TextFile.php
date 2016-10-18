<?php

class TextFile extends AbstractFile {

	public function isCompressible() {
		return true;
	}

	function open() {
		$this->handle = fopen($this->getPath(), $this->getMode());
	}
	
	function getNextLine() {
		$this->ensureOpen('r');
		return fgets($this->handle);
	}
	
	function write($data) {
		$this->ensureOpen('w');
		fwrite($this->handle, $data);
	}
	
	function getContents() {
		$this->ensureClosed();
		return file_get_contents($this->getPath());
	}
	
	function close() {
		$r = fclose($this->handle);
		$this->handle = null;
		return $r;
	}
}