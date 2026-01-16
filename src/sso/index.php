<?php

include_once 'inc.php';

$srv                    = new \Jacwright\RestServer\RestServer;
$srv->useCors           = true;
$srv->allowedOrigin     = '*';
$srv->rootPath          = \SSO\SsoSystem::Instance()->{ \SSO\SsoSystem::ROOT_INDEX };
$srv->addClass('\SSO\Router');
$srv->handle();
