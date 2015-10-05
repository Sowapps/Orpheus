<?php

/**
 * The file class to save file's informations in database
 *
 * This class is really useful to save file's information in database.
 * It abstracts uploads and downloads.
 */
class File extends PermanentEntity {
	
	//Attributes
	protected static $table		= 'file';
	
	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	const MODE_COPY	= 1;
	const MODE_MOVE	= 2;

// 	const USAGE_RESTAURANT		= 'RT';
// 	const USAGE_PERSONAVATAR	= 'PA';
// 	const USAGE_GALERYAVATAR	= 'GA';
	/**
	 * @var {string:array}[]
	 */
	protected static $usages	= null;
// 		self::USAGE_RESTAURANT					=> array('type' => 'image'),
// 		self::USAGE_PERSONAVATAR	=> array('type' => 'image'),
// 		self::USAGE_GALERYAVATAR	=> array('type' => 'image', 'standalone' => 1),
// 	);

	const SOURCETYPE_UPLOAD				= 'UP';
// 	const SOURCETYPE_UPLOADCONVERTED	= 'UPC';
// 	const SOURCETYPE_PHPQRCODE			= 'PQRC';
// 	const SOURCETYPE_PDFGENERATOR		= 'UPC';
	/**
	 * @var string[]
	 */
	protected static $sourceTypes		= array(self::SOURCETYPE_UPLOAD);
	
	/*
	create_date:            datetime=sqlDatetime()
	
	# Identification
	name:                   string(200)                 # Nom visuel du fichier
	extension:              string(5)                   # Extension locale
	mimetype:               string(100)                 # Type MIME
	usage:                  enum(File::getUsages)       # Usage
	
	# Source
	source_name:            string(200)                 # Nom du fichier utilisé par la source
	source_type:            enum(File::getSourceTypes)  # Type de source
	*/
	/**
	 * @see PermanentObject::__toString()
	 */
	public function __toString() {
		return defined("TERMINAL") ? $this->name : escapeText($this->name);
	}
	
	/**
	 * Get the public file name
	 * 
	 * @return string
	 */
	public function getFileName() {
		return toSlug($this->name).'.'.$this->extension;
	}
	
	/**
	 * Get the path to the files' folder
	 * 
	 * @return string
	 */
	public static function getFolderPath() {
		return FILESTOREPATH;
	}
	
	/**
	 * Get the absolute path to the file 
	 * 
	 * @return string
	 */
	public function getPath() {
		return static::getFolderPath().$this->id().'.'.$this->extension;
	}
	
	/**
	 * Get the absolute link to see/download the file 
	 * 
	 * @return string
	 */
	public function getLink() {
		return static::genLink($this->id());
	}
	
	/**
	 * Get the translated text of the file's source
	 * 
	 * @return string
	 */
	public function getSourceText() {
		return t('source_'.$this->source_type, static::getDomain());
	}
	
	// Obsolete, no replacement, do it yourself
// 	public function getHTMLPreview() {
// 		switch( $this->getType() ) {
// 			case 'image':
// 				return '<img class="picture" src="'.$this->getLink().'"/>';
// 			default:
// 				return '';
// 		}
// 	}
	
	/**
	 * @see PermanentObject::remove()
	 */
	public function remove() {
		if( !parent::remove() ) { return false; }
		$path	= $this->getPath();
		if( file_exists($path) ) { unlink($this->getPath()); }
		return true;
	}

	/**
	 * Get the file's type
	 * 
	 * @return string
	 */
	public function getType() {
		list($type)	= explodeList('/', $this->mimetype, 2);
// 		list($type, $other)	= explodeList('/', $this->mimetype, 2);
		return $type;
	}

	/**
	 * Get the cache max-age in seconds
	 * 
	 * @return integer
	 */
	public function getCacheMaxAge() {
		return 86400;
	}

