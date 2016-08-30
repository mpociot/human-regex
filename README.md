# HumanRegex

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mpociot/human-regex.svg?style=flat-square)](https://packagist.org/packages/mpociot/human-regex)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/mpociot/human-regex/master.svg?style=flat-square)](https://travis-ci.org/mpociot/human-regex)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/bd7f1aee-d735-4ee4-af5a-b40b905b5ab8.svg?style=flat-square)](https://insight.sensiolabs.com/projects/bd7f1aee-d735-4ee4-af5a-b40b905b5ab8)
[![Quality Score](https://img.shields.io/scrutinizer/g/mpociot/human-regex.svg?style=flat-square)](https://scrutinizer-ci.com/g/mpociot/human-regex)
[![Total Downloads](https://img.shields.io/packagist/dt/mpociot/human-regex.svg?style=flat-square)](https://packagist.org/packages/mpociot/human-regex)

## Regular expressions for human beings, not machines

## Installation

You can install the package via composer:

``` bash
composer require mpociot/human-regex
```

## Usage

``` php
$regex = HumanRegex::create()
    ->alphanumerics()
    ->then('-')
    ->digits()->exactly(4)
    ->then('-')
    ->digits()->exactly(2)
    ->then('-')
    ->digits()->exactly(2)
    ->then('.')
    ->thenEither('mov')->or('mp4');
    
$regex->matches('foobar-2016-08-29.mp4');
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email m.pociot@gmai.com instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
