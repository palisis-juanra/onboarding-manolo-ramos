# TourCMS API implementation demo

> [!NOTE]
> This project is under development.

## Main features

- *User login and session management.*
- *Channel selection.*
- *Tour list rendering*
- *Full booking process, including rates, departure and clients selection.*
- *Search Tour by Tour ID.*
- *Search Customer by Customer ID.*


## Requirements

- **Docker** for the local environment.
- **Apache** or another web server with **PHP 8+** installed.
- **Redis 5.0.13** or later.

## Running the project
1. Clone the repo into the public directory of your local web server.
2. Create a .env file using the .env-default template and add your configuration where necessary.
3. Install all composer dependencies with `composer install`
4. Access the application using `http://localhost`