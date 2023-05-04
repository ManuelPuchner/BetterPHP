# BetterPHP

## How to use
### Create Database
````shell
$ docker compose -f docker-compose-db.yaml up [-d]
````

### Create create-script
````shell
$ php betterphp/cmd/generateTables.php 
````

### Generate the api
````shell
$ php betterphp/cmd/index.php 
````

### Start the api
- Either use Apache or
- Use the php built-in server
````shell
$ php -S localhost:8080 -t ./dist
````

