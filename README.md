# mailcheck-php

The PHP library that suggests a right domain when your users misspell it in an email address. See the original at https://github.com/mailcheck/mailcheck.

When your user types in "user@hotnail.con", Mailcheck will suggest "user@hotmail.com".

Mailcheck will offer up suggestions for top level domains too, and suggest ".com" when a user types in "user@hotmail.cmo".

mailcheck-php is part of the [Mailcheck family](http://getmailcheck.org), and we're always on the lookout for more ports and adaptions. Get in touch!

## Installation

> **Requires [PHP 8.0+](https://php.net/releases/)**

You can install the package via composer:

```bash
composer require masroore/mailcheck
```

## Usage

```php
> $mailcheck = new Kaiju\Mailcheck\Mailcheck();
> print_r($mailcheck->suggest("user@hotma.com"));
# => {
#   :account      =>"user",
#   :domain       =>"hotmail.com",
#   :fullAddress  =>"user@hotmail.com"
# }
```

Returns `null` if no suggestion:
```php
> $mailcheck->suggest("user@hotmail.com")
# => null
```

Pass in a custom list of domains and TLDs:
```php
$mailcheck->setDomains(["gmail.com", "hotmail.com", "aol.com"]);
$mailcheck->setTopLevelDomains(["com", "net", "org"]);
```

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

Maintainers
-------

- [Dr. Masroor Ehsan](https://github.com/masroore), Author.

License
-------

Licensed under the MIT License.
