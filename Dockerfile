ARG PHPMYADMIN_VERSION="5.2.1"

FROM phpmyadmin:${PHPMYADMIN_VERSION}-apache

COPY ./cf-entrypoint.sh ./cf-entrypoint.sh
COPY ./vcap.php ./vcap.php
RUN chmod +x cf-entrypoint.sh

ENTRYPOINT ["./cf-entrypoint.sh"]
CMD ["apache2-foreground"]