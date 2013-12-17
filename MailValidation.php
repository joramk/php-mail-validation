<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MailValidation {
	
	private $EmailAddress;
	private $DoCheckLength = true;
	private $DoCheckTopLevelDomain = true;
	private $DoCheckControlChars = true;
	private $DoCheckDNS = true;
	
	static private $MailboxDomainSeperator = '@';
	static private $AllowedMailParts = 2;				// don't change!
	static private $MinimumTopLevelDomainLength = 3;
	static private $MaximumTopLevelDomainLength = 256;	// RFC5322 sec. 3.2.3
	static private $MinimumMailboxNameLength = 1;
	static private $MaximumMailboxNameLength = 64;		// RFC5322 sec. 3.4.1
	static private $TopLevelDomainFile = 'MailValidation.tld';
	static private $TopLevelDomainRegEx = '/\.([A-Za-z0-9-])$/';
	static private $ControlCharsRegex = '/[\x00-\x1F\x7F-\xFF]/';
	
	public function __construct($emailAddress = null) {
		if(!empty($emailAddress))
			$this->EmailAddress = $emailAddress;
	}
	
	public function validate() {
		if(empty($this->EmailAddress))
			return false;
		if(strstr($this->EmailAddress, '@' === false))
			return false;
		$addressParts = $this->_splitAddressParts($this->EmailAddress);
		return count($addressParts) == self::$AllowedMailParts
				&& $this->CheckControlChars($this->EmailAddress)
				&& $this->CheckLength($this->EmailAddress)
				&& ($this->CheckIpAddress($addressParts['domain'])
						|| $this->CheckTopLevelDomain($addressParts['domain']))
				;
	} 
	
	public function setEmailAddress($value) {
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
			$this->EmailAddress = stripslashes($value);
		else
			$this->EmailAddress = $value;
		return $this;
	}
	
	public function setAllChecks($value = true) {
		$this->setCheckDNS($value);
		$this->setCheckControlChars($value);
		$this->setCheckLength();
		$this->setCheckTopLevelDomain($value);
		return $this;
	}

	public function setCheckDNS($value = true) {
		$this->DoCheckDNS = $value === true ? true : false;
		return $this;
	}
	
	public function setCheckControlChars($value = true) {
		$this->DoCheckControlChars = $value === true ? true : false;
		return $this;
	}
	
	public function setCheckLength($value = true) {
		$this->DoCheckLength = $value === true ? true : false;
		return $this;
	}
	
	public function setCheckTopLevelDomain($value = true) {
		$this->DoCheckTopLevelDomain = $value === true ? true : false;
		return $this;
	}
	
	private function CheckControlChars($emailAddress) {
		return $this->DoCheckControlChars || $this->_checkControlChars($emailAddress);
	}
	
	private function _checkControlChars($emailAddress) {
		return preg_match(self::$ControlCharsRegex, $emailAddress);
	}
	
	private function CheckTopLevelDomain($domainPart) {
		return !$this->DoCheckTopLevelDomain || $this->_checkTopLevelDomain($domainPart);
	}
	
	private function CheckLength($emailAddress) {
		list($mailbox, $domain) = $this->_splitAddressParts($emailAddress);
		return !$this->DoCheckLength
				|| ($this->_checkLengthMin($mailbox, self::$MinimumMailboxNameLength)
				 && $this->_checkLengthMax($mailbox, self::$MaximumMailboxNameLength)
				 && $this->_checkLengthMin($domain, self::$MinimumTopLevelDomainLength)
				 && $this->_checkLengthMax($domain, self::$MaximumTopLevelDomainLength));
	}
	
	private function _checkTopLevelDomain($domainPart) {
		$matches = preg_match(self::$TopLevelDomainRegEx, $domainPart);
		return in_array($matches[1], file(self::$TopLevelDomainFile));
	}
	
	private function _checkLengthMin($emailAddress, $minimumLength) {
		return strlen($emailAddress) >= $minimumLength;
	}
	
	private function _checkLengthMax($emailAddress, $maximumLength) {
		return strlen($emailAddress) <= $maximumLength;
	}
	
	private function _splitAddressParts($emailAddress) {
		list($mailbox, $domain) = split(self::$MailboxDomainSeperator, $emailAddress);
		return array('mailbox' => $mailbox, 'domain' => $domain);
	}
	
	private function CheckIpAddress($domainPart) {
		return $this->_validateIp4Address($domainPart)
				|| $this->_validateIp6Address($domainPart);
	}
	
	private function _validateIp4Address($ipAddress) {
		return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}
	
	private function _validateIp6Address($ipAddress) {
		return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}
}


?>
