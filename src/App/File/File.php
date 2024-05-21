<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\File;

use Exception;
use Orpheus\Config\Config;
use Orpheus\EntityDescriptor\Entity\PermanentEntity;
use Orpheus\Exception\UserException;
use Orpheus\File\UploadedFile;
use Orpheus\Publisher\PasswordGenerator;
use Orpheus\Publisher\SlugGenerator;
use Orpheus\Publisher\Validation\Validation;

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
	
	protected static array $sourceTypes = [];
	
	protected static ?array $usages = null;
	
	public function getLabel(): string {
		return $this->name;
	}
	
	/**
	 * Get the public file name
	 */
	public function getFileName(): string {
		$slugGenerator = new SlugGenerator();
		
		return $slugGenerator->format($this->name) . '.' . $this->extension;
	}
	
	/**
	 * Get the absolute path to the file
	 */
	public function getPath(): string {
		return static::getFolderPath() . '/' . $this->id() . '.' . $this->extension;
	}
	
	/**
	 * Get the absolute link to see/download the file
	 */
	public function getLink(): string {
		return static::genLink($this->id(), $this->passkey);
	}
	
	/**
	 * Get the translated text of the file's source
	 */
	public function getSourceText(): string {
		return t('source_' . $this->source_type, static::getDomain());
	}
	
	/**
	 * Duplicate this object to a new one
	 *
	 * @param string|null $label The label, if object & no $parent, use it as parent
	 */
	public function duplicate(?string $label = null, PermanentEntity|string|null $parent = null): static {
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
	
	public function remove(): bool {
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
	 */
	public function getType(): string {
		[$type] = explodeList('/', $this->mimetype, 2);
		
		return $type;
	}
	
	/**
	 * Get the cache max-age in seconds
	 */
	public function getCacheMaxAge(): int {
		return 86400;
	}
	
	/**
	 * Download the file
	 * @deprecated Use a DownloadController
	 */
	public function download($passKey, $forceDownload = false): never {
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
	 */
	public static function getFolderPath(): string {
		return FILE_STORE_PATH;
	}
	
	/**
	 * Upload one file from
	 *
	 * @param UploadedFile|string $inputName The input name to retrieve or the UploadedFile object
	 * @param string $label The public name of the file to create
	 * @param string $usage The usage of the file to create
	 * @param PermanentEntity|string|null $parent Optional id of the parent
	 * @return File The File object or false if there is no valid upload
	 * @throws Exception
	 */
	public static function uploadOne(UploadedFile|string $inputName, string $label, string $usage, PermanentEntity|string|null $parent = null): File {
		if( !in_array($usage, static::getUsageNames()) ) {
			static::throwException('invalidUsage');
		}
		if( is_string($inputName) ) {
			$uploadedFile = UploadedFile::loadFiles($inputName);
			if( empty($uploadedFile) ) {
				throw new UserException('noFile');
			}
			if( is_array($uploadedFile) ) {
				throw new UserException('noFile');
			}
			/** @var UploadedFile $uploadedFile */
			$usages = listFileUsages();
			if( isset($usages[$usage]['type']) ) {
				$uploadedFile->expectedType = $usages[$usage]['type'];
			}
			$uploadedFile->validate();
		} else {
			$uploadedFile = &$inputName;
		}
		$file = static::createAndGet([
			'name'        => $label,
			'parent_id'   => $parent ? id($parent) : null,
			'usage'       => $usage,
			'extension'   => strtolower($uploadedFile->getExtension()),
			'mimetype'    => $uploadedFile->getMimeType(),
			'source_name' => $uploadedFile->getFileName(),
			'source_type' => FILE_SOURCE_TYPE_UPLOAD,
		]);
		try {
			checkDir(static::getFolderPath());
			if( !move_uploaded_file($uploadedFile->getTempPath(), $file->getPath()) ) {
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
	 * @param int $mode Copy or Move the file. Default is to move the file.
	 * @return File
	 *
	 * $input could contain data: name, extension (deducted), mimetype (deducted), usage, parent_id (0), position (0), source_name, source_type
	 */
	public static function import(array $input, string $path, int $mode = self::MODE_MOVE): File {
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
	
	/**
	 * @throws Exception
	 */
	public static function importFromURL(array $input, string $url): File {
		if( !is_dir(TEMP_PATH) ) {
			mkdir(TEMP_PATH, 0777, true);
		}
		if( !isset($input['extension']) ) {
			$input['extension'] = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
		}
		do {
			$tmpFile = TEMP_PATH . '/' . generateRandomString(10) . '.tmp';
		} while( file_exists($tmpFile) );
		try {
			copy($url, $tmpFile);
			$input['source_url'] = $url;
			
			return static::import($input, $tmpFile);
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
	public static function importFromDataURL(array $input, string $dataURL): File {
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
			$input['source_type'] = FILE_SOURCE_TYPE_DATA_URI;
			// Empty name by default
		}
		$content = file_get_contents($dataURL);
		$file = static::createAndGet($input);
		file_put_contents($file->getPath(), $content);
		
		return $file;
	}
	
	public static function createAndGet(array $input = [], ?array $fields = null, ?Validation $validation = null): static {
		$input['passkey'] = (new PasswordGenerator())->generate(30);
		if( is_array($fields) ) {
			$fields[] = 'passkey';
		}
		
		return parent::createAndGet($input, $fields, $validation);
	}
	
	public static function getMimeTypeExtension($mimetype): string {
		return match ($mimetype) {
			'image/gif'  => 'gif',
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
		};
	}
	
	/**
	 * Generate link from file id
	 *
	 * @param int $id The file id
	 * @param string $passkey The file passkey
	 * @param boolean $download True to download explicitly the file
	 * @return string The generated link
	 */
	public static function genLink(int $id, string $passkey, bool $download = false): string {
		return u(ROUTE_FILE_DOWNLOAD, ['fileID' => $id]) . '?k=' . urlencode($passkey) . ($download ? '&download' : '');
	}
	
	/**
	 * Get the allowed source types
	 *
	 * @return string[]
	 */
	public static function getSourceTypes(): array {
		return static::$sourceTypes;
	}
	
	/**
	 * Add a source type to the allowed ones
	 */
	public static function addSourceType(string $sourceType): bool {
		if( in_array($sourceType, static::$sourceTypes) ) {
			return false;
		}
		static::$sourceTypes[] = $sourceType;
		
		return true;
	}
	
	/**
	 * Get the allowed usages
	 */
	public static function getUsages(): array {
		if( static::$usages === null ) {
			static::$usages = array_fill_keys(Config::get('file_usages', []), null);
		}
		
		return static::$usages;
	}
	
	/**
	 * Get the allowed usages' name
	 */
	public static function getUsageNames(): array {
		return array_keys(listFileUsages());
	}
	
	/**
	 * Set the given usage option as allowed
	 */
	public static function setUsage(string $usage, ?array $options = null): bool {
		static::$usages[$usage] = $options;
		
		return true;
	}
	
	/**
	 * Get all files with the given usage
	 *
	 * @return File[]
	 */
	public static function getByUsage(string $usage): array {
		return static::requestSelect()
			->where('usage', 'LIKE', $usage)
			->run();
	}
	
	/**
	 * Get all files with the given parent
	 *
	 * @return File[]
	 */
	public static function getByParent(string $usage, PermanentEntity|string $parent): array {
		return static::requestSelect()
			->where('usage', 'LIKE', $usage)
			->where('parent_id', $parent)
			->orderBy('id ASC')
			->run();
	}
	
}

/** @noinspection PhpUnhandledExceptionInspection */
File::initialize('file');
