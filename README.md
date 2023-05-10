# cf-phpmyadmin

An extension to the [phpmyadmin docker image](https://github.com/phpmyadmin/docker) targeted at deployments on the Cloud Foundry platform, which auto populates the available servers based on the bound services.

## Sample manifest.yml

```yml
---
version: 1
applications:
- name: my-app
   memory: 512M
   instances: 1
   docker:
     image: ghcr.io/bitpatty/cf-phpmyadmin:latest
   services:
     - my-mysql-database
     - some-other-database
```

## Environment variables

The following environment variables from the [original image](https://github.com/phpmyadmin/docker) will be overriden by the init script:

* ``PMA_HOSTS`` - Automatically pouplated based on bound services
* ``PMA_VERBOSES`` - Automatically pouplated based on bound services
* ``PMA_PORTS`` - Automatically pouplated based on bound services
* ``APACHE_PORT`` -  Based on the `PORT` environment variable, with fallback to 3000

The following environment variables are available for configuring the setup script:

* ``PMA_VCAP_DB_SERVICE_TAGS`` - A comma separated list of service tags used by MySQL services, defaults to `mysql` and `mariadb`.
* ``PMA_VCAP_DB_SERVICE_TAGS_SEPARATOR`` - If you cannot use a comma for ``PMA_VCAP_DB_SERVICE_TAGS`` you can specify the separator in this variable.

The following environment variables can be used as with the [original image](https://github.com/phpmyadmin/docker):

* ``PMA_ARBITRARY`` - when set to 1 connection to the arbitrary server will be allowed
* ``PMA_HOST`` - define address/host name of the MySQL server
* ``PMA_VERBOSE`` - define verbose name of the MySQL server
* ``PMA_PORT`` - define port of the MySQL server
* ``PMA_USER`` and ``PMA_PASSWORD`` - define username and password to use only with the `config` authentication method
* ``PMA_ABSOLUTE_URI`` - the full URL to phpMyAdmin. Sometimes needed when used in a reverse-proxy configuration. Don't set this unless needed. See [documentation](https://docs.phpmyadmin.net/en/latest/config.html#cfg_PmaAbsoluteUri).
* ``PMA_CONFIG_BASE64`` - if set, this option will override the default `config.inc.php` with the base64 decoded contents of the variable
* ``PMA_USER_CONFIG_BASE64`` - if set, this option will override the default `config.user.inc.php` with the base64 decoded contents of the variable
* ``PMA_UPLOADDIR`` - if defined, this option will set the path where files can be saved to be available to import ([$cfg['UploadDir']](https://docs.phpmyadmin.net/en/latest/config.html#cfg_UploadDir))
* ``PMA_SAVEDIR`` - if defined, this option will set the path where exported files can be saved ([$cfg['SaveDir']](https://docs.phpmyadmin.net/en/latest/config.html#cfg_SaveDir))
* ``PMA_CONTROLHOST`` - when set, this points to an alternate database host used for storing the [phpMyAdmin Configuration Storage database](https://docs.phpmyadmin.net/en/latest/setup.html#phpmyadmin-configuration-storage) database
* ``PMA_CONTROLPORT`` - if set, will override the default port (3306) for connecting to the control host for storing the [phpMyAdmin Configuration Storage database](https://docs.phpmyadmin.net/en/latest/setup.html#phpmyadmin-configuration-storage) database
* ``PMA_PMADB`` - define the name of the database to be used for the [phpMyAdmin Configuration Storage database](https://docs.phpmyadmin.net/en/latest/setup.html#phpmyadmin-configuration-storage). When not set, the advanced features are not enabled by default: they can still potentially be enabled by the user when logging in with the zero conf (zero configuration) feature. Suggested values: `phpmyadmin` or `pmadb`
* ``PMA_CONTROLUSER`` - define the username for phpMyAdmin to use for advanced features (the [controluser](https://docs.phpmyadmin.net/en/latest/config.html#cfg_Servers_controluser))
* ``PMA_CONTROLPASS`` - define the password for phpMyAdmin to use with the [controluser](https://docs.phpmyadmin.net/en/latest/config.html#cfg_Servers_controlpass)
* ``PMA_QUERYHISTORYDB`` - when set [to true](https://docs.phpmyadmin.net/en/latest/config.html#cfg_QueryHistoryDB), enables storing [SQL history](https://docs.phpmyadmin.net/en/latest/config.html#cfg_Servers_history) to the [phpMyAdmin Configuration Storage database](https://docs.phpmyadmin.net/en/latest/setup.html#phpmyadmin-configuration-storage). When [false](https://docs.phpmyadmin.net/en/latest/config.html#cfg_QueryHistoryDB), history is stored in the browser and is cleared when logging out
* ``PMA_QUERYHISTORYMAX`` - when set to an integer, controls the number of history items. See [documentation](https://docs.phpmyadmin.net/en/latest/config.html#cfg_QueryHistoryMax). Defaults to `25`.
* ``MAX_EXECUTION_TIME`` - if set, will override the maximum execution time in seconds (default 600) for phpMyAdmin ([$cfg['ExecTimeLimit']](https://docs.phpmyadmin.net/en/latest/config.html#cfg_ExecTimeLimit)) and PHP [max_execution_time](https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time) (format as `[0-9+]`)
* ``MEMORY_LIMIT`` - if set, will override the memory limit (default 512M) for phpMyAdmin ([$cfg['MemoryLimit']](https://docs.phpmyadmin.net/en/latest/config.html#cfg_MemoryLimit)) and PHP [memory_limit](https://www.php.net/manual/en/ini.core.php#ini.memory-limit) (format as `[0-9+](K,M,G)` where K is for Kilobytes, M for Megabytes, G for Gigabytes and 1K = 1024 bytes)
* ``UPLOAD_LIMIT`` - if set, this option will override the default value for apache and php-fpm (format as `[0-9+](K,M,G)` default value is 2048K, this will change ``upload_max_filesize`` and ``post_max_size`` values)
* ``TZ`` - if defined, this option will change the default PHP `date.timezone` from `UTC`. See [documentation](https://www.php.net/manual/en/timezones.php) for supported values.
* ``HIDE_PHP_VERSION`` - if defined, this option will hide the PHP version (`expose_php = Off`). Set to any value (such as `HIDE_PHP_VERSION=true`).
