<?php

use Orpheus\Exception\UserException;

class UploadedFile {
	
	protected $fileName;
	protected $fileSize;
	protected $tempPath;
	protected $error;
	
	public $allowedExtensions;
	public $allowedMimeTypes;
	public $type;
	
	public function __construct($fileName, $fileSize, $tempPath, $error) {
		$this->fileName = $fileName;
		$this->fileSize = $fileSize;
		$this->tempPath = $tempPath;
		$this->error = $error;
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
		[$type,] = explodeList('/', $this->getMIMEType(), 2);
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
	
	public function validate($action = 'Uploading file') {
		if( $this->error ) {
			switch( $this->error ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
				{
					throw new UserException('fileTooBig');
					break;
				}
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
				{
					throw new UserException('transfertIssue');
					break;
				}
				default:
				{
					// UPLOAD_ERR_NO_TMP_DIR UPLOAD_ERR_CANT_WRITE UPLOAD_ERR_EXTENSION
					// http://php.net/manual/fr/features.file-upload.errors.php
					log_error("Server upload error (error={$this->error}, name={$this->fileName})", $action, false);
					throw new UserException('serverIssue');
				}
			}
		}
		
		if( $this->type !== null && $this->getType() !== $this->type ) {
			throw new UserException(t('invalidType', 'global', $this->getType()));
		}
		if( $this->allowedExtensions !== null ) {
			$ext = $this->getExtension();
			if( $ext !== $this->allowedExtensions && (!is_array($this->allowedExtensions) || !in_array($ext, $this->allowedExtensions)) ) {
				throw new UserException(t('invalidExtension', 'global', $this->getExtension()));
			}
		}
		$mimetype = $this->getMIMEType();
		if( $mimetype === 'application/octet-stream' ) {
			// This mimetype is too much generic, it goes with lot of issues
			throw new UserException('invalidMimeType', 'global');
		}
		if( $this->allowedMimeTypes !== null ) {
			if( $mimetype !== $this->allowedMimeTypes && (!is_array($this->allowedMimeTypes) || !in_array($mimetype, $this->allowedMimeTypes)) ) {
				throw new UserException('invalidMimeType', 'global');
			}
		}
	}
	
	protected static function loadPath($from, &$files = [], $path = '') {
		$fileName = empty($path) ? $from['name'] : apath_get($from['name'], $path);
		if( empty($fileName) ) {
			return $files;
		}
		if( is_array($fileName) ) {
			if( !empty($path) ) {
				$path .= '/';
			}
			foreach( $fileName as $index => $fn ) {
				static::loadPath($from, $files, $path . $index);
			}
			return $files;
		}
		apath_setp($files, $path, new static($fileName, apath_get($from, 'size/' . $path),
			apath_get($from, 'tmp_name/' . $path), apath_get($from, 'error/' . $path)));
		return $files;
	}
	
	/**
	 * @param string $name
	 * @return UploadedFile
	 */
	public static function load($name) {
		if( empty($_FILES[$name]['name']) ) {
			return null;
		}
		if( is_array($_FILES[$name]['name']) ) {
			return static::loadPath($_FILES[$name]);
		}
		return new static($_FILES[$name]['name'], $_FILES[$name]['size'], $_FILES[$name]['tmp_name'], $_FILES[$name]['error']);
	}
}
