# BetterPHP

## Requirements
- docker
- php 8.0
## Installation
### Download `install-betterphp.sh` and run
````shell
$ curl -s https://raw.githubusercontent.com/ManuelPuchner/BetterPHP/main/install-betterphp.sh | bash 
````

## How to use
### Start dev environment
````shell
$ ./betterphp.sh dev
````

### Create `create.sql` file
````shell
$ ./betterphp.sh db generate
````

### Create a .env file
- in the `src` folder
- see `.env.example`
- set the database credentials

## How to write the api
### model
- create a model in the `src/model` folder
   - the model must have the attribute 'Entity'
     ```php

        require_once dirname(__DIR__) . '/../betterphp/utils/Entity.php';
     ```
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
### controller
- create a controller in the `src/controller` folder
   - the controller must extend the `Controller` class
     ```php

        require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';
      ```
   - by throwing an exception you can send a http error code to the client
     ```php
        # example
        throw new Exception('Not found', 404);
        # or
        throw new Exception('Not found', HttpErrorCodes::NOT_FOUND);
      ```

### service
- create a service to write api endpoints
   - multiple routes can be defined for one service
   - the controller instance must be defined in the function
     ```php
        Route::get('/test', function () {
          $data = CurrencyController::getInstance()->getCurrencies();
          return Response::ok('Hello World', $data);
        });
     ```