<?php

// The environment variable in which the VCAP services are stored
$VCAP_SERVICES_ENV_NAME = 'VCAP_SERVICES';
$VCAP_APPLICATION_ENV_NAME = 'VCAP_APPLICATION';

// The service tags to look up
$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID = 'PMA_VCAP_DB_SERVICE_TAGS';
$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID = 'PMA_VCAP_DB_SERVICE_TAGS_SEPARATOR';
$VCAP_DB_SERVICE_TAGS_SEPARATOR = ',';
$VCAP_DB_SERVICE_TAGS = [
    'mysql',
    'mariadb'
];

// Configuration database
$VCAP_CONFIG_DB_SERVICE_NAME_ID = 'PMA_VCAP_CONFIG_DB_SERVICE_NAME';
$VCAP_CONFIG_DB_SERVICE_NAME = '';

// Apache configuration
$APACHE_PORT = '3000';

// Check if VCAP_SERVICES is present
if (!isset($_ENV[$VCAP_SERVICES_ENV_NAME])) {
    echo "Could not detect $VCAP_SERVICES_ENV_NAME.";
    exit(1);
}

// Parser overrides
if (
    isset($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID])
    && !empty($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID])
) {
    $VCAP_DB_SERVICE_TAGS_SEPARATOR = $_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID];
}

if (
    isset($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID])
    && !empty($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID])
) {
    $VCAP_DB_SERVICE_TAGS = explode($VCAP_DB_SERVICE_TAGS_SEPARATOR, $_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID]);
}

if (
    isset($_ENV[$VCAP_CONFIG_DB_SERVICE_NAME_ID])
    && !empty($_ENV[$VCAP_CONFIG_DB_SERVICE_NAME_ID])
) {
    $VCAP_CONFIG_DB_SERVICE_NAME = $_ENV[$VCAP_CONFIG_DB_SERVICE_NAME_ID];
}

// Find the MySQL services
$cf_parsed_vcap_services = json_decode($_ENV[$VCAP_SERVICES_ENV_NAME], true);
$cf_mysql_services = array();

foreach ($cf_parsed_vcap_services as $cf_provider => $cf_service_list) {
    foreach ($cf_service_list as $cf_service) {
        foreach ($VCAP_DB_SERVICE_TAGS as $cf_service_tag) {
            if (in_array($cf_service_tag, $cf_service['tags'], true)) {
                $cf_mysql_services[] = $cf_service;
                break;
            }
        }
    }
}

// Check that there is at least one present, unless arbitrary is allowed
if (
    count($cf_mysql_services) === 0
    && (!isset($_ENV['PMA_ARBITRARY'])
        || $_ENV['PMA_ARBITRARY'] !== '1'
    )
) {
    echo "No MySQL service detected and no arbitrary connections allowed";
    exit(1);
}

// Extract the relevant configuration values
$cf_config = [
    'hosts' => array(),
    'ports' => array(),
    'verboses' => array(),
    'controlhost' => '',
    'controlport' => '',
    'controldb' => '',
    'controluser' => '',
    'controlpass' => ''
];

for ($i = 0; $i < count($cf_mysql_services); $i++) {
    if (
        isset($VCAP_CONFIG_DB_SERVICE_NAME)
        && !empty($VCAP_CONFIG_DB_SERVICE_NAME)
        && $cf_config['instance_name'] === $VCAP_CONFIG_DB_SERVICE_NAME
    ) {
        $cf_config['controlhost'] = $cf_mysql_services[$i]['credentials']['host'];
        $cf_config['controlport'] = $cf_mysql_services[$i]['credentials']['port'];
        $cf_config['controluser'] = $cf_mysql_services[$i]['credentials']['username'];
        $cf_config['controlpass'] = $cf_mysql_services[$i]['credentials']['password'];
        $cf_config['controldb'] = $cf_mysql_services[$i]['credentials']['database'];
    } else {
        $cf_config['hosts'][] = $cf_mysql_services[$i]['credentials']['host'];
        $cf_config['ports'][] = $cf_mysql_services[$i]['credentials']['port'];
        $cf_config['verboses'][] = $cf_mysql_services[$i]['instance_name'];
    }
}

// Check that the control db is around if necessary
if (
    isset($VCAP_CONFIG_DB_SERVICE_NAME)
    && !empty($VCAP_CONFIG_DB_SERVICE_NAME)
    && (!isset($cf_config['controlhost'])
        || empty($cf_config['controlhost'])
    )
) {
    echo "$VCAP_CONFIG_DB_SERVICE_NAME configuration not found";
    exit(1);
}

// Set the apache port
if (
    isset($_ENV['PORT'])
    && !empty($_ENV['PORT'])
) {
    $APACHE_PORT = $_ENV['PORT'];
}

// Write the env configuration into .cf.env
$env_file = fopen(".cf.env", "a");

fwrite($env_file, "export PMA_HOSTS='" . implode(',', $cf_config['hosts']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export PMA_PORTS='" . implode(',', $cf_config['ports']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export PMA_VERBOSES='" . implode(',', $cf_config['verboses']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export APACHE_PORT='" . $APACHE_PORT . "'");
fwrite($env_file, "\n");

// Populate the control db configuration
if (
    isset($VCAP_CONFIG_DB_SERVICE_NAME)
    && !empty($VCAP_CONFIG_DB_SERVICE_NAME)
) {
    fwrite($env_file, "export PMA_CONTROLHOST='" . $cf_config['controlhost'] . "'");
    fwrite($env_file, "\n");
    fwrite($env_file, "export PMA_CONTROLPORT='" . $cf_config['controlport'] . "'");
    fwrite($env_file, "\n");
    fwrite($env_file, "export PMA_PMADB='" . $cf_config['controldb'] . "'");
    fwrite($env_file, "\n");
    fwrite($env_file, "export PMA_CONTROLUSER='" . $cf_config['controluser'] . "'");
    fwrite($env_file, "\n");
    fwrite($env_file, "export PMA_CONTROLPASS='" . $cf_config['controlpass'] . "'");
    fwrite($env_file, "\n");
    exit(1);
}

fclose($env_file);
