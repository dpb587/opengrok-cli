dpb587/opengrok-cli
===================

Talks to an OpenGrok server and outputs the results in a format similar to `grep`:

    $ ./bin/opengrok-cli --server=http://lxr.php.net --project=PHP_5_4 oci_internal_debug
    /ext/oci8/oci8.c:777: PHP_FUNCTION(oci_internal_debug);
    /ext/oci8/oci8.c:862: 	PHP_FE(oci_internal_debug,			arginfo_oci_internal_debug)
    /ext/oci8/oci8.c:932: 	PHP_FALIAS(ociinternaldebug,	oci_internal_debug,		arginfo_oci_internal_debug)
    /ext/oci8/oci8_interface.c:1307: /* {{ "{{{" }} proto void oci_internal_debug(int onoff)
    /ext/oci8/oci8_interface.c:1309: PHP_FUNCTION(oci_internal_debug)

Use `--help` to see all available options. Additionally, the following environment variables can be used:

    `OPENGROK_SERVER` - instead of `--server`
    `OPENGROK_PROJECT` - instead of `--project` (optionally comma-separated)

Create a [PHAR](http://us.php.net/manual/en/book.phar.php) by using `./bin/compile`. Optionally export `OPENGROK_SERVER`
to define the default OpenGrok server when the PHAR is run.
