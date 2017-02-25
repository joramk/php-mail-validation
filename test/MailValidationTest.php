<?php

require_once('../MailValidation.php');

class MailValidationTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MailValidation
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new MailValidation;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers MailValidation::validate
	 * @todo   Implement testValidate().
	 */
	public function testValidate() {
		$this->object->setEmailAddress('joe@iana.org');
		$this->object->setAllChecks(true);
		$this->assertTrue($this->object->validate());

		$this->object->setEmailAddress('joe@example.com');
		$this->assertFalse($this->object->validate());
	}

	/**
	 * @covers MailValidation::setEmailAddress
	 * @todo   Implement testSetEmailAddress().
	 */
	public function testSetEmailAddress() {
		$this->assertEquals($this->object->setEmailAddress('joe@example.com')->EmailAddress, 'joe@example.com');
		$this->assertEquals($this->object->setEmailAddress(null)->EmailAddress, '');
		$this->assertEquals($this->object->setEmailAddress(false)->EmailAddress, '');
	}

	/**
	 * @covers MailValidation::setAllChecks
	 * @todo   Implement testSetAllChecks().
	 */
	public function testSetAllChecks() {
		$this->object->setAllChecks(false);
		$this->assertFalse($this->object->DoCheckDNS);
		$this->assertFalse($this->object->DoCheckControlChars);
		$this->assertFalse($this->object->DoCheckLength);
		$this->assertFalse($this->object->DoCheckTopLevelDomain);
		$this->object->setAllChecks(true);
		$this->assertTrue($this->object->DoCheckDNS);
		$this->assertTrue($this->object->DoCheckControlChars);
		$this->assertTrue($this->object->DoCheckLength);
		$this->assertTrue($this->object->DoCheckTopLevelDomain);
	}

	/**
	 * @covers MailValidation::setCheckDNS
	 * @todo   Implement testSetCheckDNS().
	 */
	public function testSetCheckDNS() {
		$this->assertTrue($this->object->setCheckDNS(true)->DoCheckDNS, true);
		$this->assertFalse($this->object->setEmailAddress('joe@example.com')->validate());
		$this->assertFalse($this->object->setCheckDNS(false)->DoCheckDNS, false);
		$this->assertTrue($this->object->setEmailAddress('joe@example.com')->validate());
	}

	/**
	 * @covers MailValidation::setCheckControlChars
	 * @todo   Implement testSetCheckControlChars().
	 */
	public function testSetCheckControlChars() {
		$this->assertTrue($this->object->setCheckControlChars(true)->DoCheckControlChars, true);
		$this->assertFalse($this->object->setCheckControlChars(false)->DoCheckControlChars, false);
	}

	/**
	 * @covers MailValidation::setCheckLength
	 * @todo   Implement testSetCheckLength().
	 */
	public function testSetCheckLength() {
		$this->assertTrue($this->object->setCheckLength(true)->DoCheckLength, true);
		$this->assertFalse($this->object->setCheckLength(false)->DoCheckLength, false);
	}

	/**
	 * @covers MailValidation::setCheckTopLevelDomain
	 * @todo   Implement testSetCheckTopLevelDomain().
	 */
	public function testSetCheckTopLevelDomain() {
		$this->assertTrue($this->object->setCheckTopLevelDomain(true)->DoCheckTopLevelDomain, true);
		$this->assertFalse($this->object->setCheckTopLevelDomain(false)->DoCheckTopLevelDomain, false);
	}
}
