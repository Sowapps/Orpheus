<?php

namespace Orpheus\File;

use RuntimeException;

/**
 * Class to manipulate Text files
 */
class TextFile extends AbstractFile {
	
	public function isCompressible(): bool {
		return true;
	}
	
	function open(): void {
		$handle = fopen($this->getPath(), $this->getMode());
		if( !$handle ) {
			throw new RuntimeException(sprintf('Unable to open file "%s" using GZ', $this->getPath()));
		}
		$this->handle = $handle;
	}
	
	function getNextLine(): false|string {
		$this->ensureOpen('r');
		return fgets($this->handle);
	}
	
	function write($data): void {
		$this->ensureOpen('w');
		fwrite($this->handle, $data);
	}
	
	function getContents(): false|string {
		$this->ensureClosed();
		return file_get_contents($this->getPath());
	}
	
	function close(): bool {
		$r = fclose($this->handle);
		$this->handle = null;
		return $r;
	}
}
