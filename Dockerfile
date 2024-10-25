FROM php:8.2-alpine

WORKDIR /planet-server

COPY . /planet-server

RUN ls

EXPOSE 8080

CMD [ "php", "-S", "0.0.0.0:8080", "-t", "." ]
