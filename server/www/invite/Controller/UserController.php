<?php

namespace Controller;

use Model\UserModel;
use Error;
use finfo;

class UserController extends BaseController
{
    private $authMiddleware;
    private $userModel;

    public function __construct($authMiddleware, UserModel $userModel)
    {
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }
}
