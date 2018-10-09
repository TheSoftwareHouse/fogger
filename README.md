# *Fogger* - GDPR friendly database masker

## Purpose

*Fogger* is a tool that solves the problem of data privacy. When developers need to work with production 
data but are obliged to comply with GDPR regulations they need a way to get the database copy with all the
sensitive data masked. And while you can always write your own, custom solution to the problem - **you 
don't have to anymore** - with *fogger* you are covered.
Apart from masking data you can also subset or even exclude some tables. Don't worry for the realtions with
foreign keys, *fogger* will refine database so everything is clean and shiny.  
You can configure various masking and subsetting strategies, and when what *fogger* has to offer is not enough - you 
can easily extend it with your own strategies.

## How to use the docker image

*Fogger* requires docker environment, redis and rabbitMq services and two databases: source and target. You can
set up this stack using for example this docker-compose file: 
```
version: '2.0'
services:
  fogger:
    image: tshio/fogger:latest
    volumes:
    - .:/fogger
    environment:
      SOURCE_DATABASE_URL: mysql://user:pass@source:3306/source
      TARGET_DATABASE_URL: mysql://user:pass@target:3306/target
      RABBITMQ_URL: amqp://user:pass@rabbit:5672
      REDIS_URL: redis://redis
  worker:
    image: fogger-app:latest
    environment:
      SOURCE_DATABASE_URL: mysql://user:pass@source:3306/source
      TARGET_DATABASE_URL: mysql://user:pass@target:3306/target
      RABBITMQ_URL: amqp://user:pass@rabbit:5672
      REDIS_URL: redis://redis
    restart: always
    command: rabbit:consumer --messages=200 fogger_data_chunks
  redis:
    image: redis:4
  rabbit:
    image: rabbitmq:3
    environment:
      RABBITMQ_DEFAULT_USER: user
      RABBITMQ_DEFAULT_PASS: pass
  source:
    volumes:
    - ./dump.sql:/docker-entrypoint-initdb.d/dump.sql
    environment:
      MYSQL_DATABASE: source
      MYSQL_PASSWORD: pass
      MYSQL_ROOT_PASSWORD: pass
      MYSQL_USER: user
    image: mysql:5.7
  target:
    environment:
      MYSQL_DATABASE: target
      MYSQL_PASSWORD: pass
      MYSQL_ROOT_PASSWORD: pass
      MYSQL_USER: user
    image: mysql:5.7
```
Note: 
  - we are mapping volume to fogger's and worker's `/fogger` directory - so the config file would be accesible both in 
  container and in our host filesystem
  - we are importing database content from `dump.sql`
     
Of course you can modify and adjust the settings to your needs - for example - instead of importing database from 
dump file you can pass the existing database url to `fogger` and `worker` containers in the env variables.

Now we can spin up the set-up by `docker-compose up -d`. If the database is huge and you want to speed up the process 
you can spawn additional workers executing `docker-compose up -d --scale=worker=4` instead. Give it few seconds for the
services to spin up then you can start with *Fogger*:

*Fogger* gives you three CLI commands:

* `docker-compose run --rm fogger fogger:init` will connect to your source database and prepare a boilerplate 
configuration file with the information on tables and columns in your database. This configuration file is a place 
where you define witch column should be masked (and how) and witch tables should be subsetted. 
See [example config file](Example config file).

* `docker-compose run --rm fogger fogger:run` is the core command that will orchestrate the copying, masking and 
subsetting of data. The actual copying will be done by background worker that can scale horizontally. Before `run`
is executed make sure that the config file has been modified to your needs. Available subset and mask strategies has
been described below. 

* `docker-compose run --rm fogger fogger:finish` will recreate indexes, refine database so that all the foreign key 
constraints are still valid, and then recreate them as well. This command runs automatically after run so you 
need to execute it only when you have stopped the `run` command with `ctrl-c`.

* it's done - the masked and subsetted data are in a target database. You can do whatever you please with it. For
example: `docker-compose exec target /usr/bin/mysqldump -u user --password=pass target > target.sql` will save the
dump of masked database in your filesystem.           

### Example config file

```
tables:
  posts:
    columns:
      title: { maskStrategy: starify, options: { length: 12 } }
      body: { maskStrategy: faker, options: { method: "sentences" } }
    subsetStrategy: tail
    subsetOptions: { length: 1000 }
  comments:
    columns:
      comment: { maskStrategy: faker, options: { method: "sentences" } }
  users:
    columns:
      email: { maskStrategy: faker, options: { method: "safeEmail" } }
excludes:
    - logs
```
This is an example of config file. The boilerplate based on your database schema will be generated for you by 
`fogger:init`, all you have to do is fill in the mask strategies on the columns that you want masked and subset 
strategies on the tables for witch you only want fraction of the rows. 

For the clarity and readability of the config files, all the tables that will not be changed
can be omitted. They will be copied as they are. Similarly you can omit columns that are not to be masked. 
Tables from the `excludes` section will exist in the target database, but will be empty. 

### List of available strategies

#### Masking data

* hashify - will save the MD5 hash instead of data - you can pass optional argument: `template`
    
    `email: { maskStrategy: "hashify", options: { template: "%s@example.com" } }`

* starify - will save the 10 stars instead of data - you can pass optional argument: `length` to override default 10
    
    `email: { maskStrategy: "starify", options: { }`

* faker - will use a marvelous [faker](https://github.com/fzaninotto/Faker) library. Pass the `method` of faker that 
you want to use here as an option. 

    `email: { maskStrategy: "faker", options: { method: "safeEmail" }`
    `date: { maskStrategy: "faker", options: { method: "date", parameters: ["Y::m::d", "2017-12-31 23:59:59"] }`
    
#### Subsetting data

* range - only copy those rows, where `column` is between `min` and `max`
```
subsetStrategy: range
subsetOptions: { column: "craetedAt", min: "2018-01-01 00:00", max: "2018-01-31 23:59:59" }
```

* head and tail - only copy `length` first / last rows
```
subsetStrategy: head
subsetOptions: { length: 1000 }
```
or
```
subsetStrategy: tail
subsetOptions: { length: 1000 }
```

### Under the hood

If you are interested what really happens: 

* source database schema without indices and foreign keys is copied to target
* data is divided into chunks (this includes query modification for subsetting). Chunks are processedby
background workers (using RabbitMQ) 
* during copying sensitive data is substituted for masked version - in order to keep the substituted values
consistent, redis is used as a cache
* when all data is copied, *fogger* will recreate indices 
* refining cleans up database removing (or setting to null) relations that point to excluded or subsetted table rows
* the last step is to recreate foreign keys 

## Contributing

Feel free to contribute to this project! Just fork the code, make any updates and let us know!
