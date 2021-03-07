![](https://banners.beyondco.de/Hopper.png?theme=light&packageManager=composer+require&packageName=nedwors%2Fhopper+--dev&pattern=pieFactory&style=style_1&description=&md=1&showWatermark=1&fontSize=100px&images=database)

# Hopper

![Tests](https://github.com/nedwors/hopper/workflows/Tests/badge.svg)

Hop between databases with ease whilst developing locally on Laravel.

Imagine: A colleague asks you to check their PR out. But you're working on a new feature yourself and your local database is \*just\* right. You've got it tuned with the right models, the right data - losing it would just be an inconvenience too far...

So now imagine: A colleague asks you to check their PR out. You jump onto their branch, set up your database - migrate it, seed it, wipe it, whatever - and review their work. Then, you return to your feature and pick up where you left off - *with your database still intact*.

Enter Hopper. It's as simple as:
```bash
php artisan hop awesome_new_feature
```
Now, you're on a database called `awesome_new_feature`.

With Git, it's even simpler:
```bash
php artisan hop
```
Now, you're on a database for the current branch!

And you can always hop back to your default database with:
```bash
php artisan hop --d
```
Now, you're on the default database as configured by your Laravel settings.

## Installation

You can install the package via composer:

```bash
composer require nedwors/hopper --dev
```

## Setup

Hopper comes with a config file for you to publish to `config/hopper.php`:

```bash
a hop:publish
```
> You don't need to publish the config file to use Hopper, but it is recommended. But no pressure. Honest... See [configuration](#configuration) to see whether you want to for your project.

## Usage

Commands:
- [hop](#hop)
- [hop:current](#hopcurrent)
- [hop:delete](#hopdelete)

Configuration:
- [Default Git Branch](#default-git-branch)
- [Connections](#connections)
    - [Sqlite](#sqlite)
    - [MySql](#mysql)
    - [Adding Your Own](#adding-your-own)
- [Boot Checks](#boot-checks)
- [Post Creation Steps](#post-creation-steps)

### Commands
### hop
This is the core command when using Hopper. There are 3 ways it can be used:
- Use a database for the current Git branch
- Use a specific database
- Use the default database

#### Using a database for the current Git branch
This option is where Hopper really shines. Simply checkout a branch, hop, and you're on a new database. Checkout your previous branch, hop, and you're back to where you started.

To use Hopper this way, simply hop without arguments:
```bash
git checkout -b updates

php artisan hop
```
Now, when using your app, it will be connected to the `updates` database.

Your default Git branch aliases to your default database. So, imagine `main` is your default branch. When you run this:
```bash
git checkout main

php artisan hop
```
You won't move to the a database called `main`. Instead, you'll be moved to your default database - simple!

But don't worry, you're not forced to use the current Git branch. You can also specify a name at any point, or manually use the default branch.

#### Using a specific database
To use a specific database, simply pass the name of the database to use:
```bash
php artisan hop foobar
```
Now, when using your app, it will be connected to the `foobar` database.

#### Using the default database
To use the default database, pass the `--d` option to the command:
```bash
php artisan hop --d
```
Now, your app simply uses the default database as is set up in Laravel.

> Hopper steps aside when the default database is used; it doesn't touch your database connection. This is useful for team members who might not want to use Hopper themselves. They can rest assured that Hopper isn't interfering with their setup.

#### Post Creation

When it is the first time using a database, Hopper will create it ready to use. Likely, you'll want to migrate and setup up this database. Hopper provides a clean way to run Post Creation Steps - [see how you can set these up](#post-creation-steps).

### hop:current
See the database that you are currently using:
```bash
php artisan hop test

php artisan hop:current

// Currently using test
```

### hop:delete
Delete the given database:
```bash
php artisan hop:delete test

// Deleted test
```
When a database is deleted, you will be moved back to your default database.

### Configuration
As stated in [setup](#setup), you don't need to publish the hopper config file to use Hopper. The config file is as so:

```php
'default-branch' => env('HOPPER_DEFAULT_BRANCH', 'main'),

'connections' => [
    'sqlite' => [
        'driver' => Sqlite::class,
        'database-path' => 'hopper/'
    ],
    'mysql' => [
        'driver' => MySql::class,
        'database-prefix' => 'hopper_'
    ],
],

'boot-checks' => [
    Environment::class
],

'post-creation-steps' => [
    'migrate:fresh'
]
```
We'd encourage to checkout all the options described below to see if you want to publish it or not.


### Default Git Branch
You should define here the name of the default git branch in your project:

```php
...

'default-branch' => env('HOPPER_DEFAULT_BRANCH', 'main')

...
```
Now, every time you [hop](#hop) on this branch without arguments, your default database will be used automatically - rather than a database called `main`.

You'll see that there is a `HOPPER_DEFAULT_BRANCH` `.env` variable available too - this means you can alter this value without publishing the config file.

### Connections
Currently, Hopper has built in support for `sqlite` and `mysql` database connections. Hopper will automatically use whichever connection you are using.

These and future drivers are exposed and configured in the config file - which means you can add your own even if Hopper doesn't yet support it natively! See the supported connections in `hopper.php`:

```php
...

'connections' => [

    'sqlite' => [
        'driver' => Sqlite::class,
        'database-path' => 'hopper/'
    ],

    'mysql' => [
        'driver' => MySql::class,
        'database-prefix' => 'hopper_'
    ],

]

...
```

#### Sqlite
Hopper stores all Sqlite databases within a relative directory in the database directory of your application. You can configure the name of the directory in the config file:
```php
...

'connections' => [

    'sqlite' => [
        'driver' => Sqlite::class,
        'database-path' => 'hopper/'
    ],

]

...
```
So, as you might expect, all temporary databases created by Hopper will be stored in `database/hopper/`. So for example, running this command...
```bash
php artisan hop test
```
...will create a sqlite database at `database/hopper/test.sqlite`.
> The `hopper` directory will be created by Hopper if it doesn't already exist

> All database names passed to hopper when using Sqlite will be sanitized. Slashes will be automatically converted to dashes for the Sqlite connection. So for instance, `hop this/database` will create a database called `this-database.sqlite`

> It's probably worth adding your chosen directory to your `.gitignore` when using the `sqlite` connection!

#### MySql
Hopper creates all MySql databases on your configured MySql connection. All databases created will have a prefix applied to their name so you can easily identify them as needed. You can configure the prefix in the config file:
```php
...

'connections' => [

    'sqlite' => [
        'driver' => MySql::class,
        'database-prefix' => 'hopper_'
    ],

]

...
```
So for example, running this command...
```bash
php artisan hop test
```
...will create a MySql database called `hopper_test`.

> All database names passed to hopper when using MySql will be sanitized. Dashes will be automatically converted to underscores for the MySql connection. So for instance, `hop this-database` will create a database called `hopper_this_database`

#### Adding your own
Hopper makes it nice and easy to add your own connection for a database type this is supported by Laravel but not by Hopper.

To add your own connection, create a class that implements the `Connection` interface. You can refer to the existing connections to see how to build your new implementation. When you are ready to implement it, add it to the connections array:
```php
...

'connections' => [

    ...

    'newconnection' => [
        'driver' => NewConnection::class
    ],

    ...

]

...
```

### Boot Checks
Hopper exposes the checks it runs prior to wiring up your database connection. This way, you can alter the existing checks and/or add your own if needed. They are found in the `hopper.php` config file:

```php
...

'boot-checks' => [
    Environment::class
]

...
```
The included `Environment` check ensures the app environment is `local`. To add your own, ensure your class either implements the `BootCheck` interface or exposes a `check()` method which returns a `boolean`. Then, pop the class name in this array and you're all set.

### Post Creation Steps
It is super simple to configure the steps you want to run after creating a new database.

The steps can be found in the `hopper.php` config file:

```php
...

'post-creation-steps' => [
    'migrate:fresh --seed'
]

...
```
All strings included in this array must refer to an Artisan command in your app.

Closures can also be defined:

```php
...

'post-creation-steps' => [
    'migrate:fresh',
    fn() => app(SpecificDatabaseSeeder::class)->run()
]

...
```
> All steps are run in order of declaration, so ensure you `migrate` your database before any seeders!

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email nedwors@gmail.com instead of using the issue tracker.

## Credits

- [Sam Rowden](https://github.com/nedwors)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
