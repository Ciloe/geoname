ARG VERSION

FROM mdillon/postgis:${VERSION}

RUN set -ex; \
  sed -e 's/^deb /deb-src /' < /etc/apt/sources.list >> /etc/apt/sources.list; \
  apt-get update; \
  apt-get install -qy libdbd-pg-perl postgresql-client; \
  apt-get build-dep -qy sqitch; \
  cpan App::Sqitch

COPY ./docker/postgres/data/pgdata /var/lib/postgresql/data/pgdata
COPY ./docker/postgres/sqitch.conf /root/.sqitch/sqitch.conf
COPY ./sqitch /sqitch
COPY ./import /import
