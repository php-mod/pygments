Pygments for PHP
================

PHP Server side syntax highlighter based on [Pygments](http://pygments.org/ "") highlighter software.

## Installation:
To use this plugin you need pygments in your server:

```
sudo apt-get python-pygments
```

That's all. Now you can download the plugin via Composer or as independent library and use it.

## Usage

```php

$code = file_get_contents("test.js");
echo Pygmentize::format($code, "js");
```
##Options
Pygments::render($code, $language, $style, $linenumbers)
* `$code`: the source code to highlight
* `$language`: Language type. see section "Languages and filetypes supported" in this doc.
