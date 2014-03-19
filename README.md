<img src="http://virtphp.org/images/logo_lg.png" />

virtPHP is a tool for creating and managing multiple isolated PHP environments on a single machine. It's like Python's [virtualenv](http://virtualenv.org), but for PHP.

virtPHP creates isolated environments so that you may run any number of PHP development projects, all using different versions of PEAR packages and different PECL extensions. You may even specify a different version of PHP, if your system has various installations of PHP.

To install multiple versions of PHP, we suggest taking a look at the [phpenv](https://github.com/CHH/phpenv) and [php-build](https://github.com/CHH/php-build) projects and using virtPHP with them, to manage multiple virtual PHP environments.

**Note: virtPHP is currently only targeted to command line `php` (php-cli) for *nix based systems.**

[![Build Status](https://travis-ci.org/virtphp/virtphp.png?branch=master)](https://travis-ci.org/virtphp/virtphp)
[![Coverage Status](https://coveralls.io/repos/virtphp/virtphp/badge.png)](https://coveralls.io/r/virtphp/virtphp)


## Installation

Download the `virtphp.phar` file from the [latest release](https://github.com/virtphp/virtphp/releases) and place it in `/usr/local/bin` or wherever it's accessible from your `PATH`.

Optionally, you may clone this repository and [build the phar file yourself](#building-the-phar-file).


## Usage

virtPHP is a command-line tool. To get started, you'll probably want to check out what it can do. To do this, just execute it without any arguments, like this:

``` bash
php virtphp.phar
```

If you have the phar file set executable (i.e. `chmod 755`), then you can execute it like this:

``` bash
./virtphp.phar
```

Or, if it's in your `PATH`, like this:

``` bash
virtphp.phar
```

We recommend putting it in your `PATH` and aliasing it to `virtphp`, so that you can run it like this:

``` bash
virtphp
```

For convenience, the following examples will assume you have simply downloaded `virtphp.phar` and have not placed it in your `PATH` or set it executable.

### Getting Started

To create a new virtPHP environment, use the `create` command:

``` bash
php virtphp.phar create myenv
```

By default, this will create a new PHP environment in `myenv/`, using your system PHP as the base.

After creating the environment, you may activate it so that you now use the new environment in your shell:

``` bash
source myenv/bin/activate
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
deactivate
```

Now, depending on your base environment, when you run `pear list -a`, you won't see the PHPUnit or pecl/mongo packages that you just installed.

To start up your virtPHP environment again, just source the `activate` script for the virtPHP environment you want to use.

Altogether, that's pretty neat, huh?

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
php virtphp.phar create --php-bin-dir="/home/user/.phpenv/versions/5.4.25/bin" project1-env
php virtphp.phar create --php-bin-dir="/home/user/.phpenv/versions/5.4.25/bin" project2-env
```

In this case, we have `project1-env` and `project2-env`, both of which use PHP 5.4.25. One of the projects, however uses the older pecl/mongo version 1.2 series, while the other project uses the pecl/mongo 1.4 series. virtPHP makes the use of both extensions possible on the same system. Here's how:

``` bash
source project1-env/bin/activate # Activate project1
pecl install mongo-1.2.12
deactivate
source project2-env/bin/activate # Activate project2
pecl install mongo-1.4.5
deactivate
```

Now, we are able to use version 1.2.12 of pecl/mongo, when working on project1-env, and we can use version 1.4.5 of pecl/mongo, when working on project2-env. We just need to activate the environment we are working on first.

In the same way, working with different versions of PHP for other projects is simple. For example, in `project3-env`, we are using PHP 5.5.

``` bash
php virtphp.phar create --php-bin-dir="/home/user/.phpenv/versions/5.5.9/bin" project3-env
```

#### Using phpenv and php-build

In the previous examples, we told virtPHP to use a specific build of PHP when creating new environments. To use virtPHP in this way, you'll need to install different builds of PHP. You can download the source, configure it, and build it on your own, or you may use [phpenv](https://github.com/CHH/phpenv) and [php-build](https://github.com/CHH/php-build) to do this for you.

First, install phpenv and php-build on your system, like this (follow any instructions these commands print to the screen):

``` bash
git clone https://github.com/CHH/phpenv.git
./phpenv/bin/phpenv-install.sh
git clone https://github.com/CHH/php-build.git
sudo ./php-build/install.sh
sudo php-build --definitions
```

That last command should have printed out a list of PHP versions you can build with php-build. Now, we can build a few versions of PHP. This may take a while, so grab a few cups of coffee.

``` bash
php-build -i development 5.3.28 $HOME/.phpenv/versions/5.3.28
php-build -i development 5.4.25 $HOME/.phpenv/versions/5.4.25
php-build -i development 5.5.9 $HOME/.phpenv/versions/5.5.9
php-build -i development 5.6.0alpha2 $HOME/.phpenv/versions/5.6.0alpha2
phpenv rehash # This makes phpenv aware of the versions we've built
phpenv versions # Prints out the builds phpenv knows about
```

_Please note: You may need to install the following packages. These are the package names for Debian-based systems. Your system may know them by different names._

```
autoconf
bison
build-essential
curl
flex
libcurl3-openssl-dev
libfreetype6-dev
libjpeg62-dev
libmcrypt-dev
libpng-dev
libtidy-dev
libxml2-dev
libxslt-dev
re2c
zlib1g-dev
```

Using `phpenv`, we can change the version of PHP we're currently using, like this:

``` bash
phpenv global 5.3.28
```

So, why would we need virtPHP, if we can do this? virtPHP goes beyond phpenv.

With virtPHP, you may install different PECL extensions, different PEAR packages, and manage separate `php.ini` configs for the same version and build of PHP. This way, projects you are developing that share the same PHP version but different configuration may be developed on the same system using different virtual PHP environments. virtPHP can work together with phpenv to achieve this.


## Known Issues

* .pearrc issue  
  > If you get an error stating the script couldn't access the `.pearrc` file (or can't find it), you can either try changing the permissions on your `[USER_DIR]/.pearrc` file or remove it entirely. This issue seems to occur sporadically.


## Contributing

If you would like to help, take a look at the [list of issues](http://github.com/virtphp/virtphp/issues). [Fork the project](https://github.com/virtphp/virtphp/fork), create a feature branch, and send us a pull request.

To ensure a consistent code base, you should make sure the code follows the [coding standards](http://symfony.com/doc/master/contributing/code/standards.html), which we borrowed from Symfony.

### Running the Tests

You may use the provided Vagrantfile to start up a VM and run tests in a clean environment. You will need [VirtualBox](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/) installed on your system.

``` bash
cd virtphp/
vagrant up
vagrant ssh
cd /vagrant
curl -sS https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

### Building the Phar File

virtPHP is distributed as an executable [phar](http://php.net/phar) file. The `bin/compile` script handles building this file. To build the phar file, change to the location of your virtPHP project clone and execute the `compile` script like this:

``` bash
./bin/compile
```

This should build a file named `virtphp.phar` in your current directory. You may move this file to wherever you like and use it for creating virtPHP environments.


## Requirements

PHP 5.3.3 or above.


## License

Copyright (c) 2013-2014 [Jordan Kasper](http://jordankasper.com), [Ben Ramsey](http://benramsey.com), [Jacques Woodcock](http://jacqueswoodcock.tumblr.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
