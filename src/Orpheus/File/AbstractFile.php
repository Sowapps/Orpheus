<?php

namespace Orpheus\File;

use Orpheus\Exception\UserException;
use RuntimeException;

/**
 * Class abstracting the usage of disk files
 */
abstract class AbstractFile {
	
	protected ?string $path = null;
	
	protected ?string $mode = null;
	
	protected mixed $handle = null;
	
	public function __construct(?string $path = null) {
		$this->setPath($path);
	}
	
	abstract function open(): void;
	
	abstract function getNextLine(): string|false;
	
	abstract function write($data): void;
	
	abstract function getContents(): string|false;
	
	abstract function close(): bool;
	
	public function __destruct() {
		$this->ensureClosed();
	}
	
	public function create(): void {
		$this->ensureOpen('w+');
	}
	
	public function ensureOpen(?string $mode = null): void {
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
	
	public function ensureClosed(): void {
		if( !$this->isOpen() ) {
			return;
		}
		$this->close();
	}
	
	public function remove(): void {
		$this->ensureClosed();
		unlink($this->getPath());
	}
	
	public static function fromSameType(string $path): static {
		return new static($path);
	}
	
	public function copyTo(AbstractFile|string $otherFile): ?static {
		$this->ensureClosed();
		if( is_string($otherFile) ) {
			$otherFile = static::fromSameType($otherFile);
		}
		if( $otherFile->exists() ) {
			$otherFile->remove();
		}
		$path = $otherFile->getPath();
		if( copy($this->getPath(), $path) ) {
			
			return $otherFile;
		}
		
		return null;
	}
	
	public function moveTo(string|AbstractFile $otherFile): bool {
		$this->ensureClosed();
		if( $otherFile instanceof AbstractFile ) {
			if( $otherFile->exists() ) {
				$otherFile->remove();
			}
			$path = $otherFile->getPath();
		} else {
			$path = $otherFile;
		}
		if( rename($this->getPath(), $path) ) {
			$this->path = $path;
			
			return true;
		}
		
		return false;
	}
	
	public function isCompressible(): bool {
		return true;
	}
	
	public function isFormatSupported(): bool {
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
			throw new RuntimeException('This file already has a path');
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
	
	public static function guessFileFormat(string $path): string {
		$filePathInfo = (object)pathinfo($path);
		return strtoupper($filePathInfo->extension);
	}
	
	public static function resolveFile(string $path, ?string $format = null): AbstractFile {
		$format ??= self::guessFileFormat($path);
		
		return match ($format) {
			'GZ' => new GZFile($path),
			'LOG' => new TextFile($path),
			default => throw new UserException('unknownFileFormat', DOMAIN_LOGS),
		};
	}
	
}
