ARG PHPMYADMIN_VERSION="5.2.1"

FROM phpmyadmin:${PHPMYADMIN_VERSION}-apache

COPY ./cf-entrypoint.sh ./cf-entrypoint.sh
COPY ./vcap.php ./vcap.php
RUN chmod +x cf-entrypoint.sh

LABEL org.opencontainers.image.authors="matteias.collet@protonmail.com"
LABEL org.opencontainers.image.source="https://github.com/BitPatty/cf-phpmyadmin"
LABEL org.opencontainers.image.title="cf-phpmyadmin"
LABEL org.opencontainers.image.description="Run phpMyAdmin with Alpine, Apache and PHP FPM on Cloud Foundry."

ENTRYPOINT ["./cf-entrypoint.sh"]
CMD ["apache2-foreground"]