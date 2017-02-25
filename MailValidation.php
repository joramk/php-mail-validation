<?php

/*
 * php-mail-validation
 * 
 * $Id: MailValidation.php 4 2013-12-17 20:35:08Z joramk $
 * 
 * @summary   Validate an E-Mail address against various checks.
 * @tutorial  $validated = new MailValidation('joe@example.org')->validate();
 * @author    Joram Knaack <joramk@gmail.com>
 * @copyright (C) Copyright 2013 Joram Knaack
 * @license   http://www.gnu.org/licenses/gpl.html
 * @link      http://code.google.com/p/php-mail-validation/
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class MailValidation {
	
	private $EmailAddress;
	
	private $DoCheckLength = true;
	private $DoCheckTopLevelDomain = true;
	private $DoCheckControlChars = true;
	private $DoCheckDNS = true;
	private $DoAllowIpAsDomainPart = true;
	
	static private $MailboxDomainSeparator = '@';
	static private $MinimumDomainLength = 3;
	static private $MaximumDomainLength = 255;
	static private $MinimumMailboxNameLength = 1;
	static private $MaximumMailboxNameLength = 64;
	static private $MaximumOverallLength = 256; // RFC 2821
	static private $TopLevelDomainFile = 'MailValidation.tld';
	static private $TopLevelDomainRegEx = '/\.([A-Za-z0-9-])$/';
	static private $ControlCharsRegEx = '/[\x00-\x1F\x7F-\xFF]/';
	static private $ValidateIp4RegEx = '/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/';
	static private $ValidateIp6RegEx = '/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i';
	
	/**
	 * Constructor to accept an E-Mail address for validation
	 * 
	 * @param string $emailAddress Set the E-Mail address to validate.
	 */
	public function __construct($emailAddress = null) {
		if(!empty($emailAddress)) {
			$this->EmailAddress = $emailAddress;
		}
	}
	
	/**
	 * Validate the given E-Mail address
	 * 
	 * @return boolean True when the given E-Mail address is valid, otherwise false.
	 */
	public function validate() {
		if(empty($this->EmailAddress)) {
			return false;
		}
		if(strstr($this->EmailAddress, self::$MailboxDomainSeparator) === false) {
			return false;
		}
		$addressParts = $this->_splitAddressParts($this->EmailAddress);
		$domainPart = $this->_convertUtf8ToIdnDomain($addressParts['domain']);
		return $this->CheckControlChars($this->EmailAddress)
				&& $this->CheckLength($this->EmailAddress)
				&& ($this->CheckIpAddress($domainPart)
						|| ($this->CheckTopLevelDomain($domainPart)
								&& $this->CheckDNS($domainPart)))
				;
	} 
	
	/**
	 * Sets the E-Mail address to validate.
	 * 
	 * @param string $value E-Mail address
	 * @return \MailValidation
	 */
	public function setEmailAddress($value) {
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			$this->EmailAddress = stripslashes($value);
		} else {
			$this->EmailAddress = $value;
		}
		return $this;
	}
	
	/**
	 * Activates or deacitvates all checks
	 * 
	 * @param boolean $value True to activate all checks, false to disable them
	 * @return \MailValidation
	 */
	public function setAllChecks($value = true) {
		$this->setCheckDNS($value);
		$this->setCheckControlChars($value);
		$this->setCheckLength($value);
		$this->setCheckTopLevelDomain($value);
		return $this;
	}

	/**
	 * Activates or deactivates the DNS record check for the domain part
	 * of an E-Mail address.
	 * 
	 * @param boolean $value True to activate the check, false to disable it
	 * @return \MailValidation
	 */
	public function setCheckDNS($value = true) {
		$this->DoCheckDNS = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * Activates or deactivates the check for special control chars
	 * which may be malicious included in the E-Mail address.
	 * 
	 * @param boolean $value True to activate the check, false to disable it
	 * @return \MailValidation
	 */
	public function setCheckControlChars($value = true) {
		$this->DoCheckControlChars = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * Activates or deactivates the check for the length of the
	 * E-Mail address.
	 * 
	 * @param boolean $value True to activate the check, false to disable it
	 * @return \MailValidation
	 */
	public function setCheckLength($value = true) {
		$this->DoCheckLength = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * Activates or deactivates the check for valid top level domain
	 * in the domain part of an E-Mail address.
	 * 
	 * @param type $value
	 * @return \MailValidation
	 */
	public function setCheckTopLevelDomain($value = true) {
		$this->DoCheckTopLevelDomain = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * Check the given E-Mail address against malicious control characters
	 * 
	 * @param string $emailAddress E-Mail address
	 * @return boolean True if control chars are detected
	 */
	private function CheckControlChars($emailAddress) {
		return !$this->DoCheckControlChars || $this->_checkControlChars($emailAddress);
	}
	
	/**
	 * 
	 * @param type $emailAddress
	 * @return type
	 */
	private function _checkControlChars($emailAddress) {
		return preg_match(self::$ControlCharsRegEx, $emailAddress);
	}
	
	/**
	 * 
	 * @param type $domainPart
	 * @return type
	 */
	private function _convertUtf8ToIdnDomain($domainPart) {
		return idn_to_ascii($domainPart);
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
	private function CheckLength($mailboxOrMailAddress, $domain = null) {
		if (is_null($domain)) {
			list($mailbox, $domain) = $this->_splitAddressParts($mailboxOrMailAddress);
		} else {
			$mailbox = $mailboxOrMailAddress;
		}
		return !$this->DoCheckLength
				|| ($this->_checkLengthMax($mailbox . self::$MailboxDomainSeparator . $domain, self::$MaximumOverallLength)
				 && $this->_checkLengthMin($mailbox, self::$MinimumMailboxNameLength)
				 && $this->_checkLengthMax($mailbox, self::$MaximumMailboxNameLength)
				 && $this->_checkLengthMin($domain, self::$MinimumDomainLength)
				 && $this->_checkLengthMax($domain, self::$MaximumDomainLength));
	}
	
	/**
	 * Checks if the TLD of the domain part is well-known (internet routable)
	 * against a text file taken from IANA which includes all valid TLDs.
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
		$seperatorPosition = strrpos($emailAddress, self::$MailboxDomainSeparator);
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
	 * or uses preg_matc as fallback if function is missing or the
	 * validate_ip filter is not available.
	 * 
	 * @param string $ipAddress
	 * @return boolean True if the supplied address is a valid IPv4 address
	 */
	private function _validateIp4Address($ipAddress) {
		if (function_exists('filter_var')
				&& in_array('validate_ip', filter_list())) {
			return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
		} else {
			return preg_match(self::$ValidateIp4RegEx, $ipAddress);
		}
	}
	
	/**
	 * Validates an IPv6 address utilising PHP's filter_var function
	 * 
	 * @param type $ipAddress
	 * @return boolean True if the supplied address is a valid IPv6 address
	 */
	private function _validateIp6Address($ipAddress) {
		if(function_exists('filter_var')
				&& in_array('validate_ip', filter_list())) {
			return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
		} else {
			return preg_match(self::$ValidateIp6RegEx, $ipAddress);
		} 
	}

	private function CheckDNS($domainpart) {
		return !$this->DoCheckDNS || $this->_checkDNS($domainpart);
	}
	
	private function _checkDNS($domainpart) {
		return getmxrr($domainpart);
	}
}

