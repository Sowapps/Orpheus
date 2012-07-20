<?php
//! The Email class
/*!
	This class is a tool to send mails.
*/
class Email {
	//Attributes
	private $Headers = array(
		'MIME-Version' => '',
		'Content-Type' => 'text/plain, charset=UTF-8',
		'Date' => '',//See init()
		'From' => 'no-reply@nodomain.com',//Override PHP's default
		'Sender' => '',
		'X-Sender' => '',
		'Reply-To' => '',//Return Email Address
		'Return-Path' => '',//Return Email Address
		'Organization' => '',
		'X-Priority' => '3',
		'X-Mailer' => 'Florent Hazard\'s Mailer',
		'Bcc' => '',
	);
	
	private $HTMLBody;
	private $TEXTBody;
	private $AltBody;
	
	/* Attached Files
	Content Array of form:
	array(
		'the_file_name',
	)
	*/
	private $AttFiles = array();
	
	private $Subject;
	private $Type=0;// Bit value, 1=>Text, 2=>HTML
	private $MIMEBoundary = array();
	
	public static $TEXTTYPE = 1;
	public static $HTMLTYPE = 2;

	//Methods
	public function __construct($TEXTBody='', $Subject='') { //Class' Constructor
		$this->init();
		$this->Subject = $Subject;
		$this->setTEXTBody($TEXTBody);
	}
	
	private function init() {
		$this->Headers['Date'] = date('r');
		if( defined('ADMINEMAIL') ) {
			if( defined('SITENAME') ) {
				$this->setSender(SITENAME.' <'.ADMINEMAIL.'>');
			} else {
				$this->setSender(ADMINEMAIL);
			}
		}
	}
	
	public function setHeader($Key, $Value) {
		if( !isset($this->Headers[$Key]) ) {
			throw new Exception('UnknownHeader');
			return false;
		}
		$this->Headers[$Key] = $Value;
	}
	
	public function setType($Type) {
		$Type = (int) $Type;
		if( $Type < 0 ) {
			$Substract = 1;
			$Type = -$Type;
		}
		if( !($Type & self::TEXTTYPE) && !($Type & self::HTMLTYPE) ) {
			throw new Exception('InvalidType');
			return false;
		}
		$this->Type = ( empty($Substract) ) ? $this->Type | $Type : $this->Type ^ $Type;
	}
	
	public function containFile($Filename) {
		return in_array($Filename, $this->AttFiles);
	}
	
	public function addFile($Filename) {
		if( $this->containFile($Filename) ) {
			throw new Exception('FileAlreadyContained');
		}
		$this->AttFiles[] = $Filename;
	}
	
	public function removeFile($Filename) {
		if( ($key = array_search($Filename, $this->AttFiles)) === false ) {
			throw new Exception('FileNotContained');
		}
		unset($this->AttFiles[$key]);
	}
	
	public function setSubject($Subject) {
		if( !is_string($Subject) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->Subject = $Subject;
	}
	
	public function setTEXTBody($Body) {
		if( !is_string($Body) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->TEXTBody = $Body;
	}
	
	public function setHTMLBody($Body) {
		if( !is_string($Body) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->HTMLBody = $Body;
	}
	
	public function setAltBody($Body) {
		if( !is_string($Subject) ) {
			throw new Exception('RequireStringParameter');
		}
		$this->AltBody = $Body;
	}
	
	public function send($ToAddress) {
		if( empty($ToAddress) || (!self::is_email($ToAddress) && !is_array($ToAddress)) ) {
			throw new Exception('InvalidEmailAddress');
		}
		
		if( $this->isMultiContent() ) {
			$Boundary = $this->getBoundary();
			$this->setHeader('MIME-Version', '1.0');
			$this->setHeader('Content-Type', "multipart/alternative; boundary=\"{$Boundary}\"");
			$Body = '';
			$ContentsArr = array();
			if( $this->isAlternative() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'multipart/alternative',
					),
					'body' => ( mb_detect_encoding($str, "UTF-8") == "UTF-8" ) ? utf8_decode($this->AltBody) : $this->AltBody,
				);
			}
			
			if( $this->isTEXT() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'text/plain; charset="UTF-8"',
						'Content-Transfer-Encoding' => 'quoted-printable',
					),
					'body' => $this->TEXTBody,
				);
			}
			
			if( $this->isHTML() ) {
				$ContentsArr[] = array(
					'headers' => array(
						'Content-Type' => 'text/html; charset="UTF-8"',
						'Content-Transfer-Encoding' => 'quoted-printable',
					),
					'body' => $this->HTMLBody,
				);
			}
			
			if( $this->containsFiles() ) {
				$this->setHeader('Content-Type', "multipart/mixed; boundary=\"{$Boundary}\"");
				
				//With files, mail content is overloaded, also we make a blocklist under a bloc with own boundary.
				$subContentsArr = $ContentsArr;
				if( !empty($subContentsArr) ) {
					$ContentsArr = array();
					$subBoundary = $this->getBoundary(1);
					$subBody = '';
					
					foreach($subContentsArr as $Content) {
						$subHeaders = '';
						$Content['headers']['Content-Type'] .= '; format=flowed';
						foreach( $Content['headers'] as $headerName => $headerValue ) {
							$subHeaders .= "{$headerName}: {$headerValue}\n";
						}
						$subBody .= <<<BODY
--{$subBoundary}
{$subHeaders}
{$Content['body']}


BODY;
					}
					$subBody .= <<<BODY
--{$subBoundary}--
BODY;
					$ContentsArr[] = array(
						'headers' => array(
							'Content-Type' => "multipart/alternative; boundary=\"{$subBoundary}\"",
						),
						'body' => $subBody,
					);
					
				}
				
				foreach( $this->AttFiles as $fileName ) {
					if( !is_readable($fileName) ) {
						continue;
					}
					$ContentsArr[] = array(
						'headers' => array(
							'Content-Type' => self::getMimeType($fileName).'; name="'.pathinfo($fileName, PATHINFO_BASENAME).'"',
							'Content-Transfer-Encoding' => 'base64',
							'Content-Disposition' => 'attachment; filename="'.pathinfo($fileName, PATHINFO_BASENAME).'"',
						),
						'body' => chunk_split(base64_encode(file_get_contents($fileName))),
					);
				}
			}
			if( !empty($ContentsArr) ) {
				$Body = '';
				
				foreach($ContentsArr as $Content) {
					$ContentHeaders = '';
					
					if( empty($Content['headers']) ) {
						throw new Exception('ContentRequireHeaders');
					}
					if( empty($Content['body']) ) {
						throw new Exception('ContentRequireBody');
					}
					foreach( $Content['headers'] as $headerName => $headerValue ) {
						$ContentHeaders .= "{$headerName}: {$headerValue}\n";
					}
					$Body .= <<<BODY
--{$Boundary}
{$ContentHeaders}
{$Content['body']}


BODY;
				}
				$Body .= <<<BODY
--{$Boundary}--
BODY;
			}
			
		} else {
			if( $this->isHTML() ) {
				$this->setHeader('MIME-Version', '1.0');
				$this->setHeader('Content-Type', 'text/html; charset="UTF-8"');
				$Body = $this->HTMLBody;
			
			} else if( $this->isTEXT() ) {
				$this->setHeader('MIME-Version', '');
				$this->setHeader('Content-Type', 'text/plain; charset="UTF-8"');
				$Body = $this->TEXTBody;
			
			}
			
		}
		if( empty($Body) ) {
			throw new Exception('EmptyMail');
		}
		
