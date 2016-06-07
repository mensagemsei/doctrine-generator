# Generator to Doctrine ORM 2

A PHP package customized to generate entity and repository classes of a project that uses Doctrine ORM in a command-line interface.

## Require 

Require the package Doctrine in your project installed with Composer.

## Usage

Before, you need create a PHP file named *generator.php* with the following code:

```php
require_once 'generator/bootstrap.php';

// TODO: Add an instance of the EntityManager and assigns in a variable named $em 

Generator\Application::run($em);
```

To view a list of all available commands, you may use the list command:

```
php generator.php list
```

To view all arguments and options, you may use the help command:

```
php generator.php doctrine:generate --help
```