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
	public function testDefaults() {
		$this->assertFalse($this->object->getAllowIpAsDomainPart());
		$this->assertTrue($this->object->getCheckControlChars());
		$this->assertTrue($this->object->getCheckDNS());
		$this->assertTrue($this->object->getCheckLength());
		$this->assertTrue($this->object->getCheckTopLevelDomain());
		$this->assertTrue($this->object->getValidateLocalPart());
		$this->assertTrue($this->object->getEmailAddress() === '');
	}
	
	public function testValidate() {
		$this->assertTrue($this->object->setEmailAddress('joe@iana.org')->validate());
		$this->assertFalse($this->object->setEmailAddress('joe@example.com')->validate());
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS());
		$this->assertFalse($this->object->setEmailAddress('.x@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('01234@example.notexistenttld')->validate());
		$this->assertTrue($this->object->validate('x@example.com'));
		$this->assertTrue($this->object->setEmailAddress('x+x@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('123.@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('.123.@example.com')->validate());
		$this->assertFalse($this->object->setEmailAddress('12..3@example.com')->validate());
		$this->assertTrue($this->object->setEmailAddress('123@@example.com')->validate());
	}
	
	public function testGenericAddresses() {
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS());
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
//		$this->assertFalse($this->object->setEmailAddress('this is"not\allowed@example.com')->validate());
//		$this->assertFalse($this->object->setEmailAddress('this\ still\"not\\allowed@example.com')->validate());
	}

	public function testValidAddresses() {
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS());
		$this->assertTrue($this->object->setAllowIpAsDomainPart()->getAllowIpAsDomainPart());
		$this->assertTrue($this->object->validate('email@example.com'));             
		$this->assertTrue($this->object->validate('firstname.lastname@example.com'));
		$this->assertTrue($this->object->validate('email@subdomain.example.com'));   
		$this->assertTrue($this->object->validate('firstname+lastname@example.com'));
//		$this->assertTrue($this->object->validate('email@123.123.123.123'));         
		$this->assertTrue($this->object->validate('email@[123.123.123.123]'));       
		$this->assertTrue($this->object->validate('"email"@example.com'));           
		$this->assertTrue($this->object->validate('1234567890@example.com'));        
		$this->assertTrue($this->object->validate('email@example-one.com'));         
		$this->assertTrue($this->object->validate('_______@example.com'));           
		$this->assertTrue($this->object->validate('email@example.name'));           
		$this->assertTrue($this->object->validate('email@example.museum'));          
		$this->assertTrue($this->object->validate('email@example.co.jp'));           
		$this->assertTrue($this->object->validate('firstname-lastname@example.com'));
		$this->assertTrue($this->object->validate('much."more\ unusual"@example.com'));                                 
		$this->assertTrue($this->object->validate('very.unusual."@".unusual.com@example.com'));                         
		$this->assertTrue($this->object->validate('very."(),:;<>[]".VERY."very@\\ "very".unusual@strange.example.com'));
	}

	public function testInvalidAddresses() {
		$this->assertFalse($this->object->setCheckDNS(false)->getCheckDNS());
		$this->assertTrue($this->object->setAllowIpAsDomainPart()->getAllowIpAsDomainPart());
		$this->assertFalse($this->object->validate('plainaddress'));                 
//		$this->assertFalse($this->object->validate('#@%^%#$@#$@#.com'));             
		$this->assertFalse($this->object->validate('@example.com'));                 
		$this->assertFalse($this->object->validate('Joe Smith <email@example.com>'));
		$this->assertFalse($this->object->validate('email.example.com'));            
//		$this->assertFalse($this->object->validate('email@example@example.com'));    
		$this->assertFalse($this->object->validate('.email@example.com'));           
		$this->assertFalse($this->object->validate('email.@example.com'));           
		$this->assertFalse($this->object->validate('email..email@example.com'));     
//		$this->assertFalse($this->object->validate('?????@example.com'));            
		$this->assertFalse($this->object->validate('email@example.com (Joe Smith)'));
		$this->assertFalse($this->object->validate('email@example'));                
//		$this->assertFalse($this->object->validate('email@-example.com'));           
		$this->assertFalse($this->object->validate('email@example.web'));            
		$this->assertFalse($this->object->validate('email@111.222.333.44444'));      
		$this->assertFalse($this->object->validate('email@example..com'));           
		$this->assertFalse($this->object->validate('Abc..123@example.com'));        
		$this->assertFalse($this->object->validate('“(),:;<>[\]@example.com'));                
//		$this->assertFalse($this->object->validate('just"not"right@example.com'));             
//		$this->assertFalse($this->object->validate('this\ is"really"not\allowed@example.com'));
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
		$this->assertFalse($this->object->getAllowIpAsDomainPart());
		$this->object->setAllChecks(true);
		$this->assertTrue($this->object->getCheckDNS());
		$this->assertTrue($this->object->getCheckControlChars());
		$this->assertTrue($this->object->getCheckLength());
		$this->assertTrue($this->object->getCheckTopLevelDomain());
		$this->assertFalse($this->object->getAllowIpAsDomainPart());
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
	}

	/**
	 * @covers MailValidation::setCheckTopLevelDomain
	 * @covers MailValidation::gettCheckTopLevelDomain
	 */
	public function testSetCheckTopLevelDomain() {
		$this->assertTrue($this->object->setCheckTopLevelDomain(true)->getCheckTopLevelDomain(), true);
		$this->assertFalse($this->object->setCheckTopLevelDomain(false)->getCheckTopLevelDomain(), false);
	}

	public function testSetAllowIpAsDomainPart() {
		$this->assertTrue($this->object->setAllowIpAsDomainPart(true)->getAllowIpAsDomainPart(), true);
		$this->assertFalse($this->object->setAllowIpAsDomainPart(false)->getAllowIpAsDomainPart(), false);
	}
}
