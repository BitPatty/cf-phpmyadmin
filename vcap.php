<?php

// The environment variable in which the VCAP services are stored
$VCAP_SERVICES_ENV_NAME = 'VCAP_SERVICES';
$VCAP_APPLICATION_ENV_NAME = 'VCAP_APPLICATION';

// The service tags to look up
$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID = 'PMA_VCAP_DB_SERVICE_TAGS';
$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID = 'PMA_VCAP_DB_SERVICE_TAGS_SEPARATOR';
$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR = ',';
$VCAP_DB_SERVICE_TAGS = [
    'mysql',
    'mariadb'
];

// Check if VCAP_SERVICES is present
if(! isset($_ENV[$VCAP_SERVICES_ENV_NAME])) {
    echo "Could not detect $VCAP_SERVICES_ENV_NAME.";
    exit(1);
}

// Parser overrides
if(
    isset($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID]) 
    && ! empty($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID])
) {
    $VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR = $_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR_ID];
}


if(
    isset($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID]) 
    && ! empty($_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID])
) {
    $VCAP_DB_SERVICE_TAGS = explode($VCAP_DB_SERVICE_TAGS_OVERRIDE_SEPARATOR, $_ENV[$VCAP_DB_SERVICE_TAGS_OVERRIDE_ID]);
}

// Find the MySQL services
$cf_parsed_vcap_services = json_decode($_ENV[$VCAP_SERVICES_ENV_NAME], true);
$cf_mysql_services = array();

foreach($cf_parsed_vcap_services as $cf_provider => $cf_service_list) {
    foreach($cf_service_list as $cf_service) {
        foreach($VCAP_DB_SERVICE_TAGS as $cf_service_tag) {
            if(in_array($cf_service_tag, $cf_service['tags'], true)) {
                $cf_mysql_services[] = $cf_service;
                break;
            }
        }
    }
}

if(
    count($cf_mysql_services) === 0
    && (
        ! isset($_ENV['PMA_ARBITRARY']) 
        || $_ENV['PMA_ARBITRARY'] !== '1'
    )
) {
    echo "No MySQL service detected and no arbitrary connections allowed";
    exit(1);
}

$cf_config = [
    'hosts' => array(),
    'ports' => array(),
    'verboses' => array()
];

for($i = 0; $i < count($cf_mysql_services); $i++) {
   $cf_config['hosts'][] = $cf_mysql_services[$i]['credentials']['host'];
   $cf_config['ports'][] = $cf_mysql_services[$i]['credentials']['port'];
   $cf_config['verboses'][] = $cf_mysql_services[$i]['instance_name'];
}


$apache_port = '3000';

if(
    isset($_ENV['PORT'])
    && ! empty($_ENV['PORT'])
) {
    $apache_port = $_ENV['PORT'];
}

$env_file = fopen(".cf.env", "a");

fwrite($env_file, "export PMA_HOSTS='" . implode(',', $cf_config['hosts']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export PMA_PORTS='" . implode(',', $cf_config['ports']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export PMA_VERBOSES='" . implode(',', $cf_config['verboses']) . "'");
fwrite($env_file, "\n");
fwrite($env_file, "export APACHE_PORT='" . $apache_port . "'");
fwrite($env_file, "\n");

fclose($env_file);

?>