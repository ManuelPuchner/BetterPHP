# BetterPHP

## Installation
### Requirements
- docker
- php 8.0
### download ``betterphp.sh``
````shell
$ curl -O https://raw.githubusercontent.com/ManuelPuchner/BetterPHP/main/install-betterphp.sh && chmod +x install-betterphp.sh
````

## How to use
### Create the dev environment
- creates the following containers
  - web (apache server http://localhost:8080)
  - postgres (database)
  - pgadmin (database admin tool http://localhost:8090)
````shell
$ docker compose -f docker-compose-dev-environment.yaml up [-d]
````

### Create create-script
````shell
$ php betterphp/cli/generateTables.php 
````

### Generate the api
- by running the command you will generate the api in the `dist` folder
````shell
$ php betterphp/cli/index.php 
````

### Generate create table statements
- by running the command you will generate the create table statements in the `dist` folder
````shell
$ php betterphp/cli/generateTables.php 
````

### Create a .env file
- in the `src` folder
- see `.env.example`
- set the database credentials

## How to write the api
### controller
- create a model in the `src/model` folder
   - the model must extend the 'Entity' class
     ```php
        use betterphp\utils\Entity;
        require_once dirname(__DIR__) . '/../betterphp/utils/Entity.php';
      ``
   - the model already includes the id column
   - for the automatic creation of the `create.sql` file
     - write table constraints like this
        ```php
         /**
          * @TABLE_CONSTRAINT CONSTRAINT portfolio_pk PRIMARY KEY (id)
          */
         class Currency extends Entity
         {
        ```
     - write column constraints and the sql data types like this
        ```php
        /** @SQL bigserial NOT NULL PRIMARY KEY*/
        protected int $id;
        ```

- create a controller in the `src/controller` folder
   - the controller must extend the `Controller` class
     ```php
        use betterphp\utils\Controller;
        require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';
      ```
   - by throwing an exception you can send a http error code to the client
     ```php
        # example
        throw new Exception('Not found', 404);
        # or
        throw new Exception('Not found', HttpErrorCodes::NOT_FOUND);
      ```

- create a service to write api endpoints
   - multiple routes can be defined for one service
   - the controller instance must be defined in the function
     ```php
        Route::get('/test', function () {
          $data = CurrencyController::getInstance()->getCurrencies();
          return Response::ok('Hello World', $data);
        });
     ```