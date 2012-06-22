Gisele - Google CLI Search
=====================================

Gisele is simple tool which allows you to make a Google search in CLI

Installation
--------------------

1. Download the [`gisele.phar`](https://github.com/borisguery/Gisele/raw/master/build/gisele.phar) executable
2. Run Gisele: `php gisele.phar`

Installation from Source
------------------------

1. Run `git clone https://github.com/borisguery/Gisele.git`
2. Run Composer to get the dependencies: `composer install` (see Composer [documentation](http://getcomposer.org/doc/))

You can now run Gisele by executing the `bin/gisele` script: `php /path/to/gisele/bin/gisele`

Usage
-----

There are actually three search commands available.

### web
`php gisele.phar web "boris guery"`

### image
`php gisele.phar image "php logo"`

### news
`php gisele.phar news "php"`


All commands have the following options:

``` sh
 --throttle (-t)
```
 The number of seconds between each requests, can be either an integer, or a range like 1,3 (default: '5,10')`
 ``` sh
 --interactive (-i)
 ```
 Ask confirmation before fetching next page
 ``` sh
 --max-result (-m)
 ```
 The maximum of result to fetch
 ``` sh
 --format (-f)
 ```
 Custom format to render the results, depending on the command, the following placeholder are available: `{counter}`, `{title}`, `{link}`, `{from}`, `{ago}` (the last two are only available for the `news` command)
 ``` sh
 --lucky (-l)
 ```
 Are you lucky? Stop at the first result

Contributing
------------

If you have some time to spare on an useless project and would like to help take a look at the [list of issues](http://github.com/borisguery/gisele/issues).

Requirements
------------

* PHP 5.3+
* Internet connection

Authors
-------

Boris Guéry - <guery.b@gmail.com> - <http://twitter.com/borisguery> - <http://borisguery.com>

License
-------

Gisele is licensed under the WTFPL License - see the LICENSE file for details

About
-----

This tool has mostly been written as a proof-of-concept while experimenting with Symfony2, Goutte, Composer and PHAR creation.

Most part of this application is heavily inspired by Composer and Symfony2 source code.

Legal Notes
-----------

Using this tool may be an infringement of the Google's terms of use (well, this may be unclear in some countries...), use at your own risk.
