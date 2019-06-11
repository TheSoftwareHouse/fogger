# *Fogger* - GDPR friendly database masker

## Purpose

*Fogger* is a tool that solves the problem of data privacy. When developers need to work with production data but are obliged to comply with GDPR regulations they need a way to get the database copy with all the sensitive data masked. And while you can always write your own, custom solution to the problem - **you don't have to anymore** - with *fogger* you are covered.

Apart from masking data you can also subset or even exclude some tables. Don't worry for the realtions with foreign keys, *fogger* will refine database so everything is clean and shiny.  

You can configure various masking and subsetting strategies, and when what *fogger* has to offer is not enough - you can easily extend it with your own strategies.

## How to use the docker image

*Fogger* requires docker environment, redis for caching and two databases: source and target. 

Start with building the image youself `docker build . -t mongo-fogger  . `

You can then set up a stack using this docker-compose file:
 
```
version: '2.0'
services:
  fogger:
    image: mongo-fogger
    volumes:
    - .:/fogger
    environment:
      REDIS_URI: redis://redis
      MONGO_URI: mongodb://root:example@mongo:27017
  worker:
    image: tshio/fogger:latest
    environment:
      REDIS_URI: redis://redis
      MONGO_URI: mongodb://root:example@mongo:27017
    restart: always
    command: fogger:consumer --messages=200
  redis:
    image: redis:4
  gui:
    image: mongo-express
    ports:
      - 8081:8081
    environment:
      ME_CONFIG_MONGODB_SERVER: mongo
      ME_CONFIG_MONGODB_ADMINUSERNAME: root
      ME_CONFIG_MONGODB_ADMINPASSWORD: example
  mongo:
    image: mongo
    restart: always
    environment:
      MONGO_INITDB_ROOT_USERNAME: root
      MONGO_INITDB_ROOT_PASSWORD: example
```
Note: 
  - we are mapping volume to fogger's and worker's `/fogger` directory - so the config file would be accesible both in container and in our host filesystem
  - we are using here empty mongo instance and a mongo-express gui
  - the above mongo images should be removed and real instance should be refered in env variables
     
Now we can spin up the set-up by `docker-compose up -d`. If you have the resources and want to speed up the process you can spawn additional workers executing `docker-compose up -d --scale=worker=4` instead. Give it few seconds for the services to spin up then you can start with *Fogger*:

* `docker-compose run --rm fogger fogger:mongo:run` will read the config file, connect to your source database and start preparing chunk information for the workers. See [example config file](Example config file).
* `docker-compose run --rm fogger fogger:mongo:finish` will create on target collection all the indexes present in source collection. 
* `docker-compose run --rm fogger fogger:mongo:books` is an additional command that will populate your mongo server, test database, books collection with example documents to play with before you start with real data. 

### Example config file

```
source: test
target: target
collections:
    books:
        paths:
            '$.authors[*].firstName': { maskStrategy: faker, options: { method: firstName } }
            '$.review': { maskStrategy: faker, options: { method: realText, arguments: [ 50 ] } }
```
This is an example of config file. 

### List of available strategies

#### Masking data

* hashify - will save the MD5 hash instead of data - you can pass optional argument: `template`
    
    `email: { maskStrategy: "hashify", options: { template: "%s@example.com" } }`

* starify - will save the 10 stars instead of data - you can pass optional argument: `length` to override default 10
    
    `email: { maskStrategy: "starify" }`

* faker - will use a marvelous [faker](https://github.com/fzaninotto/Faker) library. Pass the `method` of faker that you want to use here as an option. 

    `email: { maskStrategy: "faker", options: { method: "safeEmail" } }`
    `date: { maskStrategy: "faker", options: { method: "date", arguments: ["Y::m::d", "2017-12-31 23:59:59"] } }`
    
## Contributing

Feel free to contribute to this project! Just fork the code, make any updates and let us know!
