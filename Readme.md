//Creating a new laravel project with docker in the current directory, ensure there are not capital letters and that the directories start with small letters
sudo docker run --rm -v $(pwd):/app composer create-project --prefer-dist laravel/laravel laravel_authorisation


//Docker file
FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        zip \
        unzip

RUN docker-php-ext-install pdo pdo_mysql zip

# Copy the Laravel project files, including the artisan file
COPY --chown=www-data:www-data . /var/www/html

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]



//Docker compose yaml
version: '3'

services:
  web:
    build:
      context: .
      dockerfile: Dockerfile
    image: laravel_authorisation
    container_name: laravel_authorisation
    ports:
      - "8000:8000"
    depends_on:
      - mysql
    networks:
      - laravel

  mysql:
    image: mysql:5.7
    container_name: laravel_authorisation_mysql
    environment:
      MYSQL_DATABASE: laravel_authorisation
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_password
      MYSQL_ROOT_PASSWORD: root_password
    ports:
      - "3306:3306"
    networks:
      - laravel

networks:
  laravel:
    driver: bridge



//Spinning the containers
docker-compose up -d


docker-compose exec web composer install
docker-compose exec web cp .env.example .env
docker-compose exec web php artisan key:generate



DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_authorisation
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password

//running the application
docker-compose exec web php artisan serve --host=0.0.0.0 --port=8000

//rebuilding a single container
docker build -t laravel_authorisation .

//Running a single container
docker run -p 8000:8000 laravel_authorisation

Ensure the docker-compose,yml and the Dockerfile are in the root directory of the project, i.e same directory as the env and the artisan file

rebuild with this command docker build -t container_name .
