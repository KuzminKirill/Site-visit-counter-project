# Country Visit Statistics Microservice

This microservice records site visits by country and provides endpoints to collect site visits.

## Features

- Update statistics: Allows recording site visits by country.
- Show statistics: Provides collected statistics for all countries.

## Technologies Used

- PHP 8.3.7
- Symfony framework (ver 7.0.7)
- Docker for containerization
- Redis for caching

## Installation

1. Clone the repository:

   ```bash
   git clone <repository-url>
   ```

2. Build the docker container

    ```bash
   docker-compose up --build
   ```

3. After building, you can exit console, and do run into background:

    ```bash
   docker-compose up -d
    ``` 

4. For easy use I already add .env with all you need to this project, but ***please do not do that in real project, protect your env data***

### One more time: please do not do that in real project, protect your env data!!!

## Usage
### You can access to api from local machine by sending requests to http://localhost:8000

1. Updating statistics:

   ```http
   POST /update
   ```

   Example request body:

   ```json
   {
       "country": "US"
   }
   ```

2. Retrieving statistics:

   ```http
   GET /statistics
   ```

Countries should be in a **ISO 3166-1 Alpha-2** standard and upper case (it becomes so in any case)

## Testing

1. Enter docker container with project 
```bash
   docker exec -it <your container name> bash
 ```
By default container name is:  **site_visit_app-web-1**

2. Run PHPUnit tests:

```bash
php bin/phpunit
```

## What can be done in addition?

1. Add getting list of countries from external source to validate real countries
2. Move code from controller to service
3. Add different type of data storage (redis will be bottleneck because it's single thread way of work). It’s worth taking some kind of database that will be fast to write and only then calculate the amount for the period in the background; here some kind of timeseries DB like clickhouse or postgres timescaledb would be more suitable.
4. At least - update redis config in case of RDB settings
5. We can use other way to contain statistic - one key - one county. But getting sum by using "keys", will be not the best operation in terms of performance.
