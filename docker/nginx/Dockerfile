ARG VERSION

# Dev image
FROM nginx:${VERSION}-alpine as dev

# Copy nginx config
COPY ./docker/nginx/site.conf /etc/nginx/conf.d/default.conf

# Prod image
FROM dev as prod

COPY ./ /code
