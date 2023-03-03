# Alpine Packages Finder

This project was built to query the [Alpine Linux Packages page](https://pkgs.alpinelinux.org/packages), 
to find if the name of a package, widely used while writing a Dockerfile, is correct.

## Requirements

- PHP 8.1 or greater
- Composer 2

## Instalation

After cloning this repository, run `composer install` to install the project's dependencies.

## Usage

The most basic usage is to run `php bin/console --name "package-name"`.

This project can also read from a file or directory containing files formatted as below:

```text
php82-bcmath
php82-pecl-mongodb
php82-gmp
php82-posix
php82-ctype
php82-common
php82-fpm
php82-pdo
php82-opcache
php82-phar
php82-iconv
php82-session
php82-sockets
php82-curl
php82-sodium
php82-soap
php82-shmop
php82-openssl
php82-mbstring
php82-tokenizer
php82-fileinfo
php82-xml
php82-xmlwriter
php82-simplexml
```

It's just a plain text file with a package name per line.

Assuming that this file is on your home directory, in a folder called `packages`, you can run the finder like this:

```shell
php bin/console --path ~/packages/file.txt
```

Or symply:

```shell
php bin/console --path ~/packages
```

Following the Alpine Packages' filters, there's other flags available:

- arch
- repository
- branch
- maintainer

Also, you can define the number of concurrent requests, with the `--concurrency` flag. Example:

```shell
php bin/console --path ~/packages --concurrency 30
```
