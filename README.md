# Mean Kinetic Temperature Calculator

This is a PHP & Symfony application to calculate the Mean Kinetic Temperature (MKT) for a given set of time/temperature data.

## Features

- Upload time/temperature data from CSV, YML, or XML files
- Store data sets in the database
- Calculate and display MKT for each data set
- View data sets and their MKT results

## Requirements

- Docker
- Docker Compose

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/your-username/mkt_calculator.git
   cd mkt_calculator
   ```

2. Build and start the Docker containers:

   ```bash
   docker-compose up --build
   ```

3. Create the database schema:

   ```bash
   docker-compose exec app php bin/console doctrine:migrations:migrate
   ```

4. Open your browser and navigate to `http://localhost:9000`.

## Running Tests

```bash
docker-compose exec app php bin/phpunit
```

## License

This project is licensed under the MIT License.