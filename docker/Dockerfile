# Local host for the site. Runs the same Apache modules the live server does,
# so .htaccess is actually exercised instead of being ignored by php -S.
FROM php:8.3-apache

# The document root .htaccess needs all four of these. None are on by default.
RUN a2enmod rewrite headers expires deflate

# Debian's stock vhost ignores .htaccess entirely. Ionos does not.
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# There is no mail transport in a container, so mail() would fail with nothing
# to show for it. Write the message to a file the host can read instead.
COPY capture-mail.sh /usr/local/bin/capture-mail
RUN chmod +x /usr/local/bin/capture-mail \
 && echo 'sendmail_path = /usr/local/bin/capture-mail' \
      > /usr/local/etc/php/conf.d/zz-capture-mail.ini \
 && mkdir -p /maildrop && chown www-data:www-data /maildrop

# Surface mistakes rather than white pages while developing.
RUN { echo 'display_errors = On'; echo 'error_reporting = E_ALL'; } \
      > /usr/local/etc/php/conf.d/zz-dev-errors.ini
