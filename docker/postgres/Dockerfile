FROM mdillon/postgis:11

RUN set -ex; \
  sed -e 's/^deb /deb-src /' < /etc/apt/sources.list >> /etc/apt/sources.list; \
  apt-get update; \
  apt-get install -qy libdbd-pg-perl postgresql-client; \
  apt-get build-dep -qy sqitch; \
  cpan App::Sqitch