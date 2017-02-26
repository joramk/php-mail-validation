# php-mail-validation #

Validating e-mail addresses is a complicated procedure. This helper class is easy tu use and validates addresses in several ways.

## Checks implemented ##
 * Check for valid top level domain
 * Check for length and formatting
 * Check for existing and valid MX entry

## Usage example ##
``` $result = new Mailvalidation('joe@example.com')->validate(); ```

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
