<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\File;

use Exception;
use Orpheus\Config\Config;
use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\File\UploadedFile;
use Orpheus\Publisher\PasswordGenerator;
use Orpheus\Publisher\PermanentObject\PermanentObject;

/**
 * The File class to save file's information in database
 *
 * This class is really useful to save file's information in database.
 * It abstracts uploads and downloads.
 *
 * @property string $create_date
 * @property string $create_ip
 * @property integer $create_user_id
 * @property string $name
 * @property string $extension
 * @property string $mimetype
 * @property string $usage
 * @property integer $parent_id
 * @property integer $position
 * @property string $passkey
 * @property string $source_type
 * @property string $source_name
 * @property string $source_url
 */
class File extends PermanentEntity {
	
	const MODE_COPY = 1;
	
	const MODE_MOVE = 2;
	
	protected static string $table = 'file';
	
	protected static array $fields = [];
	
	protected static $validator = null;
	
	protected static string $domain;
	
	protected static array $sourceTypes = [];
	
	/**
	 * @var {string:array}[]
	 */
	protected static $usages = null;
	
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
	public function getFileName(): string {
		return toSlug($this->name) . '.' . $this->extension;
	}
	
	/**
	 * Get the absolute path to the file
	 *
	 * @return string
	 */
	public function getPath() {
		return static::getFolderPath() . $this->id() . '.' . $this->extension;
	}
	
	/**
	 * Get the absolute link to see/download the file
	 *
	 * @return string
	 */
	public function getLink(): string {
		return static::genLink($this->id(), $this->passkey);
	}
	
	/**
	 * Get the translated text of the file's source
	 *
	 * @return string
	 */
	public function getSourceText(): string {
		return t('source_' . $this->source_type, static::getDomain());
	}
	
	/**
	 * Duplicate this object to a new one
	 *
	 * @param string|PermanentObject $label The label, if object & no $parent, use it as parent
	 * @param PermanentObject|integer $parent
	 * @return File
	 */
	public function duplicate($label = null, $parent = null) {
		if( !$parent && is_object($label) ) {
			$parent = $label;
		}
		$input = [];
		$input['name'] = $label . '';
		$input['extension'] = $this->extension;
		$input['mimetype'] = $this->mimetype;
		$input['usage'] = $this->usage;
		$input['position'] = $this->position;
		$input['source_type'] = $this->source_type;
		$input['source_name'] = $this->source_name;
		$input['source_url'] = $this->source_url;
		if( $parent ) {
			$input['parent_id'] = id($parent);
		}
		
		return static::import($input, $this->getPath(), self::MODE_COPY);
	}
	
	/**
	 * @see PermanentObject::remove()
	 */
	public function remove() {
		if( !parent::remove() ) {
			return false;
		}
		$path = $this->getPath();
		if( file_exists($path) ) {
			unlink($this->getPath());
		}
		
		return true;
	}
	
