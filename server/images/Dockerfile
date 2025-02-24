FROM php:7.4-fpm

# Install LDAP extension dependencies
RUN apt-get update && \
    apt-get install -y libldb-dev libldap2-dev nano ldap-utils unzip && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-install pcntl ldap pdo pdo_mysql

# Install tzdata and set timezone to Europe/Kiev
RUN apt-get install -y tzdata
RUN ln -fs /usr/share/zoneinfo/Europe/Kiev /etc/localtime

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the timezone in php.ini
RUN echo "date.timezone = Europe/Kiev" >> /usr/local/etc/php/php.ini

# Copy custom php.ini file if needed
COPY php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www

CMD ["php-fpm"]
