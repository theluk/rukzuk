FROM phusion/baseimage:0.9.15

VOLUME /srv/rukzuk/htdocs/cms

# proposed breaks some php packages (e.g. php-intl)
RUN rm -rf /etc/apt/sources.list.d/proposed.list

# phusion/baseimage is not always up to date. :-(
RUN apt-get update  && \
    apt-get dist-upgrade -y --no-install-recommends


# Set Timezone
RUN echo "Europe/Berlin" > /etc/timezone
RUN dpkg-reconfigure --frontend noninteractive tzdata

# Make Debian/Ubunut and Docker friends
ENV DEBIAN_FRONTEND noninteractive

# install phantomjs 2.0
RUN apt-get install -y --no-install-recommends software-properties-common && \
    add-apt-repository ppa:tanguy-patte/phantomjs && \
    apt-get update && \
    apt-get install -y --no-install-recommends phantomjs

# Install Apache httpd
RUN apt-get install -y --no-install-recommends \
        sqlite3 \
        apache2 \
        libapache2-mod-php5 \
        libv8-3.14.5 \
        msmtp \
        msmtp-mta \
        php5 \
        php5-sqlite \
        php5-cli \
        php5-curl \
        php5-gd \
        php5-intl \
        php5-mcrypt

# Activate Apache mods
RUN a2enmod ssl && \
    a2enmod rewrite

# Activate PHP mods
RUN php5enmod mcrypt

# Install php5-v8js
COPY php5-v8js_0.1.3-1_amd64.deb /tmp/php5-v8js.deb
RUN dpkg -i /tmp/php5-v8js.deb

# Activate apache2 in runit
RUN mkdir -p /etc/service/apache2
COPY apache2.runit /etc/service/apache2/run
RUN chmod +x /etc/service/apache2/run

# Create folder
ENV CMS_PATH /opt/rukzuk/htdocs
ENV INSTANCE_PATH /srv/rukzuk
RUN mkdir -p ${CMS_PATH}
RUN mkdir -p ${INSTANCE_PATH}/htdocs/cms
RUN chown -R www-data:www-data ${INSTANCE_PATH}/htdocs

# Install the release/cmsrelase.tar.gz (a version from our Jenkins)
RUN curl -L $(curl -s https://api.github.com/repos/rukzuk/rukzuk/releases/latest | grep browser_download_url | grep 'tgz\|tar.gz' | cut -d '"' -f 4) | tar -xz --strip 1 -C ${CMS_PATH}/..


RUN ln -s ${CMS_PATH}/app/server/environment ${INSTANCE_PATH}/environment
RUN ln -s ${CMS_PATH} ${INSTANCE_PATH}/application

# Initial
ENV APPLICATION_ENV standalone
ENV CMS_SQLITE_DB ${INSTANCE_PATH}/htdocs/cms/db.sqlite3
COPY config.php ${INSTANCE_PATH}/config.php
COPY cms.apache /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /etc/my_init.d
COPY init.sh /etc/my_init.d/rukzuk_init.sh
RUN chmod +x /etc/my_init.d/rukzuk_init.sh
COPY msmtprc.tpl /etc/msmtprc.tpl

EXPOSE 80

