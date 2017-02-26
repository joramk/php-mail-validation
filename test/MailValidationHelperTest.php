<?php

require_once('../MailValidationHelper.php');

class MailValidationHelperTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MailValidationHelper
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new MailValidationHelperProxy;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	public function testCheckLengthMax() {
		/*
		 * Helper check length max tests
		 */
		$this->assertTrue($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumOverallLength, 'a'),
				MailValidationHelperProxy::$MaximumOverallLength));
		$this->assertFalse($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumOverallLength + 1, 'a'),
				MailValidationHelperProxy::$MaximumOverallLength));

		$this->assertTrue($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumMailboxNameLength, 'a'),
				MailValidationHelperProxy::$MaximumMailboxNameLength));
		$this->assertFalse($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumMailboxNameLength + 1, 'a'),
				MailValidationHelperProxy::$MaximumMailboxNameLength));

		$this->assertTrue($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumDomainLength, 'a'),
				MailValidationHelperProxy::$MaximumDomainLength));
		$this->assertFalse($this->object->test_checkLengthMax(str_pad('',
				MailValidationHelperProxy::$MaximumDomainLength + 1, 'a'),
				MailValidationHelperProxy::$MaximumDomainLength));
	}
	
	public function testCheckLengthMin() {
		/**
		 * Helper check length min tests
		 */
		$this->assertTrue($this->object->test_checkLengthMin('1@1.1',
				MailValidationHelperProxy::$MinimumMailboxNameLength +
				MailValidationHelperProxy::$MinimumDomainLength));
		$this->assertFalse($this->object->test_checkLengthMin('1@1',
				MailValidationHelperProxy::$MinimumMailboxNameLength +
				MailValidationHelperProxy::$MinimumDomainLength));

		$this->assertTrue($this->object->test_checkLengthMin(str_pad('',
				MailValidationHelperProxy::$MinimumMailboxNameLength, 'a'),
				MailValidationHelperProxy::$MinimumMailboxNameLength));
		$this->assertFalse($this->object->test_checkLengthMin(str_pad('',
				MailValidationHelperProxy::$MinimumMailboxNameLength - 1, 'a'),
				MailValidationHelperProxy::$MinimumMailboxNameLength));

		$this->assertTrue($this->object->test_checkLengthMin(str_pad('',
				MailValidationHelperProxy::$MinimumDomainLength, 'a'),
				MailValidationHelperProxy::$MinimumDomainLength));
		$this->assertFalse($this->object->test_checkLengthMin(str_pad('',
				MailValidationHelperProxy::$MinimumDomainLength - 1, 'a'),
				MailValidationHelperProxy::$MinimumDomainLength));
	}	
	
	/**
	 * @covers MailValidation::setAllowIpAsDomainPart
	 * @covers MailValidation::gettAllowIpAsDomainPart
	 */
	public function testCheckIpAddress() {
		$this->assertTrue($this->object->testCheckIpAddress('[12.12.12.12]'));
		$this->assertFalse($this->object->testCheckIpAddress('12.12.12.12'));
//		$this->assertFalse($this->object->testCheckIpAddress('[0.12.12.12]'));
		$this->assertFalse($this->object->testCheckIpAddress('[256.12.12.12]'));
		
		$this->assertTrue($this->object->testCheckIpAddress('[1::]'));
		$this->assertTrue($this->object->testCheckIpAddress('[2600::1]'));
		$this->assertFalse($this->object->testCheckIpAddress('1::'));
	}
	
	public function testCheckDNS() {
		$this->assertFalse($this->object->testCheckDNS('example.unexistenttld'));
		$this->assertTrue($this->object->testCheckDNS('gmail.com'));
	}
	
	public function testSplitAddressParts() {
		$this->assertEquals($this->object->testSplitAddressParts('joe@example.com'),
				array('mailbox' => 'joe', 'domain' => 'example.com'));
		$this->assertEquals($this->object->testSplitAddressParts('joe'),
				array('mailbox' => 'joe', 'domain' => ''));
	}
	
	public function testValidateLocalPart() {
		$this->assertTrue($this->object->testValidateLocalPart('abc.defg'));
		$this->assertFalse($this->object->testValidateLocalPart('.abcdefg'));
		$this->assertFalse($this->object->testValidateLocalPart('abcdefg.'));
		$this->assertFalse($this->object->testValidateLocalPart('abc..defg'));
	}
	
	public function testCheckTopLevelDomain() {
		$this->assertTrue($this->object->testCheckTopLevelDomain('com'));
		$this->assertTrue($this->object->testCheckTopLevelDomain('google'));
		$this->assertFalse($this->object->testCheckTopLevelDomain('notexistent'));
		$this->assertTrue($this->object->testCheckTopLevelDomain('de'));
	}
	
	public function testCheckControlChars() {
		$this->assertFalse($this->object->testCheckControlChars(chr(0)));
		$this->assertTrue($this->object->testCheckControlChars(chr(47)));
	}
}

class MailValidationHelperProxy extends MailValidationHelper {
	public function test_checkLengthMin($value, $length) {
		return $this->_checkLengthMin($value, $length);
	}
	
	public function test_checkLengthMax($value, $length) {
		return $this->_checkLengthMax($value, $length);
	}
	
	public function testCheckIpAddress($domainPart) {
		$oldValue = $this->DoAllowIpAsDomainPart;
		$this->DoAllowIpAsDomainPart = true;
		$returnValue = $this->CheckIpAddress($domainPart);
		$this->DoAllowIpAsDomainPart = $oldValue;
		return $returnValue;
	}
	
	public function testCheckDNS($domainPart) {
		return $this->CheckDNS($domainPart);
	}
	
	public function testSplitAddressParts($emailAddress) {
		return $this->SplitAddressParts($emailAddress);
	}
	
	public function testValidateLocalPart($localPart) {
		return $this->ValidateLocalPart($localPart);
	}
	
	public function testCheckTopLevelDomain($domainPart) {
		return $this->CheckTopLevelDomain($domainPart);
	}
	
	public function testCheckControlChars($emailAddress) {
		return $this->CheckControlChars($emailAddress);
	}
}
