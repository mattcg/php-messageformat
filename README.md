# MessageFormat #

[![Build Status](https://travis-ci.org/karwana/php-messageformat.svg?branch=master)](https://travis-ci.org/karwana/php-messageformat)

Fast and reliable message formatting using INI language files and [MessageFormatter](http://www.php.net/manual/en/class.messageformatter.php).

Once language files are parsed, they're [serialized](http://www.php.net/manual/en/function.serialize.php) and cached using [Cache](https://github.com/karwana/php-cache).

## Why INI? ##

INI file parsing in PHP is much faster than YAML, although slightly slower but more readable than serialized PHP arrays. We think that's a good trade-off.

## Usage ##

The `MessageFormat` constructor accepts three arguments: the path to directory where your INI files are kept, the locale name and an optional third argument, which is a chained `MessageFormat` instance that will be used as a fallback for nonexistent keys.

### Using a single INI file with sections ###

```php

use Karwana\MessageFormat\MessageFormat;

$mf = new MessageFormat('/path/to/language/files/directory', 'en');

// Assume en.ini contains the following:
//
// [my_domain]
// my_key = "My mesage is \"{0}\"."
// my_other_key = "The colors of the rainbow."

// The following line will print 'My message is "Hello".' to output.
echo $mf->format('my_domain.my_key', array('Hello'));

```

### Chaining multiple instances ###

```php
$mf = new MessageFormat($ini_dir, 'en-gb', new MessageFormat($ini_dir, 'en'));

// Assume en-gb.ini contains the following:
//
// [my_domain]
// my_other_key = "The colours of the rainbow."

// The following line will print the British English message from en-gb.ini.
echo $mf->format('my_domain.my_other_key');

// The following line will print the fallback from en.ini.
echo $mf->format('my_domain.my_key', array('Yo'));
```

With chaining, only the messages which vary in between language or regional variants need to be specified in each variant file. This saves you having to keep track of and repeat changes across multiple files.

## License ##

See `LICENSE`.
