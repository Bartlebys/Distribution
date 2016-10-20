FROM bartlebys/php-apache-mongo:latest
MAINTAINER Benoit Pereira da Silva <https://pereira-da-silva.com>

################################
## CONFIGURE AND ENABLE XDEBUG #
################################

#Erase the current Configuration of xdebug
RUN     echo "" > /usr/local/etc/php/conf.d/xdebug.ini

# Configure XDEBUG
# To start a Docker container with full XDEBUG support
# you must define the remote_host, serverName, and idekey
# Check runXDebug.sh for details.
# Check -> http://manual.pohape.ru/xdebug/

RUN     echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_mode=req" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/xdebug.ini\
        && echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/xdebug.ini

# Enable XDEBUG's extension
RUN    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> /usr/local/etc/php/conf.d/xdebug.ini