	/**
	 * Get the file's type
	 *
	 * @return string
	 */
	public function getType() {
		[$type] = explodeList('/', $this->mimetype, 2);
		
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
	public function download($passKey, $forceDownload = false) {
		// Allow File to have no passkey, then the file is public
		if( $this->passkey && $passKey !== $this->passkey ) {
			static::throwException('invalidPasskey');
		}
		$filePath = $this->getPath();
		if( !is_readable($filePath) ) {
			static::throwException('unreadableFile');
		}
		// Start download, close session and end buffer
		session_write_close();
		ob_clean();
		
		header('Content-Type: ' . $this->mimetype);
		if( $forceDownload ) {
			header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');
		} else {
			header('Content-Disposition: inline; filename="' . $this->getFileName() . '"');
		}
		header('Content-length: ' . filesize($filePath));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
		header('Cache-Control: private, max-age=' . $this->getCacheMaxAge());
		header('Pragma: public');
		
		readfile($filePath);
		die();
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
	 * Upload one file from
	 *
	 * @param UploadedFile|string $inputName The input name to retrieve or the UploadedFile object
	 * @param string $label The public name of the file to create
	 * @param string $usage The usage of the file to create
	 * @param int|string $parent Optional id of the parent
	 * @return    boolean|File The File object or false if there is no valid upload
	 * @throws    Exception
	 */
	public static function uploadOne($inputName, $label, $usage, $parent = 0) {
		if( !in_array($usage, static::getUsageNames()) ) {
			static::throwException('invalidUsage');
		}
		if( !$parent && is_object($label) ) {
			$parent = $label;
		}
		if( is_string($inputName) ) {
			$upFile = UploadedFile::load($inputName);
			// No valid upload
			if( empty($upFile) ) {
				return false;
			}
			$usages = listFileUsages();
			if( isset($usages[$usage]['type']) ) {
				$upFile->type = $usages[$usage]['type'];
			}
			$upFile->validate('Uploading file' . $label . ' (' . $usage . ')');
		} else {
			$upFile = &$inputName;
		}
		$file = static::createAndGet([
			'name'        => $label . '',
			'parent_id'   => id($parent),
			'usage'       => $usage,
			'extension'   => strtolower($upFile->getExtension()),
			'mimetype'    => $upFile->getMIMEType(),
			'source_name' => $upFile->getFileName(),
			'source_type' => FILE_SOURCETYPE_UPLOAD,
		]);
		try {
			checkDir(static::getFolderPath());
			if( !move_uploaded_file($upFile->getTempPath(), $file->getPath()) ) {
				throw new Exception('unableToMoveUploadedFile');
			}
		} catch( Exception $e ) {
			$file->remove();
			throw $e;
		}
		
		return $file;
	}
	
	/**
	 * Import file from path
	 *
	 * @param array $input The input data to create the file object
	 * @param string $path The path to import from.
	 * @param integer $mode Copy or Move the file. Default is to move the file.
	 * @return File
	 *
	 * $input could contain data: name, extension (deducted), mimetype (deducted), usage, parent_id (0), position (0), source_name, source_type
	 */
	public static function import(array $input, $path, $mode = self::MODE_MOVE) {
		checkDir(static::getFolderPath());
		if( !isset($input['extension']) ) {
			$input['extension'] = pathinfo($path, PATHINFO_EXTENSION);
		}
		if( !isset($input['mimetype']) ) {
			$input['mimetype'] = getMimeType($path);
		}
		$file = static::createAndGet($input);
		$result = $mode == self::MODE_MOVE ? rename($path, $file->getPath()) : copy($path, $file->getPath());
		if( !$result ) {
			static::throwException('unableToImport');
		}
		
		return $file;
	}
	
	public static function importFromURL(array $input, $url) {
		if( !is_dir(TEMPPATH) ) {
			mkdir(TEMPPATH, 0777, true);
		}
		if( !isset($input['extension']) ) {
			$input['extension'] = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
		}
		do {
			$tmpFile = TEMPPATH . generatePassword() . '.tmp';
		} while( file_exists($tmpFile) );
		try {
			copy($url, $tmpFile);
			$input['source_url'] = $url;
			
			return static::import($input, $tmpFile, self::MODE_MOVE);
		} catch( Exception $e ) {
			unlink($tmpFile);
			throw $e;
		}
	}
	
	/**
	 * Import file from data-url
	 *
	 * @param array $input The input data to create the file object
	 * @param string $dataURL The data URL to import from, data:// or data: (PHP 5.2.0+)
	 * @return File
	 *
	 * $input could contains data: name, extension (deducted), mimetype (deducted), usage, parent_id (0), position (0), source_name, source_type
	 */
	public static function importFromDataURL(array $input, $dataURL) {
		checkDir(static::getFolderPath());
		if( !isset($input['mimetype']) ) {
			$str = strstr($dataURL, ';', true);
			$str = explode(':', $str);
			$input['mimetype'] = $str[1];
			unset($str);
		}
		if( !isset($input['extension']) && isset($input['mimetype']) ) {
			$input['extension'] = static::getMimeTypeExtension($input['mimetype']);
		}
		if( !isset($input['source_type']) ) {
			$input['source_type'] = FILE_SOURCETYPE_DATAURI;
			// Empty name by default
		}
		$content = file_get_contents($dataURL);
		$file = static::createAndGet($input);
		file_put_contents($file->getPath(), $content);
		
		return $file;
	}
	
	public static function createAndGet($input = [], $fields = null, &$errCount = 0): PermanentObject {
		$input['passkey'] = (new PasswordGenerator())->generate(30);
		if( is_array($fields) ) {
			$fields[] = 'passkey';
		}
		
		return parent::createAndGet($input, $fields, $errCount);
	}
	
	public static function getMimeTypeExtension($mimetype) {
		$types = [
			'image/gif'  => 'gif',
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
		];
		
		return $types[$mimetype];
	}
	
	/**
	 * Generate link from file id
	 *
	 * @param int $id The file id
	 * @param string $passkey The file passkey
	 * @param boolean $download True to download explicitly the file
	 * @return string The generated link
	 */
	public static function genLink($id, $passkey, $download = false) {
		return u(ROUTE_FILE_DOWNLOAD, ['fileID' => $id]) . '?k=' . urlencode($passkey) . ($download ? '&download' : '');
	}
	
	/**
	 * Get the allowed source types
	 *
	 * @return string[]
	 */
	public static function getSourceTypes() {
		return static::$sourceTypes;
	}
	
	/**
	 * Add a source type to the allowed ones
	 *
	 * @param string $sourceType
	 * @return boolean
	 */
	public static function addSourceType($sourceType) {
		if( in_array($sourceType, static::$sourceTypes) ) {
			return false;
		}
		static::$sourceTypes[] = $sourceType;
		
		return true;
	}
	
	/**
	 * Get the allowed usages
	 *
	 * @return {string:array}[]
	 */
	public static function getUsages(): array {
		if( static::$usages === null ) {
			static::$usages = array_fill_keys(Config::get('file_usages', []), null);
		}
		
		return static::$usages;
	}
	
	/**
	 * Get the allowed usages' name
	 *
	 * @return    array
	 */
	public static function getUsageNames() {
		return array_keys(listFileUsages());
	}
	
	/**
	 * Set the given usage option as allowed
	 *
	 * @param string $usage
	 * @param array $options
	 * @return    boolean
	 */
	public static function setUsage($usage, $options = null): bool {
		static::$usages[$usage] = $options;
		
		return true;
	}
	
	/**
	 * Get all files with the given usage
	 *
	 * @param string $usage
	 * @return    File[]
	 */
	public static function getByUsage($usage): array {
		return static::get(static::ei('usage') . ' LIKE ' . static::formatValue($usage));
	}
	
	/**
	 * Get all files with the given parent
	 *
	 * @param string $usage
	 * @param int|PermanentObject $parent
	 * @return File[]
	 */
	public static function getByParent($usage, $parent): array {
		return static::get(static::ei('usage') . ' LIKE ' . static::formatValue($usage) . ' AND ' . static::ei('parent_id') . ' = ' . id($parent), 'id ASC');
	}
	
}

File::init();
