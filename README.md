dpb587/opengrok-cli
===================

Command line interface for getting results from an OpenGrok server.

Setup
-----

    $ git clone git://github.com/dpb587/opengrok-cli.git
    $ composer.phar install


Usage
-----

Use `--help` to see all available options.

    $ ./bin/opengrok-cli --server=http://lxr.php.net --project=PHP_5_4 oci_internal_debug
    /ext/oci8/oci8.c:777: PHP_FUNCTION(oci_internal_debug);
    /ext/oci8/oci8.c:862: 	PHP_FE(oci_internal_debug,			arginfo_oci_internal_debug)
    /ext/oci8/oci8.c:932: 	PHP_FALIAS(ociinternaldebug,	oci_internal_debug,		arginfo_oci_internal_debug)
    /ext/oci8/oci8_interface.c:1307: /* {{ "{{{" }} proto void oci_internal_debug(int onoff)
    /ext/oci8/oci8_interface.c:1309: PHP_FUNCTION(oci_internal_debug)

Additionally, the following environment variables can be used:

 * `OPENGROK_SERVER` (instead of `--server`)
 * `OPENGROK_PROJECT` (instead of `--project`)


PHAR
----

To create a [PHAR](http://us.php.net/manual/en/book.phar.php), you'll need to run `composer.phar install --dev`. Then
you may use `./bin/compile` which will generate `opengrok-cli.phar` in the current directory.

If the `OPENGROK_SERVER` or `OPENGROK_PROJECT` environment variables are defined, they will be used as defaults in the
compiled version. For example:

    $ export OPENGROK_SERVER=http://lxr.php.net
    $ export OPENGROK_PROJECT=PHP_5_4
    $ ./bin/compile
    $ unset OPENGROK_SERVER OPENGROK_PROJECT
    $ php opengrok-cli.phar oci_internal_debug
    /ext/oci8/oci8.c:777: PHP_FUNCTION(oci_internal_debug);
    /ext/oci8/oci8.c:862: 	PHP_FE(oci_internal_debug,			arginfo_oci_internal_debug)
    /ext/oci8/oci8.c:932: 	PHP_FALIAS(ociinternaldebug,	oci_internal_debug,		arginfo_oci_internal_debug)
    /ext/oci8/oci8_interface.c:1307: /* {{ "{{{" }} proto void oci_internal_debug(int onoff)
    /ext/oci8/oci8_interface.c:1309: PHP_FUNCTION(oci_internal_debug)
