<?php

include_once 'inc.php';
error_log(__FILE__);
$srv                    = new \Jacwright\RestServer\RestServer;
$srv->useCors           = true;
$srv->allowedOrigin     = '*';
$srv->rootPath          = \SSO\SsoSystem::Instance()->{ \SSO\SsoSystem::ROOT_API };
$srv->addClass('\SSO\Api');
$srv->handle();