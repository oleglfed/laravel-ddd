# Laravel DDD
### Domain Driven Development domains generator. 


![image](https://img.shields.io/packagist/v/oleglfed/laravel-ddd.svg?style=flat)
![image](https://img.shields.io/packagist/l/oleglfed/laravel-ddd.svg?style=flat)
[![Build Status](https://travis-ci.org/oleglfed/laravel-ddd.svg?branch=master)](https://travis-ci.org/oleglfed/laravel-ddd.svg?branch=master)
[![StyleCI](https://styleci.io/repos/91183556/shield?branch=master)](https://styleci.io/repos/91183556)

This package is made to generate Domains, based on DB table. 
Package get all table fields and creates domain. With Domain creates repository, service and Infrastructure. Also the Package automatically binds generated classes to your app, so you can easily use DI or make Service by contract `$service = app(UserServiceInterface::class);`  

`php artisan make:domain User --table=users`

## Installation:
Require this package with composer using the following command:

```sh
$ composer require oleglfed/laravel-ddd
```

Go to your `config/app.php` and add the service provider:

`\oleglfed\LaravelDDD\LaravelDddServiceProvider::class`

## Usage
This package creates `app/Domain` and `app/Infrastructure` directories. So at first package should be able to create these two directories. Afterwards, you can revoke writable access from `app` directory.

This package requires writable permissions to config/domains directory. 
Before use, create `config/domains` directory with writable permissions or allow the package to write into `config` directory. It is necessary for writing domains binding. Afterwards, these domains will be bound to your app by LaravelDddServiceProvider

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



### Advanced usage

This package contains a few useful methods to work with services and repositories.


```
    public function index(UserServiceInterface $service)
    {
        $service->all(); //Shows all records
        
        $service->get(1); //Shows record with ID: 1
        
        $servcie->findWhere(['first_name' => 'Oleg']); //Returns all records with provided where
        
        $servcie->deleteWhere(['first_name' => 'Oleg']); //Deletes all records with provided where
        
        $servcie->firstOrCreate(['email' => 'oleg.fedoliak@gmail.com']); //Returns a record with provided where, or creates it
        
        $servcie->firstOrNull(['email' => 'oleg.fedoliak@gmail.com']); //Returns a record with provided where, or null
       
        $servcie->lists(20, 10, ['first_name', 'email']); //Returns paginated list with `per page`, `offset`, `columns` (all by default) 
        
        $servcie->count(); //Returns count of records 
        
        //Create
        $user = $service->newInstance();
        $user->setFirstName('Oleg');
        $user->setEmail('oleg.fedoliak@gmail.com');
        
        $user = $servcie->create($user);
        
        //Update
        $user->setFirstName('John');
        $user = $servcie->update($user);
        
        //Save. There is a method which will check if record is exists, and if so will update it. Otherwise create it.
        $user = $servcie->save($user);

        //Delete
        $isDeleted = $servcie->delete($user);
    }
```

If your routes use `resource` you can explicitly bind Domains
Open `Providers\RouteServiceProvider` and add:

```
    \Route::model('user', App\Domains\User\UserEloquent::class);
```
Then you are able to use:
```
 public function update(Request $request, UserInterface $user, UserServiceInterface $service)
 {
    dd($user); <-- will be dumped User Domain
 }        

```

### License

The Laravel DDD Generator is free software licensed under the MIT license.