		$Headers = '';
		foreach($this->Headers as $headerName => $headerValue ) {
			if( !empty($headerValue) ) {
				$Headers .= "{$headerName}: {$headerValue}\r\n";
			}
		}
		$Headers .= "\r\n";
		if( !is_array($ToAddress) ) {
			if( !mail($ToAddress, $this->Subject, $Body, $Headers) ) {
				throw new Exception("ProblemSendingMail");
			}
		} else {
			foreach($ToAddress as $MailToData) {
				$MailToEmail = '';
				if( self::is_email($MailToData) ) {
					$MailToEmail = $MailToData;
					
				//More compatibilities with array of data.
				} else if( is_array($MailToData) ) {
					if( !empty($MailToData['mail']) && self::is_email($MailToData['mail']) ) {
						$MailToEmail = $MailToData['mail'];
					} elseif( !empty($MailToData['email']) && self::is_email($MailToData['email']) ) {
						$MailToEmail = $MailToData['email'];
					}
				}
				if( empty($MailToEmail) ) {
					throw new Exception("EmptyEmailAddress");
				}
				if( !mail($MailToEmail, $this->Subject, $Body, $Headers)) {
					throw new Exception("ProblemSendingMail");
				}
			}
		}
		return true;
	}
	
	public function setReplyTo($ReplyToEmailAdd) {
		$this->setHeader('Return-Path', $ReplyToEmailAdd);
		$this->setHeader('Reply-To', $ReplyToEmailAdd);
	}
	
	public function setSender($SendEmailAdd) {
		$this->setHeader('From', $SendEmailAdd);
		$this->setHeader('Sender', $SendEmailAdd);
		if( empty($Headers['Return-Path']) ) {
			$this->setReplyTo($SendEmailAdd);
		}
	}
	
	public function getBoundary($BoundaryInd=0) {
		if( empty($this->MIMEBoundary[$BoundaryInd]) ) {
			$this->MIMEBoundary[$BoundaryInd] = '-=%IGSTAFF_'.md5(microtime(1)+$BoundaryInd).'%=-';
		}
		return $this->MIMEBoundary[$BoundaryInd];
	}
	
	public function isHTML() {
		return !empty($this->HTMLBody);
	}
	
	public function isTEXT() {
		return !empty($this->TEXTBody);
	}
	
	public function isAlternative() {
		return !empty($this->AltBody);
	}
	
	public function containsFiles() {
		return !empty($this->AttFiles);
	}
	
	public function isMultiContent() {
		return ( $this->isHTML() + $this->isTEXT() + $this->containsFiles() ) > 1;
	}
	
	public static function is_email($mail) {
		$atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
		$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
		$regex = '/^' . $atom . '+(\.' . $atom . '+)*@(' . $domain . '{1,63}\.)+' . $domain . '{2,63}$/i';
		return is_string($mail) && preg_match($regex, $mail);
	}

	public static function getMimeType($FileName) {
		if( function_exists('finfo_open') ) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			return finfo_file($finfo, $FileName);
		}
		return mime_content_type($FileName);
	}
}
?>