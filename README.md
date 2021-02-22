# Hopper

![Tests](https://github.com/nedwors/hopper/workflows/Tests/badge.svg)

Swap - Hop - between databases with ease whilst developing locally.

Ever needed to check out a colleague's PR quickly, but you've got your db set up just as you need it for the feature you're working on? Ever wanted to quickly try some new migrations/seeders, but you don't want to lose your current db?

Enter Hopper... It's as simple as:
```bash
php artisan hop test
```
Now, you're on a database called `test`.

And with Git integration, it can be even simpler:
```bash
php artisan hop
```
Now, you're on a database for the current branch!

## Installation

You can install the package via composer:

```bash
composer require nedwors/hopper
```

## Setup

Hopper comes with a config file for you to publish to `config/hopper.php`:

```bash
a hop:publish
```
> You don't need to publish the config file to use Hopper, but it's recommended

## Usage

Commands:
- [hop](#hop)
- [hop:current](#hop:current)
- [hop:delete](#hop:delete)

Configuration:
- [Post Creation Steps](#post-creation-steps)

### Commands
### hop
This is the core command when using Hopper. There are 3 ways to interact with this command:
- Use a database for the current Git branch
- Use a specific database
- Use the default database

#### Using a database for the current Git branch
This option is where Hopper really shines. Simply checkout a new branch, hop, and you're on a new database. Checkout your previous branch, hop, and you're back to where you started.

To use Hopper this way, simply hop without arguments:
```bash
git checkout -b new-feature

php artisan hop
```
Now, when using your app, it will be connected to the `new-feature` database.

And to really ease the use of Hopper, your default Git branch aliases to your default database. So, if `main` is your default branch when you run this:
```bash
git checkout main
php artisan hop
```
You'll be moved to your default database - simple!

But don't worry, you're not forced to use the current Git branch. You can also specify a name at any point, or manually use the default branch.

#### Using a specific database
To use a specific database, simply pass the name of the database to use:
```bash
php artisan hop foobar
```
Now, when using your app, it will be connected to the `foobar` database.

> Just pass the name of the database, not any extensions etc

#### Using the default database
To use the default database, pass the `--d` option to the command:
```bash
php artisan hop --d
```
Now, your app simply uses the default database as is set up in Laravel.

Hopper simply steps aside when the default database is used; it doesn't touch your database connection. This is useful for team members who might not want to use Hopper themselves. They can rest assured that Hopper isn't intefering with their setup.

#### Post Creation

When it is the first time hopping to a database, Hopper will create it ready to use. Likely, you'll want to migrate and setup up this database. Hopper provides a clean way to run Post Creation Steps - see how you can set this up.

### Testing

``` bash
composer test
```

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
