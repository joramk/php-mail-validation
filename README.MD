# php-mail-validation #

Validating e-mail addresses is a complicated procedure. This helper class is easy tu use and validates addresses in several ways.

## Checks implemented ##
 * Check for valid top level domain
 * Check for length and formatting
 * Check for existing and valid MX entry

## Current release ##
Get the current version from [here](https://github.com/joramk/php-mail-validation/releases).

## Usage examples ##
```
<?php
require_once('MailValidation.php');

$result = new MailValidation('joe@example.com')->validate();
$result = new MailValidation()->setEmailAddress('joe@example.com')->validate();
$result = new MailValidation()->validate('joe@example.com');
```

## Configurable parameters ##
```
$mailvalidation->setCheckDNS();
$mailvalidation->setCheckControlChars();
$mailvalidation->setCheckLength();
$mailvalidation->setCheckTopLevelDomain();
$mailvalidation->setAllowIpAsDomainPart();

$mailvalidation->getCheckDNS();
$mailvalidation->getCheckControlChars();
$mailvalidation->getCheckLength();
$mailvalidation->getCheckTopLevelDomain();
$mailvalidation->getAllowIpAsDomainPart();
```

## Update tld file ##
The file `MailValidation.tld` must be writable and reside in the same directory as the `MailValidation.php` class file.

``` new Mailvalidation()->DownloadTopLevelDomains(); ```
