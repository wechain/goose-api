FROM ubuntu:18.04
MAINTAINER redgoose <scripter@me.com>

WORKDIR /goose

RUN apt-get -qq update
RUN apt-get -y -qq install nano net-tools

# install php
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get -y -qq install php7.2-cli php7.2-fpm php7.2-curl php7.2-mysql php7.2-mbstring

# install composer
RUN apt-get -y -qq install openssl ca-certificates
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN chmod +x composer.phar
RUN mv composer.phar /usr/local/bin/composer

# copy project files
COPY ./ .

# setting in project
RUN composer install
RUN ./script.sh ready

# play command
CMD service php7.2-fpm start && php -S 0.0.0.0:8000 server.php

EXPOSE 8000