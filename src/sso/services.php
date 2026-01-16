<?php
// session_start();
include_once 'inc.php';
// error_log('targeted services');

$srv                    = new \Jacwright\RestServer\RestServer;
$srv->useCors           = true;
$srv->allowedOrigin     = '*';
$srv->rootPath          = \SSO\SsoSystem::Instance()->{ \SSO\SsoSystem::ROOT_SERVICES };
$srv->addClass('\SSO\Form');
$srv->addClass('\SSO\FormForAdmin');
$srv->handle();
