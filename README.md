# Shooting.ie

This repository contains the code and data for
[Shooting.ie](http://www.shooting.ie/). If you'd like to contribute to the
site, please fork this repo and send me a pull request.

## External Libraries

We use the following external libraries.

- [JQuery](https://github.com/jquery/jquery) - Hotlinked from Google's CDN
- [Klein](https://github.com/chriso/klein.php) - Git Submodule
- [Mustache](https://github.com/bobthecow/mustache.php) - Git Submodule
- [Twitter Bootstrap](https://github.com/twitter/bootstrap/) - Git Submodule

If you are replicating this site locally for development purposes, you don't
need to install anything special. Simply run `git submodule update --init` in
your checkout.

## Testing

Run the tests with `ant test`. The tests require the following packages:

- [PHPUnit](http://www.phpunit.de/)
- [PHP CodeSniffer](http://pear.php.net/PHP_CodeSniffer)
- [PHP CPD](https://github.com/sebastianbergmann/phpcpd)
- [PHP Mess Detector](http://phpmd.org/)

You can install the testing pre-requisites using PEAR:

    pear config-set auto_discover 1
    pear install pear.phpqatools.org/phpqatools pear.netpirates.net/phpDox
