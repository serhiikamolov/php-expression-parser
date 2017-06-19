# PHP Expression Parser

### Installation 

$ composer install

### Run in CLI mode

$ php parser.php "200+12*((1/8)+1)-19"


### Run under built-in web server

**Startting the web server**

$ php -S localhost:8181

**Run in any browser**

http://localhost:8181/parser.php?expr=200+12*((1/8)+1)-19


### Requirements

php 7.0 and higher


### How to run tests

$ ./vendor/bin/phpunit tests

