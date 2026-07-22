FROM nginx:1.27-alpine

RUN apk add --no-cache openssl

COPY docker/nginx/docker-entrypoint.d/09-render-noma-conf.sh /docker-entrypoint.d/09-render-noma-conf.sh
RUN chmod +x /docker-entrypoint.d/09-render-noma-conf.sh
