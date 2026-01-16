<?php

include_once ( $dirSrvsSso = dirname(__FILE__) ).'/../../../../jacwright/restServer/source/Jacwright/RestServer/RestServer.php';
include_once $dirSrvsSso.'/SsoCommons.class.php';

$srv                    = new \Jacwright\RestServer\RestServer;
$srv->useCors           = true;
$srv->allowedOrigin     = '*';
$srv->addClass('SSoCommons');
$srv->handle();
