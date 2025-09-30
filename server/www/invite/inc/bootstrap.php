<?php
define("PROJECT_ROOT_PATH", __DIR__ . "/../");
// include the base controller file 

require_once PROJECT_ROOT_PATH . "/Controller/BaseController.php";
// include the use model file 
require_once PROJECT_ROOT_PATH . "/Model/UserModel.php";

require_once PROJECT_ROOT_PATH . '/assets/lib/BeforeValidException.php';
require_once PROJECT_ROOT_PATH . '/assets/lib/ExpiredException.php';
require_once PROJECT_ROOT_PATH . '/assets/lib/SignatureInvalidException.php';
require_once PROJECT_ROOT_PATH . '/assets/lib/JWT.php';
require_once PROJECT_ROOT_PATH . '/Service/TaxCalculator.php';

?>