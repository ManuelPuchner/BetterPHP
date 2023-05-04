# BetterPHP

## How to use
### Create Database
````shell
$ docker compose -f docker-compose-db.yaml up [-d]
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

### Create a .env file
- in the `src` folder
- see `.env.example`
- set the database credentials

### Start the api
- Either use Apache or
- Use the php built-in server
````shell
$ php -S localhost:8080 -t ./dist
````

