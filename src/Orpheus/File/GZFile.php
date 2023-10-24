<?php

namespace Orpheus\File;

use RuntimeException;

/**
 * Class to manipulate GZ files
 */
class GZFile extends AbstractFile {
	
	public function isCompressible(): bool {
		return false;
	}
	
	public function isFormatSupported(): bool {
		return function_exists('gzopen');
	}
	
	function open(): void {
		$handle = gzopen($this->getPath(), $this->getMode());
		if( !$handle ) {
			throw new RuntimeException(sprintf('Unable to open file "%s" using GZ', $this->getPath()));
		}
		$this->handle = $handle;
	}
	
	function getNextLine(): string|false {
		$this->ensureOpen('r');
		return gzgets($this->handle);
	}
	
	function write($data): void {
		$this->ensureOpen('w9');
		gzwrite($this->handle, $data);
	}
	
	function getContents(): string|false {
		$this->ensureClosed();
		return file_get_contents('compress.zlib://'.$this->getPath());
	}
	
	function close(): bool {
		$r = gzclose($this->handle);
		$this->handle = null;
		return $r;
	}
	
}
