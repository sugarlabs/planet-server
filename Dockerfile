FROM php:8.2-alpine
WORKDIR /planet-server
COPY . /planet-server
RUN ls
CMD [ "php", "index.php" ]
