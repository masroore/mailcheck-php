# mailcheck-php

The PHP library that suggests a right domain when your users misspell it in an email address. See the original at https://github.com/mailcheck/mailcheck.

When your user types in "user@gmil.con", Mailcheck will suggest "user@gmail.com".

Mailcheck will offer up suggestions for second and top level domains too. For example, when a user types in "user@hotmail.cmo", "hotmail.com" will be suggested. Similarly, if only the second level domain is misspelled, it will be corrected independently of the top level domain.

## Installation

> **Requires [PHP 8.1+](https://php.net/releases/)**

You can install the package via composer:

```bash
composer require masroore/mailcheck
```

## Usage

```php
> $mailcheck = new Kaiju\Mailcheck\Mailcheck();
> print_r($mailcheck->suggest("user@gmil.con"));

# Kaiju\Mailcheck\EmailSuggestion Object
# (
#     [fullAddress] => user@gmail.com
#     [originalAddress] => user@gmil.con
#     [account] => user
#     [domain] => gmail.com
# )
```

Returns `null` if no suggestion:

```php
> $mailcheck->suggest("user@hotmail.com")
# => null
```

Domains
-------

Mailcheck has inbuilt defaults if the `domains`, `secondLevelDomains` or `topLevelDomains` options aren't provided. We still recommend supplying your own domains based on the distribution of your users.

#### Adding your own Domains ####

You can replace Mailcheck's default domain/TLD suggestions by supplying replacements:

```php
$mailcheck->setDomains(['customdomain.com', 'anotherdomain.net']); // replaces existing domains
$mailcheck->setSecondLevelDomains(['domain', 'yetanotherdomain']); // replaces existing SLDs
$mailcheck->setTopLevelDomains(['com.au', 'ru']);  // replaces existing TLDs
```

## Contributing

Let's make Mailcheck awesome. We're on the lookout for maintainers and [contributors](https://github.com/masroore/mailcheck/contributors).

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