	/**
	 * Download the file
	 * 
	 * @return string
	 * Send the file to the client
	 */
	public function download($forceDownload=false) {
		$filePath	= $this->getPath();
		if( !is_readable($filePath) ) {
			static::throwException('unreadableFile');
		}
		// Start download, close session and end buffer
		session_write_close();
		ob_clean();
		// text('$file->getFileName : '.$file->getFileName());
		// text('$file->mimetype : '.$file->mimetype);
		// text('mod time : '.filemtime($filePath));
		// die('Script interrupted');
		
		// header('Content-Type: application/x-download');
		
		header('Content-Type: '.$this->mimetype);
		if( $forceDownload ) {
			header('Content-Disposition: attachment; filename="'.$this->getFileName().'"');
		} else {
			header('Content-Disposition: inline; filename="'.$this->getFileName().'"');
		}
		header('Content-length: '.filesize($filePath));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filePath)).' GMT');
		// header('Cache-Control: public, max-age=3600, must-revalidate');
		header('Cache-Control: private, max-age='.$this->getCacheMaxAge());
		header('Pragma: public');
		
		readfile($filePath);
		die();
	}
	
	/**
	 * Upload one file from
	 * 
	 * @param	UploadedFile|string $inputName The input name to retrieve or the UploadedFile object
	 * @param	string $label The public name of the file to create
	 * @param	string $usage The usage of the file to create
	 * @param	id $parent Optionnal id of the parent
	 * @throws	Exception
	 * @return	boolean|File The File object or false if there is no valid upload
	 */
	public static function uploadOne($inputName, $label, $usage, $parent=0) {
		if( !in_array($usage, static::getUsageNames()) ) {
			static::throwException('invalidUsage');
		}
		if( is_string($inputName) ) {
			$upFile = UploadedFile::load($inputName);
// 			debug('$upFile', $upFile);
			// No valid upload
			if( empty($upFile) ) { return false; }
			if( isset(static::$usages[$usage]['type']) ) {
				$upFile->type	= static::$usages[$usage]['type'];
			}
			$upFile->validate('Uploading file'.$label.' ('.$usage.')');
		} else {
			$upFile	= &$inputName;
		}
		$file	= static::createAndGet(array(
			'name'			=> $label, 
			'parent_id'		=> id($parent), 
			'usage'			=> $usage,
			'extension'		=> strtolower($upFile->getExtension()),
			'mimetype'		=> $upFile->getMIMEType(),
			'source_name'	=> $upFile->getFileName(),
			'source_type'	=> self::SOURCETYPE_UPLOAD,
		));
		try {
			checkDir(static::getFolderPath());
			if(	!move_uploaded_file($upFile->getTempPath(), $file->getPath()) ) {
				throw new Exception('unableToMoveUploadedFile');
			}
		} catch( Exception $e ) {
			$file->remove();
			throw $e;
		}
// 		debug('Return file', $file);
		return $file;
	}

	/** Import file from path
	 * 
	 * @param array $input The input data to create the file object
	 * @param string $path The path to import from.
	 * @param integer $mode Copy or Move the file. Default is to move the file.
	 * @return File
	 * 
	 * $input could contains data: name, extension (deducted), mimetype (deducted), usage, parent_id (0), position (0), source_name, source_type
	 */
	public static function import(array $input, $path, $mode=self::MODE_MOVE) {
		checkDir(static::getFolderPath());
		if( !isset($input['extension']) ) {
			$input['extension']	= pathinfo($path, PATHINFO_EXTENSION);
		}
		if( !isset($input['mimetype']) ) {
			$input['mimetype']	= getMimeType($path);
		}
		$file	= static::createAndGet($input);
// 		$file	= static::load(static::create($input));
		if( !($mode==self::MODE_MOVE ? rename($path, $file->getPath()) : copy($path, $file->getPath())) ) {
			static::throwException('unableToImport');
		}
		return $file;
	}
	
	/**
	 * Generate link from file id
	 * @param id $id The file id
	 * @param string $download True to download explicitly the file
	 * @return string The generated link
	 */
	public static function genLink($id, $download=false) {
		return u('file_download', array('fileID'=>$id));
// 		return u('download_file', $id).($download ? '?download' : '');
	}
	
	/**
	 * Get the allowed source types
	 * 
	 * @return string[]
	 */
	public static function getSourceTypes() {
		return static::$sourceTypes;
// 		return array(self::SOURCETYPE_UPLOAD, self::SOURCETYPE_UPLOADCONVERTED, self::SOURCETYPE_PHPQRCODE);
// 		return array(self::SOURCETYPE_UPLOAD, self::SOURCETYPE_UPLOADCONVERTED, self::SOURCETYPE_PDFGENERATOR);
	}
	
	/**
	 * Add a source type to the allowed ones
	 * 
	 * @param string $sourceType
	 * @return boolean
	 */
	public static function addSourceType($sourceType) {
		if( in_array($sourceType, static::$sourceTypes) ) { return false; }
		static::$sourceTypes[]	= $sourceType;
		return true;
	}

	/**
	 * Get the allowed usages
	 * 
	 * @return {string:array}[]
	 */
	public static function getUsages() {
		if( static::$usages === null ) {
			static::$usages	= array_fill_keys(Config::get('file_usages', array()), null);
// 			debug('File usages', static::$usages);
		}
		return static::$usages;
// 		return array_keys(static::$usages);
// 		return array(USAGE_ENTERPRISELOGO);
	}
	
	/**
	 * Get the allowed usages' name
	 * @return	array
	 */
	public static function getUsageNames() {
		return array_keys(static::getUsages());
	}
	
	/**
	 * Set the given usage option as allowed
	 * 
	 * @param	string	$usage
	 * @param	array	$options
	 * @return	boolean
	 */
	public static function setUsage($usage, $options=null) {
		static::$usages[$usage]	= $options;
		return true;
	}

	/**
	 * Get all files with the given usage
	 * 
	 * @param	string $usage
	 * @return	File[]
	 */
	public static function getByUsage($usage) {
		return static::get(static::ei('usage').' LIKE '.static::formatValue($usage));
	}

	/**
	 * Get all files with the given parent
	 * 
	 * @param string $usage
	 * @param id|PermanentObject $parent
	 * @return File[]
	 */
	public static function getByParent($usage, $parent) {
		return static::get(static::ei('usage').' LIKE '.static::formatValue($usage).' AND '.static::ei('parent_id').' = '.id($parent), 'id ASC');
	}
}
File::init();
