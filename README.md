# Laravel DDD
Domain Driven Development domains generator

`php artisan make:domain User --table=users`

## Installation:
Require this package with composer using the following command:

```sh
$ composer require oleglfed/laravel-ddd
```

Go to your `config/app.php` and add the service provider:

`\oleglfed\LaravelDDD\LaravelDddServiceProvider::class`

## Usage


```sh
$ php artisan make:domain User --table=users
```

To generate domain, use the `make:domain` artisan command. This command will create in ths Domains and Infrastructures directories inside app/ folder domain classes.

E.g. for User will be created classes

`UserEloquent`

`UserRepository`

`EloquentUserRepository`

`UserService`

And contracts for this classes

### Available command options:

Option | Description
--------- | -------
`table` | Based on Table will be created Eloquent, getters and setters
`directory` | By default directory name takes from domain name. To override it --directory might be set
`domain-path` | By default domain directory is app/Domains. To override it --domain-path might be set
`infrastructure-path` | By default infrastructure directory is app/Infrastructures. To override it --infrastructure-path might be set

### License

The Laravel DDD Generator is free software licensed under the MIT license.