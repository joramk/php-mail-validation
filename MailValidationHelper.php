<?php

/*
 * php-mail-validation
 * 
 * $Id: MailValidationHelper.php 4 2017-02-25 20:35:08Z joramk $
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

class MailValidationHelper {

	protected $DoCheckLength = true;
	protected $DoCheckTopLevelDomain = true;
	protected $DoCheckControlChars = true;
	protected $DoCheckDNS = true;
	protected $DoAllowIpAsDomainPart = false;
	protected $DoValidateLocalPart = true;
	
	static public $MailboxDomainSeparator = '@';
	static public $MinimumDomainLength = 3;
	static public $MaximumDomainLength = 255;
	static public $MinimumMailboxNameLength = 1;
	static public $MaximumMailboxNameLength = 64;
	static public $MaximumOverallLength = 256; // RFC 2821
	static public $TopLevelDomainUpdateUrl = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt';
	static public $TopLevelDomainFile = 'MailValidation.tld';
	static public $TopLevelDomainRegEx = '/[A-Za-z0-9-]+$/';
	static public $ControlCharsRegEx = '/[\x00-\x1F\x7F-\xFF]/';
	static public $ValidateIp4RegEx = '/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/';
	static public $ValidateIp6RegEx = '/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i';

	/**
	 * Check the given E-Mail address against malicious control characters
	 * 
	 * @param string $emailAddress E-Mail address
	 * @return boolean True if control chars are detected
	 */
	protected function CheckControlChars($emailAddress) {
		return !$this->DoCheckControlChars ||
				preg_match(self::$ControlCharsRegEx, $emailAddress) !== 1;
	}
	
	/**
	 * Checks wether the given domain part has a valid TLD or not
	 * @param string $domainPart
	 * @return boolean True if the TLD is well-known to IANA
	 */
	protected function CheckTopLevelDomain($domainPart) {
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
	protected function CheckLength($mailboxOrMailAddress, $domain = null) {
		$mailbox = $mailboxOrMailAddress;
		if (is_null($domain)) {
			list($mailbox, $domain) = array_values($this->SplitAddressParts($mailboxOrMailAddress));
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
	protected function _checkTopLevelDomain($domainPart) {
		$matches = array();
		return preg_match(self::$TopLevelDomainRegEx, $domainPart, $matches) === 1
				&& in_array(strtoupper($matches[0]), file(dirname(__FILE__) .
				DIRECTORY_SEPARATOR . self::$TopLevelDomainFile,
				FILE_USE_INCLUDE_PATH | FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}
	
	/**
	 * Checks if a string is at least as long as the given integer value
	 * @param string $emailAddress The E-Mail address to check
	 * @param int $minimumLength The required minimum length to pass this check
	 * @return boolean True if the minimum length is not below the given length
	 */
	protected function _checkLengthMin($emailAddress, $minimumLength) {
		return strlen($emailAddress) >= $minimumLength;
	}
	
	/**
	 * 
	 * @param type $emailAddress
	 * @param type $maximumLength
	 * @return boolean True if the maximum length is not exceeded
	 */
	protected function _checkLengthMax($emailAddress, $maximumLength) {
		return strlen($emailAddress) <= $maximumLength;
	}
	
	/**
	 * Splits the given E-Mail address into its local and domain parts
	 * 
	 * @param string $emailAddress The E-Mail address to split into two parts
	 * @return array An associative array consisting of the 'mailbox' and 'domain' part.
	 */
	protected function SplitAddressParts($emailAddress) {
		if (($seperatorPosition = strrpos($emailAddress, self::$MailboxDomainSeparator)) === false) {
			return array('mailbox' => $emailAddress, 'domain' => '');
		}
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
	protected function CheckIpAddress($domainPart) {
		if (!$this->DoAllowIpAsDomainPart || empty($domainPart)) {
			return false;
		}
		if (strlen($domainPart) < 5) {
			return false;
		}
		if (preg_match('/\[.+\]$/', $domainPart) !== 1) {
			return false;
		}
		$unquotedIpAddress = substr($domainPart, 1, strlen($domainPart) - 2);
		return $this->_validateIp4Address($unquotedIpAddress)
				|| $this->_validateIp6Address($unquotedIpAddress);
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

	/**
	 * Checks if the domain has at least one valid MX record entry.
	 * 
	 * @param type $domainPart
	 * @return boolean True if domain has at least one valid MX record
	 */
	protected function CheckDNS($domainPart) {
		return !$this->DoCheckDNS || $this->_checkDNS($domainPart);
	}
	
	/**
	 * Checks the domain mx record entry
	 * 
	 * @param type $domainPart
	 * @return boolean True if domain has at least one valid MX record
	 */
	private function _checkDNS($domainPart) {
		foreach (dns_get_record($domainPart, DNS_MX) as $dnsRecord) {
			if (checkdnsrr($dnsRecord['target'], 'A')) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Validate the local part of an email address
	 * 
	 * @param type $localPart
	 * @return type
	 */
	protected function ValidateLocalPart($localPart) {
		return !$this->DoValidateLocalPart || !(preg_match('/(^\.|\.$|\.\.)/', $localPart) === 1);
	}
}
