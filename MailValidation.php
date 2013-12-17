<?php

/*
 * php-mail-validation
 * 
 */

class MailValidation {
	
	private $EmailAddress;
	private $DoCheckLength = true;
	private $DoCheckTopLevelDomain = true;
	private $DoCheckControlChars = true;
	private $DoCheckDNS = true;
	private $DoAllowIpAsDomainPart = true;
	
	static private $MailboxDomainSeperator = '@';
	static private $MinimumDomainLength = 3;
	static private $MaximumDomainLength = 255;
	static private $MinimumMailboxNameLength = 1;
	static private $MaximumMailboxNameLength = 64;
	static private $MaximumOverallLength = 256; // RFC 2821
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
		return $this->CheckControlChars($this->EmailAddress)
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
	
	/**
	 * Checks wether the given domain part has a valid TLD or not
	 * @param string $domainPart
	 * @return boolean True if the TLD is well-known to IANA
	 */
	private function CheckTopLevelDomain($domainPart) {
		return !$this->DoCheckTopLevelDomain || $this->_checkTopLevelDomain($domainPart);
	}

	/**
	 * Checks the given address for its overall length and the length
	 * of its local(mailbox) and domain parts.
	 * 
	 * http://www.rfc-editor.org/rfc/rfc3696.txt
	 * http://www.rfc-editor.org/errata_search.php?rfc=3696&eid=1690
	 * 
	 * @param string $emailAddress The email address to check the length for
	 * @return boolean True if overall length check and check for both parts are passed
	 */
	private function CheckLength($emailAddress) {
		list($mailbox, $domain) = $this->_splitAddressParts($emailAddress);
		return !$this->DoCheckLength
				|| ($this->_checkLengthMax($emailAddress, self::$MaximumOverallLength)
				 && $this->_checkLengthMin($mailbox, self::$MinimumMailboxNameLength)
				 && $this->_checkLengthMax($mailbox, self::$MaximumMailboxNameLength)
				 && $this->_checkLengthMin($domain, self::$MinimumDomainLength)
				 && $this->_checkLengthMax($domain, self::$MaximumDomainLength));
	}
	
	/**
	 * Checks if the TLD of the domain part is well-known (internet routable)
	 * 
	 * http://data.iana.org/TLD/tlds-alpha-by-domain.txt
	 * 
	 * @param string $domainPart The domain part from the E-Mail address to check
	 * @return boolean True if the TLD of the domain part is known to the IANA
	 */
	private function _checkTopLevelDomain($domainPart) {
		$matches = preg_match(self::$TopLevelDomainRegEx, $domainPart);
		return in_array($matches[1], file(self::$TopLevelDomainFile));
	}
	
	/**
	 * Checks if a string is at least as long as the given integer value
	 * @param string $emailAddress The E-Mail address to check
	 * @param int $minimumLength The required minimum length to pass this check
	 * @return boolean True if the minimum length is not below the given length
	 */
	private function _checkLengthMin($emailAddress, $minimumLength) {
		return strlen($emailAddress) >= $minimumLength;
	}
	
	/**
	 * 
	 * @param type $emailAddress
	 * @param type $maximumLength
	 * @return boolean True if the maximum length is not exceeded
	 */
	private function _checkLengthMax($emailAddress, $maximumLength) {
		return strlen($emailAddress) <= $maximumLength;
	}
	
	/**
	 * Splits the given E-Mail address into its local and domain parts
	 * 
	 * @param string $emailAddress The E-Mail address to split into two parts
	 * @return array An associative array consisting of the 'mailbox' and 'domain' part.
	 */
	private function _splitAddressParts($emailAddress) {
		$seperatorPosition = strrpos($emailAddress, self::$MailboxDomainSeperator);
		return array('mailbox' => substr($emailAddress, 0, $seperatorPosition),
				'domain' => substr($emailAddress, $seperatorPosition + 1));
	}
	
	/**
	 * Checks if the domain part is a correct formatted IPv4 or IPv6 address
	 * i.e. [127.0.0.1] or [::1]
	 * 
	 * @param type $domainPart
	 * @return boolean True if the domain part is a valid IP address in brackets
	 */
	private function CheckIpAddress($domainPart) {
		if (!preg_match('/\^[.?\]$/')) {
			return false;
		} else {
			$unquotedIpAddress = substr($domainPart, 1, strlen($domainPart) - 2);
			return !$this->DoAllowIpAsDomainPart || !($this->_validateIp4Address($unquotedIpAddress)
					&& $this->_validateIp6Address($unquotedIpAddress));
		}
	}
	
	/**
	 * Validates an IPv4 address utilising PHP's filter_var function
	 * 
	 * @param string $ipAddress
	 * @return boolean True if the supplied address is a valid IPv4 address
	 */
	private function _validateIp4Address($ipAddress) {
		return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}
	
	/**
	 * Validates an IPv6 address utilising PHP's filter_var function
	 * 
	 * @param type $ipAddress
	 * @return boolean True if the supplied address is a valid IPv6 address
	 */
	private function _validateIp6Address($ipAddress) {
		return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}
}
