<?php

require_once('../MailValidation.php');

class MailValidationTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MailValidation
	 */
	protected $object;
	protected $helper;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new MailValidation;
		$this->helper = new MailValidationHelperTest();
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
		$this->assertTrue($this->object->getAllowIpAsDomainPart());
		$this->assertTrue($this->object->getCheckControlChars());
		$this->assertTrue($this->object->getCheckDNS());
		$this->assertTrue($this->object->getCheckLength());
		$this->assertTrue($this->object->getCheckTopLevelDomain());
		$this->assertTrue($this->object->getValidateLocalPart());
		$this->assertTrue($this->object->getEmailAddress() === '');
		$this->assertTrue($this->object->setEmailAddress('joe@iana.org')->validate());
		$this->assertFalse($this->object->setEmailAddress('joe@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('01234@12.12.12.12')->validate());
		$this->assertFalse($this->object->setEmailAddress('01234@312.12.12.12')->validate());
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS());
		$this->assertFalse($this->object->setEmailAddress('.x@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('01234@example.notexistenttld')->validate());
		$this->assertTrue($this->object->setEmailAddress('x@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('x+x@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('01234@[2600::]')->validate());
		$this->assertFalse($this->object->setEmailAddress('@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('123.@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('.123.@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('12..3@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('123@@example.com')->validate());
		/**
		 * BEGIN PMV-1 Test cases
		 */
		$this->assertTrue($this->object->setEmailAddress('niceandsimple@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('a.little.unusual@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('a.little.more.unusual@dept.example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('much."more\ unusual"@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('very.unusual."@".unusual.com@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('very."(),:;<>[]".VERY."very@\\\ \"very".unusual@strange.example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('Abc.example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('Abc.@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('Abc..123@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('A@b@c@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('a"b(c)d,e:f;g<h>i[j\k]l@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('just"not"right@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('this is"not\allowed@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('this\ still\"not\\allowed@example.com')->validate());
		/**
		 * END PMV1- Test cases
		 */
	}

	/**
	 * @covers MailValidation::setEmailAddress
	 * @todo   Implement testSetEmailAddress().
	 */
	public function testSetEmailAddress() {
		$this->assertEquals($this->object->setEmailAddress('joe@example.com')->getEmailAddress(), 'joe@example.com');
		$this->assertEquals($this->object->setEmailAddress(null)->getEmailAddress(), '');
		$this->assertEquals($this->object->setEmailAddress(false)->getEmailAddress(), '');
	}

	public function testDownloadTopLevelDomains() {
		$this->assertTrue(is_writable('../MailValidation.tld'));
		$this->assertInstanceOf(MailValidation::class, $this->object->DownloadTopLevelDomains());
		$this->assertTrue(is_readable('../MailValidation.tld'));
		$content = file('../MailValidation.tld');
		$this->assertTrue(preg_match('/Version [0-9]+, Last Updated/', $content[0]) === 1);
	}

	/**
	 * @covers MailValidation::setAllChecks
	 */
	public function testSetAllChecks() {
		$this->object->setAllChecks(false);
		$this->assertFalse($this->object->getCheckDNS());
		$this->assertFalse($this->object->getCheckControlChars());
		$this->assertFalse($this->object->getCheckLength());
		$this->assertFalse($this->object->getCheckTopLevelDomain());
		$this->assertTrue($this->object->getAllowIpAsDomainPart());
		$this->object->setAllChecks(true);
		$this->assertTrue($this->object->getCheckDNS());
		$this->assertTrue($this->object->getCheckControlChars());
		$this->assertTrue($this->object->getCheckLength());
		$this->assertTrue($this->object->getCheckTopLevelDomain());
		$this->assertTrue($this->object->getAllowIpAsDomainPart());
	}

	/**
	 * @covers MailValidation::setCheckDNS
	 * @covers MailValidation::getCheckDNS
	 */
	public function testSetCheckDNS() {
		$this->assertTrue($this->object->setCheckDNS(true)->getCheckDNS(), true);
		$this->assertFalse($this->object->setEmailAddress('joe@example.com')->validate());
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS(), false);
		$this->assertTrue($this->object->setEmailAddress('joe@example.com')->validate());
	}

	/**
	 * @covers MailValidation::setCheckControlChars
	 * @covers MailValidation::getCheckControlChars
	 */
	public function testSetCheckControlChars() {
		$this->assertTrue($this->object->setCheckControlChars(true)->getCheckControlChars(), true);
		$this->assertFalse($this->object->setCheckControlChars(false)->getCheckControlChars(), false);
	}

	/**
	 * @covers MailValidation::setCheckLength
	 * @covers MailValidation::getCheckLength
	 */
	public function testSetCheckLength() {
		$this->assertTrue($this->object->setCheckLength(true)->getCheckLength(), true);
		$this->assertFalse($this->object->setCheckLength(false)->getCheckLength(), false);
		
		/*
		 * Helper check length max tests
		 */
		$this->assertTrue($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumOverallLength, 'a'),
				MailValidationHelperTest::$MaximumOverallLength));
		$this->assertFalse($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumOverallLength + 1, 'a'),
				MailValidationHelperTest::$MaximumOverallLength));

		$this->assertTrue($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumMailboxNameLength, 'a'),
				MailValidationHelperTest::$MaximumMailboxNameLength));
		$this->assertFalse($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumMailboxNameLength + 1, 'a'),
				MailValidationHelperTest::$MaximumMailboxNameLength));

		$this->assertTrue($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumDomainLength, 'a'),
				MailValidationHelperTest::$MaximumDomainLength));
		$this->assertFalse($this->helper->test_checkLengthMax(str_pad('',
				MailValidationHelperTest::$MaximumDomainLength + 1, 'a'),
				MailValidationHelperTest::$MaximumDomainLength));

		/**
		 * Helper check length min tests
		 */
		$this->assertTrue($this->helper->test_checkLengthMin('1@1.1',
				MailValidationHelperTest::$MinimumMailboxNameLength +
				MailValidationHelperTest::$MinimumDomainLength));
		$this->assertFalse($this->helper->test_checkLengthMin('1@1',
				MailValidationHelperTest::$MinimumMailboxNameLength +
				MailValidationHelperTest::$MinimumDomainLength));

		$this->assertTrue($this->helper->test_checkLengthMin(str_pad('',
				MailValidationHelperTest::$MinimumMailboxNameLength, 'a'),
				MailValidationHelperTest::$MinimumMailboxNameLength));
		$this->assertFalse($this->helper->test_checkLengthMin(str_pad('',
				MailValidationHelperTest::$MinimumMailboxNameLength - 1, 'a'),
				MailValidationHelperTest::$MinimumMailboxNameLength));

		$this->assertTrue($this->helper->test_checkLengthMin(str_pad('',
				MailValidationHelperTest::$MinimumDomainLength, 'a'),
				MailValidationHelperTest::$MinimumDomainLength));
		$this->assertFalse($this->helper->test_checkLengthMin(str_pad('',
				MailValidationHelperTest::$MinimumDomainLength - 1, 'a'),
				MailValidationHelperTest::$MinimumDomainLength));
	}

	/**
	 * @covers MailValidation::setCheckTopLevelDomain
	 * @covers MailValidation::gettCheckTopLevelDomain
	 */
	public function testSetCheckTopLevelDomain() {
		$this->assertTrue($this->object->setCheckTopLevelDomain(true)->getCheckTopLevelDomain(), true);
		$this->assertFalse($this->object->setCheckTopLevelDomain(false)->getCheckTopLevelDomain(), false);
	}

	/**
	 * @covers MailValidation::setAllowIpAsDomainPart
	 * @covers MailValidation::gettAllowIpAsDomainPart
	 */
	public function testSetAllowIpAsDomainPart() {
		$this->assertTrue($this->object->setAllowIpAsDomainPart(true)->getAllowIpAsDomainPart(), true);
		$this->assertFalse($this->object->setAllowIpAsDomainPart(false)->getAllowIpAsDomainPart(), false);
	}
}

class MailValidationHelperTest extends MailValidationHelper {
	public function test_checkLengthMin($value, $length) {
		return $this->_checkLengthMin($value, $length);
	}
	public function test_checkLengthMax($value, $length) {
		return $this->_checkLengthMax($value, $length);
	}
}
