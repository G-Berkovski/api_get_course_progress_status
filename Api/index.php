<?php

require_once '../vendor/autoload.php';

use \Api\Core\Controller\MainController;

set_error_handler("\Api\Core\Helper\ErrorHelper::handleError");
set_exception_handler("\Api\Core\Helper\ErrorHelper::handleException");


header("Content-type: application/json; charset=UTF-8");

MainController::route('\Api\%s\Controller\\');
