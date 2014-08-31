<img src="http://virtphp.org/images/logo_lg.png" />

virtPHP is a tool for creating and managing multiple isolated PHP environments on a single machine. It's like Python's [virtualenv](http://virtualenv.org), but for PHP.

virtPHP creates isolated environments so that you may run any number of PHP development projects, all using different versions of PEAR packages and different PECL extensions. You may even specify a different version of PHP, if your system has various installations of PHP.

To install multiple versions of PHP, we suggest taking a look at the [phpenv](https://github.com/CHH/phpenv) and [php-build](https://github.com/CHH/php-build) projects or [phpbrew](https://github.com/c9s/phpbrew) and using virtPHP with them, to manage multiple virtual PHP environments.

**Note: virtPHP is currently only targeted to command line `php` (php-cli) for *nix based systems.**

[![Build Status](https://travis-ci.org/virtphp/virtphp.png?branch=master)](https://travis-ci.org/virtphp/virtphp)
[![Coverage Status](https://coveralls.io/repos/virtphp/virtphp/badge.png)](https://coveralls.io/r/virtphp/virtphp)


## Installation

Download the `virtphp.phar` file from the [latest release](https://github.com/virtphp/virtphp/releases) and place it in `/usr/local/bin` or wherever it's accessible from your `PATH`.

Optionally, you may clone this repository and [build the phar file yourself](#building-the-phar-file).


## Usage

virtPHP is a command-line tool. To get started, you'll probably want to check out what it can do. To do this, just execute it without any arguments, like this:

``` bash
user@host:~$ php virtphp.phar
```

If you have the phar file set executable (i.e. `chmod 755`), then you can execute it like this:

``` bash
user@host:~$ ./virtphp.phar
```

Or, if it's in your `PATH`, like this:

``` bash
user@host:~$ virtphp.phar
```

We recommend putting it in your `PATH` and aliasing it to `virtphp`, so that you can run it like this:

``` bash
user@host:~$ virtphp
```

For convenience, the following examples will assume you have simply downloaded `virtphp.phar` and have not placed it in your `PATH` or set it executable.

### Getting Started

To create a new virtPHP environment, use the `create` command:

``` bash
user@host:~$ php virtphp.phar create myenv
```

By default, this will create a new PHP environment in `myenv/`, using your system PHP as the base.

After creating the environment, you may activate it so that you now use the new environment in your shell:

``` bash
user@host:~$ source myenv/bin/activate
```

After activating your environment, you'll notice that your shell changes to include the name of your virtPHP environment, like this:

``` bash
(myenv) user@host:~$
```

And, when you run `which php`, your shell session reports it is now using PHP from your virtPHP environment:

``` bash
(myenv) user@host:~$ which php
/home/user/myenv/bin/php
```

Now, let's install a PECL extension and a PEAR package.

``` bash
(myenv) user@host:~$ pecl install mongo
(myenv) user@host:~$ pear config-set auto_discover 1
(myenv) user@host:~$ pear install pear.phpunit.de/PHPUnit
```

_(I'm not showing any of the console output here, in case you were wondering.)_

What's cool here is that you didn't have to use `sudo` to install these commands, and if you run `pear list -a`, you'll see both PHPUnit and pecl/mongo listed as being installed. Now, any project running in the current, activated virtPHP environment can make use of these packages.

To return your environment back to normal settings and discontinue using your virtPHP environment, simply use the `deactivate` command. It doesn't matter where you are when you run it—it's available to your entire virtPHP environment.

``` bash
(myenv) user@host:~$  deactivate
```

Now, depending on your base environment, when you run `pear list -a`, you won't see the PHPUnit or pecl/mongo packages that you just installed.

To start up your virtPHP environment again, just source the `activate` script for the virtPHP environment you want to use.

Altogether, that's pretty neat, huh?

So you've setup one or two or even eleven different environments. Keeping track of all of them in your head can lead to cluster headaches, right? So to list out all the environments you have installed, use the show command.

``` bash
user@host:~$ php virtphp.phar show
```

This will give you a nice list of all the environments you've created and the path to each.

``` bash
+--------+--------------------------------------+
| Name   | Path                                 |
+--------+--------------------------------------+
| mytest | /Users/virtPHP/work/project2/virtphp |
| myenv  | /Users/virtPHP/work/project1/virtphp |
+--------+--------------------------------------+
```

Because virtPHP creates physical folders and files for all of it's work, make sure you use the built in commands for destroying or cloning environments, otherwise things can get messy. However, if an environment does get out of sync you can perform a resync of a particular environment.

``` bash
user@host:~$ php virtphp.phar show --env=myenv --path=/Users/virtPHP/work/RealProject/virtphp
```

If you do another show, you will see the updated path in the list of your enviornments.

``` bash
+--------+-----------------------------------------+
| Name   | Path                                    |
+--------+-----------------------------------------+
| mytest | /Users/virtPHP/work/project2/virtphp    |
| myenv  | /Users/virtPHP/work/RealProject/virtphp |
+--------+-----------------------------------------+
```

### Under the Covers

When you create a new virtPHP environment, it creates a new directory and sets up a virtual environment for PHP within it. For example, the `myenv/` environment directory looks something like this:

```
myenv/
|-- bin/
|   |-- activate
|   |-- composer -> /home/user/myenv/bin/composer.phar*
|   |-- composer.phar*
|   |-- pear*
|   |-- peardev*
|   |-- pecl*
|   |-- php*
|   |-- php-config*
|   |-- phpize -> /usr/bin/phpize*
|   `-- phpunit*
|-- etc/
|   |-- pear.conf
|   `-- php.ini
|-- lib/
|   `-- php/
|       `-- mongo.so
`-- share/
    |-- pear/
    `-- php/
```

When you activate the environment, the `bin/` directory becomes a part of your `PATH`. When you install a PECL extension, the extension is placed in `lib/php/` (as you can see in this example, with `mongo.so`), and when you install a PEAR package, it is placed in `share/php/` (and console commands are placed in `bin/`, as you can see in this example with `phpunit`).

To be helpful, we have already installed [Composer](https://getcomposer.org) for you. When you activate a virtPHP environment, then you have access to the `composer` command.

### Advanced Concepts

Let's say you need to use the same host machine to develop multiple projects, all requiring different versions of PHP and different versions of PECL extensions or PEAR packages. virtPHP was made for this!

#### Specifying a PHP Build for virtPHP

If you have multiple builds of PHP on your system, you can tell virtPHP which one to use, when creating a new environment.

``` bash
user@host:~$ php virtphp.phar create --php-bin-dir="/home/user/.phpbrew/php/php-5.4.25/bin" project1-env
user@host:~$ php virtphp.phar create --php-bin-dir="/home/user/.phpbrew/php/php5.4.25/bin" project2-env
```

In this case, we have `project1-env` and `project2-env`, both of which use PHP 5.4.25. One of the projects, however uses the older pecl/mongo version 1.2 series, while the other project uses the pecl/mongo 1.4 series. virtPHP makes the use of both extensions possible on the same system. Here's how:

``` bash
user@host:~$ source project1-env/bin/activate # Activate project1
(project1-env) user@host:~$ pecl install mongo-1.2.12
(project1-env) user@host:~$ deactivate
user@host:~$ source project2-env/bin/activate # Activate project2
(project2-env) user@host:~$ pecl install mongo-1.4.5
(project2-env) user@host:~$ deactivate
```

Now, we are able to use version 1.2.12 of pecl/mongo, when working on project1-env, and we can use version 1.4.5 of pecl/mongo, when working on project2-env. We just need to activate the environment we are working on first.

In the same way, working with different versions of PHP for other projects is simple. For example, in `project3-env`, we are using PHP 5.5.

``` bash
user@host:~$ php virtphp.phar create --php-bin-dir="/home/user/.phpbrew/php/php-5.5.9/bin" project3-env
```

#### Using phpbrew to install multiple PHP versions

In the previous examples, we told virtPHP to use a specific build of PHP when creating new environments. To use virtPHP in this way, you'll need to install different builds of PHP. You can download the source, configure it, and build it on your own, or you may use [phpbrew](http://phpbrew.github.io/phpbrew/) to do this for you.

First, install phpbrew on your system, like this (follow any instructions these commands print to the screen):

``` bash
user@host:~$ curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
user@host:~$ chmod +x phpbrew
user@host:~$ sudo mv phpbrew /usr/bin/phpbrew
```

Then, you should init a bash script for your shell environment, which will place a `bashrc` file in the `~/.phpbrew` folder.

``` bash
user@host:~$ phpbrew init
```

Then source this file to your `.bashrc` or `.zshrc` file with this line:

``` bash
user@host:~$ source ~/.phpbrew/bashrc
```

Now, we can view and install a few versions of PHP, and control the variants that can be installed with it, with the following commands.

``` bash
user@host:~$ phpbrew known
Available stable versions:
  5.6 versions:    5.6.0
  5.5 versions:    5.5.16, 5.5.15, 5.5.14, 5.5.13, 5.5.12, 5.5.11, 5.5.10, 5.5.9
  5.4 versions:    5.4.32, 5.4.31, 5.4.30, 5.4.29, 5.4.28, 5.4.27, 5.4.26, 5.4.25
  5.3 versions:    5.3.28, 5.3.27, 5.3.26, 5.3.25, 5.3.24, 5.3.23, 5.3.22, 5.3.21

user@host:~$ phpbrew install 5.6.0 +default+debug+mysql
```

*This may take a while, so grab a few cups of coffee. phpbrew command shown with the `default`, `debug`, and `mysql` variants included.*

Variants can be installed individually, or in 'virtual variants'
``` bash
user@host:~$ phpbrew variants
Variants:
  all, apxs2, bcmath, bz2, calendar, cgi, cli, ctype, dba, debug, dom, embed,
  exif, fileinfo, filter, fpm, ftp, gcov, gd, gettext, hash, iconv, icu,
  imap, intl, ipc, ipv6, json, kerberos, mbregex, mbstring, mcrypt, mhash,
  mysql, openssl, pcntl, pcre, pdo, pgsql, phar, posix, readline, session,
  soap, sockets, sqlite, tidy, tokenizer, xml_all, xmlrpc, zip, zlib


Virtual variants:
  dbs:      sqlite, mysql, pgsql, pdo
  mb:       mbstring, mbregex
  neutral:
  default:  filter, dom, bcmath, ctype, mhash, fileinfo, pdo, posix, ipc,
            pcntl, bz2, zip, cli, json, mbstring, mbregex, calendar, sockets, readline,
            xml_all
```

After installing the spcified version of `phpbrew`, we can activate a specific version like this:

``` bash
user@host:~$ phpbrew use php-5.6.0
```

...switch between different versions:

``` bash
user@host:~$ phpbrew switch php-5.5.16
```

...or, return to the system version:

``` bash
user@host:~$ phpbrew off
```

So, why would we need virtPHP, if we can do this? virtPHP goes beyond phpbrew.

With virtPHP, you may install different PECL extensions, different PEAR packages, and manage separate `php.ini` configs for the same version and build of PHP. This way, projects you are developing that share the same PHP version but different configuration may be developed on the same system using different virtual PHP environments. virtPHP can work together with phpbrew to achieve this.


## Known Issues

* .pearrc not found issue
  If you get an error stating the script couldn't access the `.pearrc` file (or can't find it), you can either try changing the permissions on your `[USER_DIR]/.pearrc` file or remove it entirely. This issue seems to occur sporadically.


## Contributing

If you would like to help, take a look at the [list of issues](http://github.com/virtphp/virtphp/issues). [Fork the project](https://github.com/virtphp/virtphp/fork), create a feature branch, and send us a pull request.

To ensure a consistent code base, you should make sure the code follows the [coding standards](http://symfony.com/doc/master/contributing/code/standards.html), which we borrowed from Symfony.

### Running the Tests

You may use the provided Vagrantfile to start up a VM and run tests in a clean environment. You will need [VirtualBox](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/) installed on your system.

``` bash
user@host:~$ cd virtphp/
user@host:~$ vagrant up
user@host:~$ vagrant ssh
user@host:~$ cd /vagrant
user@host:~$ curl -sS https://getcomposer.org/installer | php
user@host:~$ php composer.phar install
user@host:~$ ./vendor/bin/phpunit
```

### Building the Phar File

virtPHP is distributed as an executable [phar](http://php.net/phar) file. The `bin/compile` script handles building this file. To build the phar file, change to the location of your virtPHP project clone and execute the `compile` script like this:

``` bash
user@host:~$ ./bin/compile
```

This should build a file named `virtphp.phar` in your current directory. You may move this file to wherever you like and use it for creating virtPHP environments.


## Requirements

PHP 5.3.3 or above.


## License

Copyright (c) 2013-2014 [Jordan Kasper](http://jordankasper.com), [Ben Ramsey](http://benramsey.com), [Jacques Woodcock](http://jacqueswoodcock.tumblr.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
