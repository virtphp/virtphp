VirtPHP - Virtual PHP Environments
==================================

VirtPHP creates and manages virtual PHP environments.

Installation
------------

TODO


Usage
-----

__Basic Usage__

```
~$ virtphp install {env_name}
```

This will create a new virtual PHP environment in the {env_name} directory within the current working directory. For example, if you were to run:

```
~$ cd /home/darth
~$ virtphp install foobar
```

Then you would get a new directory: `/home/darth/foobar` which contains all of the files necessary for the virtual environment.

_NOTE: This does not activate the environment, but merely create the structure (see below)._


__Activate an Environment__

Once an environment has been created (see above), run the following command from within the environment directory to activate it

```
~$ source /path/to/your/environment/bin/activate
```

After running this command your shell will indicate you are in the new environment and `which php` will demonstrate your altered state:

```
(env_name) ~$ which php
/path/to/your/environment/bin/php
```


__Deactivate an Environment__

When you want to revert back to your original PHP environment you can simply run:

```
(env_name) ~$ deactivate
```

_Notes: You can run `deactivate` from anywhere, any time within the same shell session. That said, if your shell session is close, the virtual environment will automatically be deactivated._


__Destroying a Virtual Environment__

If you no longer want a virtual environment you can easily destroy it using:

```
~$ virtphp destroy /path/to/virtual/env
```

_Note: This action is not reversible!_


__Specifying a Different PHP binary__

When you want to set up a new virtual environment using a specific PHP binary, pass in the path to the `bin` folder where the php binary is located with the `--php-bin-dir` option:

```
~$ virtphp install {env_name} --php-bin-dir="/path/to/bin"
```

_Note: The directory you specify MUST contain a valid php binary!_


__Specify an Install Path__

To specify a direct install path (versus one realtive to your working directory), simply use the `--install-path` option:

```
~$ virtphp install {env_name} --install-path="/home/darth/deathstar"
```

Then you would get a new directory: `/home/darth/deathstar/{env_name}` which contains all of the files necessary for the virtual environment.


__Clone an Existing Environment__

If necessary, you can clone an existing virtual environment in order to alter either one independently. Simply run the following command:

```
~$ virtphp clone /path/to/existing/env {new_env_name}
```

You can specify an `--install-path` option if necessary (see above) otherwise the new environment will be installed in the current working directory.


__Need Help?__

```
~$ virtphp help [command]
```



Contributing
------------

All code contributions - including those of people having commit access -
must go through a pull request and approved by a core developer before being
merged. This is to ensure proper review of all the code.

Fork the project, create a feature branch, and send us a pull request.

To ensure a consistent code base, you should make sure the code follows
the [Coding Standards](http://symfony.com/doc/2.0/contributing/code/standards.html)
which we borrowed from Symfony.

If you would like to help take a look at the [list of issues](http://github.com/jwoodcock/virtphp/issues).


Requirements
------------

PHP 5.3.3 or above

Authors
-------

Jordan Kasper - http://github.com/jakerella
Jacques Woodcock - http://github.com/jwoodcock
Ben Ramsey - http://github.com/ramsey

License
-------

See the LICENSE file in the root for more information.
