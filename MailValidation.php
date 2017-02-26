<?php

/*
 * php-mail-validation
 * 
 * $Id: MailValidation.php 4 2017-02-25 20:35:08Z joramk $
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

require_once 'MailValidationHelper.php';

class MailValidation extends MailValidationHelper {
	
	private $EmailAddress;
	
	/**
	 * Constructor to accept an E-Mail address for validation
	 * 
	 * @param string $emailAddress Set the E-Mail address to validate.
	 */
	public function __construct($emailAddress = null) {
		if(!empty($emailAddress)) {
			$this->EmailAddress = $emailAddress;
		} else {
			$this->EmailAddress = '';
		}
	}
	
	/**
	 * Validate the given E-Mail address
	 * 
	 * @return boolean True when the given E-Mail address is valid, otherwise false.
	 */
	public function validate($emailAddress = null) {
		if(is_null($emailAddress)) {
			$emailAddress = $this->EmailAddress;
		}
		if(empty($emailAddress)) {
			return false;
		}
		if(strstr($emailAddress, self::$MailboxDomainSeparator) === false) {
			return false;
		}
		$addressParts = $this->SplitAddressParts($emailAddress);
		$domainPart = idn_to_ascii($addressParts['domain']);
		return $this->CheckControlChars($emailAddress)
				&& $this->CheckLength($emailAddress)
				&& $this->ValidateLocalPart($addressParts['mailbox'])
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
		if (empty($value) || gettype($value) !== 'string') {
			$this->EmailAddress = '';
		} elseif (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			$this->EmailAddress = stripslashes($value);
		} else {
			$this->EmailAddress = $value;
		}
		return $this;
	}
	
	/**
	 * Get the current email address used for validation
	 * 
	 * @return string email address
	 */
	public function getEmailAddress() {
		return $this->EmailAddress;
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
	 * 
	 * @param type $value
	 * @return \MailValidation
	 */
	public function setAllowIpAsDomainPart($value = true) {
		$this->DoAllowIpAsDomainPart = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function getAllowIpAsDomainPart() {
		return $this->DoAllowIpAsDomainPart;
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
	 * 
	 * @return boolean
	 */
	public function getCheckDNS() {
		return $this->DoCheckDNS;
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
	 * 
	 * @return boolean
	 */
	public function getCheckControlChars() {
		return $this->DoCheckControlChars;
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
	 * 
	 * @return boolean
	 */
	public function getCheckLength() {
		return $this->DoCheckLength;
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
	 * 
	 * @return boolean
	 */
	public function getCheckTopLevelDomain() {
		return $this->DoCheckTopLevelDomain;
	}
	
	/**
	 * 
	 * @param type $value
	 * @return \MailValidation
	 */
	public function setValidateLocalPart($value = true) {
		$this->DoValidateLocalPart = $value === true ? true : false;
		return $this;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function getValidateLocalPart() {
		return $this->DoValidateLocalPart;
	}
}
