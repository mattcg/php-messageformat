# MessageFormat #

Fast and reliable message formatting using INI language files and [MessageFormatter](http://www.php.net/manual/en/class.messageformatter.php).

Once language files are parsed, they're [serialized](http://www.php.net/manual/en/function.serialize.php) and cached using [Cache](https://github.com/karwana/php-cache).

## Why INI? ##

INI file parsing in PHP is much faster than YAML, although slightly slower but more readable than serialized PHP arrays. We think that's a good trade-off.

## Usage ##

```php

use Karwana\MessageFormat\MessageFormat;

$locale = 'en';
$mf = new MessageFormat('/path/to/language/files/directory', $locale);

// Assuming your en.ini file contains the following lines:
//
// [my_domain]
// my_key = "My mesage is \"{0}\"."
//
// The following line will print 'My message is "Hello".' to output.
echo $mf->format('my_domain.my_key', array('Hello'));

```
