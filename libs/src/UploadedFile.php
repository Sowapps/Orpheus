<?php

class UploadedFile {
	
	protected $fileName;
	protected $fileSize;
	protected $tempPath;
	protected $error;
	
	public $allowedExtensions;
	public $allowedMimeTypes;
	public $type;
	
	public function __construct($fileName, $fileSize, $tempPath, $error) {
		$this->fileName	= $fileName;
		$this->fileSize	= $fileSize;
		$this->tempPath	= $tempPath;
		$this->error	= $error;
	}
	
	public function __toString() {
		return $this->getFileName();
	}
	
	public function getFileName() {
		return $this->fileName;
	}
	
	public function getBaseName() {
		return basename($this->fileName);
	}
	
	public function getFileSize() {
		return $this->fileSize;
	}
	
	public function getTempPath() {
		return $this->tempPath;
	}
	
	public function getMIMEType() {
		return getMimeType($this->tempPath);
	}
	
	public function getType() {
// 		list($type, $other)	= explodeList('/', $this->getMIMEType(), 2);
		list($type,) = explodeList('/', $this->getMIMEType(), 2);
		return $type;
	}
	
	public function getExtension() {
		return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function moveTo($path) {
		return move_uploaded_file($this->getTempPath(), $path);
	}
	
	public function validate($action='Uploading file') {
		if( $this->error ) {
			switch( $this->error ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE: {
					throw new UserException('fileTooBig');
					break;
				}
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE: {
					throw new UserException('transfertIssue');
					break;
				}
				default: {
					// UPLOAD_ERR_NO_TMP_DIR UPLOAD_ERR_CANT_WRITE UPLOAD_ERR_EXTENSION
					// http://php.net/manual/fr/features.file-upload.errors.php
					log_error("Server upload error (error={$this->error}, name={$this->fileName})", $action, false);
					throw new UserException('serverIssue');
				}
			}
		}
		
		if( $this->type !== NULL ) {
			$ext	= $this->getExtension();
			if( $ext === $this->allowedExtensions || (is_array($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) ) {
				throw new UserException('invalidExtension');
			}
		}
		if( $this->allowedExtensions !== NULL ) {
			$ext	= $this->getExtension();
			if( $ext === $this->allowedExtensions || (is_array($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) ) {
				throw new UserException('invalidExtension');
			}
		}
		if( $this->allowedMimeTypes !== NULL) {
			$mt		= $this->getMIMEType();
			if( $mt === $this->allowedMimeTypes || (is_array($this->allowedMimeTypes) && !in_array($mt, $this->allowedMimeTypes)) ) {
				throw new UserException('invalidMimeType');	
			}
		}
	}
	
	protected static function loadPath($from, &$files=array(), $path='') {
		$fileName	= $path==='' ? $from['name'] : apath_get($from['name'], $path);
// 		debug('LoadPath('.$path.') - $fileName', $fileName);
		if( empty($fileName) ) { return $files; }
		if( is_array($fileName) ) {
			if( $path!=='' ) { $path .= '/'; }
			foreach( $fileName as $index => $fn ) {
				static::loadPath($from, $files, $path.$index);
			}
// 			debug('loadPath() Files if name is an array', $files);
			return $files;
		}
		apath_setp($files, $path, new static($fileName, apath_get($from, 'size/'.$path),
			apath_get($from, 'tmp_name/'.$path), apath_get($from, 'error/'.$path)));
// 		debug('loadPath() Files if value is set', $files);
		return $files;
// 		$files[]	= ;
	}

	/**
	 * @param string $name
	 * @return UploadedFile
	 */
	public static function load($name) {
		if( empty($_FILES[$name]['name']) ) { /*text('load() Name not found'); */return null; }
		if( is_array($_FILES[$name]['name']) ) {
// 			text('load() Name is an array');
// 			$r	= array();
			return static::loadPath($_FILES[$name]);
// 			foreach( $_FILES[$name]['name'] as $ind => $fileName ) {
// 				if( empty($fileName) ) { continue; }
// 				static::loadPath($r, $_FILES[$name]);
// // 				$r[] = new static($fileName, $_FILES[$name]['size'][$ind], $_FILES[$name]['tmp_name'][$ind], $_FILES[$name]['error'][$ind]);
// 			}
// 			return $r;
		}
// 		text('load() Name is a single object');
		return new static($_FILES[$name]['name'], $_FILES[$name]['size'], $_FILES[$name]['tmp_name'], $_FILES[$name]['error']);
// 		return !empty($_FILES[$name]['name']) ? new static($_FILES[$name]['name'], $_FILES[$name]['size'], $_FILES[$name]['tmp_name'], $_FILES[$name]['error']) : null;
	}
	
}