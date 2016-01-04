<?php

class Angel_Model_User extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\User';

    // 关于用户的一些文件
    // 身份证的正面
    const FILETYPE_IDENTITY_FRONT = 'fif';
    // 身份证的背面
    const FILETYPE_IDENTITY_BACK = 'fib';

    /**
     * 通过emai和password登录
     * @param String $email
     * @param String $password
     * @return boolean 
     */
    public function auth($email, $password) {
        $result = array('valid' => false, 'msg' => '');

        $auth = Zend_Auth::getInstance();
        $adapter = new Angel_Auth_Adapter_Mongo($this->_dm, $this->_document_class, $email, $password);

        $auth = $auth->authenticate($adapter);
        if (!$auth->isValid()) {
            $result['msg'] = $auth->getMessages();
        } else {
            $result['valid'] = true;
            $result['msg'] = $auth->getIdentity();
        }

        return $result;
    }

    public function addManageUser($email, $password, $salt, $checkemail = true) {
        $usertype = "admin";
        return $this->registerUser($email, $password, uniqid(), $usertype, $salt, $checkemail, NULL);
    }

    public function addUser($email, $password, $username, $age, $gender, $salt, $checkemail = true) {

        $usertype = "user";
        return $this->registerUser($email, $password, $username, $usertype, $salt, $checkemail, $age, $gender);
    }
    
    public function addVip($email, $password, $username, $usertype, $salt, $checkmail, $age, $gender, $name, $author) {
        $result = false;
        if (empty($email)) {
            throw new Angel_Exception_User(Angel_Exception_User::EMAIL_EMPTY);
        } else {
            $validation = new Zend_Validate_EmailAddress();
            if (!$validation->isValid($email)) {
                throw new Angel_Exception_User(Angel_Exception_User::EMAIL_INVALID);
            } else {
                if ($this->isEmailExist($email)) {
                    throw new Angel_Exception_User(Angel_Exception_User::EMAIL_NOT_UNIQUE);
                }
                if ($this->isUsernameExist($username)) {
                    throw new Angel_Exception_User(Angel_Exception_User::USERNAME_NOT_UNIQUE);
                }
            }
        }

        $user = new $this->_document_class();

        $user->email = $email;
        $user->username = $username;
        $user->salt = $salt;
        $user->user_type = $usertype;
        $user->password = $password;
        $user->age = $age;
        $user->gender = $gender;
        $user->active_bln = true;
        $user->email_validated_bln = !$checkemail;
        $user->validated_bln = false;
        $user->name = $name;
        $user->author = $author;

        try {
            $this->_dm->persist($user);
            $this->_dm->flush();

            $result = $user->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }

        // send email to the new user to notice him to active his account
        if ($result && $checkemail) {
            $this->sendAccountValidationEmail($user);
        }
        // send email to the new user to welcome them
        if ($result && !$checkemail) {
            
        }
        return $result;
    }
    
    public function getVipList($return_as_paginator = true) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->field('author')->equals(1)->sort('created_at', -1);
        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }
        return $result;
    }
    
    public function saveVip($uesrId, $email, $username, $password, $salt, $checkmail, $age, $gender, $name, $author) {
        $result = false;
        
        try {
            $user = $this->getUserById($uesrId);
   
            if (!$password) {
                $password = $user->password_src;
            }
            
            $active_bln = true;
            $email_validated_bln = !$checkemail;
            $validated_bln = false;
            $user_type = $user->user_type;
            
            $data = array("email" => $email, "name"=>$name, "username" => $username, "salt" => $salt, "user_type" => $user_type, "password" => $password, "age"=> $age, "gender"=>$gender, "user_type"=> $user_type, "active_bln" => $active_bln, "email_validated_bln" => $email_validated_bln, "validated_bln" => $validated_bln, "category"=> $user->category, "author"=> $author);

            $this->save($uesrId, $data);
            
            $result = true;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }
        
        return $result;
    }

    public function setAttribute($user, $key, $value) {
        $validated_key = array('player_mode');
        if (!in_array($key, $validated_key)) {
            return false;
        }
        $attribute = $user->attribute;
        if (!$attribute) {
            $attribute = array();
        }

        $attribute[$key] = $value;
        $result = $this->updateUser($user, array('attribute' => $attribute));
        return $result;
    }

    protected function registerUser($email, $password, $username, $usertype, $salt, $checkmail, $age, $gender) {
        $result = false;
        if (empty($email)) {
            throw new Angel_Exception_User(Angel_Exception_User::EMAIL_EMPTY);
        } else {
            $validation = new Zend_Validate_EmailAddress();
            if (!$validation->isValid($email)) {
                throw new Angel_Exception_User(Angel_Exception_User::EMAIL_INVALID);
            } else {
                if ($this->isEmailExist($email)) {
                    throw new Angel_Exception_User(Angel_Exception_User::EMAIL_NOT_UNIQUE);
                }
                if ($this->isUsernameExist($username)) {
                    throw new Angel_Exception_User(Angel_Exception_User::USERNAME_NOT_UNIQUE);
                }
            }
        }

        $user = new $this->_document_class();

        $user->email = $email;
        $user->username = $username;
        $user->salt = $salt;
        $user->user_type = $usertype;
        $user->password = $password;
        $user->age = $age;
        $user->gender = $gender;
        $user->active_bln = true;
        $user->email_validated_bln = !$checkemail;
        $user->validated_bln = false;

        try {
            $this->_dm->persist($user);
            $this->_dm->flush();

            $result = $user->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }

        // send email to the new user to notice him to active his account
        if ($result && $checkemail) {
            $this->sendAccountValidationEmail($user);
        }
        // send email to the new user to welcome them
        if ($result && !$checkemail) {
            
        }
        return $result;
    }
    
    public function saveUser($uesrId, $category) {
         try {
            $user = $this->getUserById($uesrId);
   
            $email = $user->email;
            $username = $user->username;
            $salt = $user->salt;
            $usertype = $user->user_type;
            $password = $user->password_src;
            $age = $user->age;
            $gender = $user->gender;
            $active_bln = true;
            $email_validated_bln = !$checkemail;
            $validated_bln = false;
            $author = $user->author;
            
            $user->clearCategory();

            foreach ($category as $c) {
                $user->addCategory($c);
            }

            $data = array("email" => $email, "username" => $username, "salt" => $salt, "user_type" => $usertype, "password" => $password, "age"=> $age, "gender"=>$gender, "active_bln" => $active_bln, "email_validated_bln" => $email_validated_bln, "validated_bln" => $validated_bln, "category"=> $user->category, "author"=> $author);

            $this->save($uesrId, $data);
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }
    }
    
    public function removeUserCategory($user_id, $category_id) {
        try {
            $user = $this->getUserById($uesrId);
   
            $email = $user->email;
            $username = $user->username;
            $salt = $user->salt;
            $usertype = $user->user_type;
            $password = $user->password_src;
            $age = $user->age;
            $gender = $user->gender;
            $active_bln = true;
            $email_validated_bln = !$checkemail;
            $validated_bln = false;
            $author = $user->author;
            
            $categorys = array();
            
            foreach ($user->category as $c) {
                if ($c ->id != $category_id)
                    $categorys[] = $c;
            }
            
            $user->clearCategory();

            foreach ($categorys as $c) {
                $user->addCategory($c);
            }

            $data = array("email" => $email, "username" => $username, "salt" => $salt, "user_type" => $usertype, "password" => $password, "age"=> $age, "gender"=>$gender, "active_bln" => $active_bln, "email_validated_bln" => $email_validated_bln, "validated_bln" => $validated_bln, "category"=> $user->category, "author"=> $author);

            $this->save($uesrId, $data);
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }
    }
    
    public function setAuthor($user_id) {
        try {
            $user = $this->getUserById($uesrId);
   
            $email = $user->email;
            $username = $user->username;
            $salt = $user->salt;
            $usertype = $user->user_type;
            $password = $user->password_src;
            $age = $user->age;
            $gender = $user->gender;
            $active_bln = true;
            $email_validated_bln = !$checkemail;
            $validated_bln = false;
            $author = 1;
            
            $user->clearCategory();

            foreach ($category as $c) {
                $user->addCategory($c);
            }

            $data = array("email" => $email, "username" => $username, "salt" => $salt, "user_type" => $usertype, "password" => $password, "age"=> $age, "gender"=>$gender, "active_bln" => $active_bln, "email_validated_bln" => $email_validated_bln, "validated_bln" => $validated_bln, "category"=> $user->category, "author"=> $author);

            $this->save($uesrId, $data);
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::ADD_USER_FAIL);
        }
    }
    
    public function getUserByEmail($email) {
        $result = false;
        $user = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('email')->equals($email)
                ->getQuery()
                ->getSingleResult();

        if (!empty($user)) {
            $result = $user;
        }

        return $result;
    }

    /**
     * 验证某email地址是否已经存在了
     * 
     * @param string $email － 需要被检测的email地址
     * @return boolean 
     */
    public function isEmailExist($email, $return_user_model = false) {
        $result = false;
        $user = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('email')->equals($email)
                ->getQuery()
                ->getSingleResult();

        if (!empty($user)) {
            if ($return_user_model) {
                $result = $user;
            } else {
                $result = true;
            }
        }

        return $result;
    }

    public function isUsernameExist($username, $return_user_model = false) {
        $result = false;
        $user = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('username')->equals($username)
                ->getQuery()
                ->getSingleResult();

        if (!empty($user)) {
            if ($return_user_model) {
                $result = $user;
            } else {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * 根据id获取user document
     * 
     * @param string $id
     * @return mix - when the user found, return the user document
     */
    public function getUserById($id) {
        $result = false;
        
        $user = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();

        if (!empty($user)) {
            $result = $user;
        }

        return $result;
    }

    /**
     * 发送新注册帐号的验证邮件
     * @param \Documents\User $user 
     */
    public function sendAccountValidationEmail($user) {
        $token = md5(uniqid(time(), true));
        $tokenModel = new Angel_Model_Token($this->_bootstrap);

        $token = $tokenModel->generateToken($token, $this->_bootstrap_options['token']['expiry']['email_validation'], Zend_Json::encode(array('user_id' => $user->id)));

        if ($token) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $params = array();
            $params['url'] = $this->_bootstrap_options['site']['domainurl'] . $router->assemble(array('token' => $token->token), 'email-validation');
            $params['username'] = $user->username;

            Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_NEW_USER_EMAIL_VALIDATION, $user->email, $params);
        }
    }

    /**
     * 记录用户最后一次登录的信息
     * @param string $user_id
     * @param string $ip 
     */
    public function updateLoginInfo($user_id, $ip) {
        $result = false;
        $data = array();

        $data['ip'] = $ip;
        $data['last_login'] = new DateTime();

        $user = $this->updateUser($user_id, $data);
        if ($user) {
            $result = true;
        }

        return $result;
    }

    /**
     * 修改用户信息，返回的是用户对象
     * @param mix $user - string 为用户id，model为用户对象
     * @param array $data
     * @return mix - when success, returns the user model, when fail, return false
     * @throws Angel_Exception_User 
     */
    public function updateUser($user, $data) {
        $result = false;

        if (!(is_object($user) && ($user instanceof $this->_document_class))) {
            $user = $this->validateUserId($user);
        }

        try {
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }

            $this->_dm->persist($user);
            $this->_dm->flush();

            $result = $user;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_User(Angel_Exception_User::USER_UPDATE_FAIL);
        }

        return $result;
    }

    /**
     * 验证用户ID是否有效，这个方法算很多方法的 preValidation
     * @param string $user_id
     * @return user model
     * @throws Angel_Exception_User 
     */
    public function validateUserId($user_id) {
        $user = $this->getUserById($user_id);
        if (!$user) {
            throw new Angel_Exception_User(Angel_Exception_User::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * 生成remember me中的cookie值
     * @param string $user_id
     * @return string
     */
    public function getRememberMeValue($user_id, $ip) {
        $user = $this->validateUserId($user_id);

        $token = md5(uniqid(time(), true));
        $tokenModel = new Angel_Model_Token($this->_bootstrap);

        $token = $tokenModel->generateToken($token, $this->_bootstrap_options['token']['expiry']['remember_me'], Zend_Json::encode(array('user_id' => $user_id, 'ip' => $ip)));

        return $token->token;
    }

    /**
     * 根据remember me提供的cookie value，来激活用户的session
     * @param type $id
     * @param type $salt 
     */
    public function isRemembered($token, $ip) {
        $result = false;

        $tokenModel = new Angel_Model_Token($this->_bootstrap);
        $tokenDocument = $tokenModel->getTokenByToken($token);

        if ($tokenDocument) {
            $params = $tokenDocument->params;

            if ($params['ip'] == $ip) {
                Zend_Auth::getInstance()->getStorage()->write($params['user_id']);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * 激活帐号
     * @param string $token
     * @return 当激活成功时，返回的是用户document
     * @throws Angel_Exception_User 
     */
    public function activateAccount($token) {
        $result = 0;
        $user = null;

        $tokenModel = new Angel_Model_Token($this->_bootstrap);
        $tokenDocument = $tokenModel->getTokenByToken($token);

        // check whether the token is valid
        if ($tokenDocument && $tokenDocument->isActive()) {

            // the tokendocument should provides necessary parameter
            $params = $tokenDocument->getParams();
            if (isset($params['user_id'])) {

                // the user should be valid
                $user = $this->getUserById($params['user_id']);
                if ($user) {

                    // check whether the user's email has been already validated
                    if (!$user->isEmailValidated()) {

                        // check whether the token is expired, if it is expired, need to let the user ask for sending another validation email
                        if ($tokenDocument->isExpired()) {
                            $result = 2;

                            Zend_Auth::getInstance()->getStorage()->write($user->id);
                        } else {
                            $user->email_validated_bln = true;
                            $this->_dm->persist($user);

                            $tokenDocument->active_bln = false;
                            $this->_dm->persist($tokenDocument);

                            $this->_dm->flush();
                            $result = true;
                        }
                    } else {
                        // the user has already been activated
                        $result = 1;
                    }
                }
            }
        }

        if ($result === true) {
            return $user;
        }
        if ($result == 0) {
            throw new Angel_Exception_User(Angel_Exception_User::EMAIL_VALIDATION_TOKEN_INVALID);
        } else if ($result == 1) {
            throw new Angel_Exception_User(Angel_Exception_User::EMAIL_VALIDATION_VALIDATED_USER);
        } else if ($result == 2) {
            throw new Angel_Exception_User(Angel_Exception_User::EMAIL_VALIDATION_TOKEN_EXPIRED);
        } else {
            $this->_logger->info(__CLASS__, __FUNCTION__, $token . " is strange");
        }
    }

    /**
     * 重新设置密码
     * @param string $user_id - 用户ID
     * @param string $old － 旧密码
     * @param string $new － 新密码
     */
    public function resetPassword($user_id, $old, $new) {
        $result = 0;
        $user = $this->validateUserId($user_id);

        if ($user->password == crypt($old, $user->salt)) {
            $user->password = $new;

            $this->_dm->persist($user);
            $this->_dm->flush();

            $result = 1;
        } else {
            throw new Angel_Exception_User(Angel_Exception_User::INCORRECT_ORIGINAL_PASSWORD);
        }

        return $result;
    }

    /**
     * 当用户忘记了密码
     * @param string $email
     * @return boolean - true 发送给用户系统生成密码， false 没有发送系统生成密码，可能是email地址不正确
     */
    public function forgotPassword($email) {
        $result = false;

        $user = $this->isEmailExist($email, true);
        if (is_object($user)) {
            $password = substr(md5(uniqid(time(), true)), rand(0, 16), 8);
            $user->password = $password;
            $params = array("username" => $user->username, "password" => $password);
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $params['url'] = $this->_bootstrap_options['site']['domainurl'] . $router->assemble(array(), 'login');
            Angel_Model_Email::setCompanyName($this->_bootstrap_options['site']['name']);
            $result = Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_FORGOT_PASSWORD, $user->email, $params);

            if ($result) {
                $this->_dm->persist($user);
                $this->_dm->flush();
            }
        } else {
            throw new Angel_Exception_User(Angel_Exception_User::USER_NOT_FOUND);
        }

        return $result;
    }

    /**
     * 上传了用户头像的图片，从tmp处copy到profile image目录，生成其resized version供将来裁剪使用， 并修改user的profile_image field
     * @param type $user
     * @param type $image
     * @return boolean - when it is false, add profile image fails, when the image type is not correct, throw the exception. when it is ture, means success
     */
    public function addProfileImage($user, $image) {
        $result = false;

        if (is_object($user) && ($user instanceof $this->_document_class)) {
            $imageService = $this->_container->get('image');
            if (!$imageService->isAcceptedImage($image)) {
                throw new Angel_Exception_Common(Angel_Exception_Common::IMAGE_NOT_ACCEPTED);
            } else {
                $extension = $imageService->getImageTypeExtension($image);
                $utilService = $this->_container->get('util');
                $filename = $utilService->generateFilename($extension);
                $destination = $this->getProfileImagePath($filename);
                if (copy($image, $destination)) {
                    if ($imageService->resizeImage($destination)) {
                        $user->profile_image = $filename;

                        $this->_dm->persist($user);
                        $this->_dm->flush();

                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 获取profile image的位置 (＊这个方法还需要根据environment不同做修改)
     * @param string $imagename
     * @return string 
     */
    public function getProfileImagePath($imagename) {
        return APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . $this->_bootstrap_options['image']['profile_path'] . DIRECTORY_SEPARATOR . $imagename;
    }

    /**
     * 裁剪用户上传的头像
     * @param string $orig_image - 被裁图片的路径
     * @param array $coord - array(x, y, w, h) 
     * @return mixed 
     */
    public function generateProfileThumbnail($orig_image, $coord) {
        $imageService = $this->_container->get('image');
        return $imageService->generateThumbnail($orig_image, $this->_bootstrap_options['size']['profile'], $coord);
    }

    /**
     * 获取头像图片名字
     * @param string $image － 基本文件名称
     * @param int $version - 文件size
     * @return string 
     */
    public function getProfileImage($image, $version) {
        $imageService = $this->_container->get('image');
        return $imageService->generateImageFilename($this->getProfileImagePath($image), $version, false);
    }

    /**
     * 添加用户的一些文档，比如个人的信用文件，身份证的正反
     * @param \Documents\User $user
     * @param string $filetype - 对应的angelhere的文件类型
     * @param string $filepath - 上传的文件地址
     * @param string $filename - 上传的文件名
     * @return mix, when the file is added success, return the \Documents\UserDoc document
     */
    public function addUserDoc(\Documents\User $user, $filetype, $filepath, $filename, $mimetype = '') {
        $utilService = $this->_container->get('util');

        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (empty($extension)) {
            $fileService = $this->_container->get('file');
            $extension = $fileService->getExtensionByMinetype($mimetype);
        }
        $angelname = $utilService->generateFilename($extension);

        $destination = APPLICATION_PATH . '/../public' . $this->_bootstrap_options['file']['user_doc'] . DIRECTORY_SEPARATOR . $angelname;

        $result = false;
        if (copy($filepath, $destination)) {
            switch ($filetype) {
                case self::FILETYPE_IDENTITY_FRONT:
                    $result = $user->addIdentityFrontDoc($filename, $angelname);
                    break;
                case self::FILETYPE_IDENTITY_BACK;
                    $result = $user->addIdentityBackDoc($filename, $angelname);
                    break;
            }


            $this->_dm->persist($user);
            $this->_dm->flush();
        }

        return $result;
    }

    /**
     * 根据文档的类型，返回用户的此文档
     * @param string $user_id
     * @param string $doctype
     * @param string $doc_id － 此文档的ID
     * @return instance of \documents\userdoc 
     */
    public function getUserDoc($user_id, $doctype, $doc_id = null) {
        $user = $this->validateUserId($user_id);

        $doc = null;

        switch (strtolower($doctype)) {
            case self::FILETYPE_IDENTITY_FRONT:
                $doc = $user->identity_front_doc;
                break;
            case self::FILETYPE_IDENTITY_BACK:
                $doc = $user->identity_back_doc;
                break;
        }

        if ($doc && is_string($doc_id)) {
            if (is_array($doc)) {
                $target_doc = null;
                foreach ($doc as &$temp) {
                    if ($temp->id == $doc_id) {
                        $target_doc = $temp;
                        break;
                    }
                }
                $doc = $target_doc;
            } else {
                if ($doc->id != $doc_id) {
                    $doc = null;
                }
            }

            if ($doc) {
                $doc->path = APPLICATION_PATH . '/../public' . $this->_bootstrap_options['file']['user_doc'] . DIRECTORY_SEPARATOR . $doc->angelname;
            }
        }

        return $doc;
    }

    /**
     * 更新用户的个人身份信息
     * @param string $user_id - user model id
     * @param string $username - 姓名
     * @param string $identity_id - 身份证
     * @param string $phone - 电话号码
     * @param string $address - 地址
     * @param boolean $wait_tobe_validate － 是否修改的信息需要被管理员审核
     * @return boolean false (fails) or user document (success)
     * @throws Angel_Exception_User 
     */
    public function updateIndentityInfo($user_id, $username, $identity_id, $phone, $address, $wait_tobe_validate = true) {
        $result = false;
        $data = array();

        if (empty($username)) {
            throw new Angel_Exception_User(Angel_Exception_User::USERNAME_REQUIRED);
        }
        $data['username'] = $username;

        if (empty($identity_id)) {
            throw new Angel_Exception_User(Angel_Exception_User::IDENTITY_ID_REQUIRED);
        }
        $data['identity_id'] = $identity_id;

        if (empty($phone)) {
            throw new Angel_Exception_User(Angel_Exception_User::PHONE_REQUIRED);
        }
        $data['phone'] = $phone;

        if (empty($address)) {
            throw new Angel_Exception_User(Angel_Exception_User::ADDRESS_REQUIRED);
        }
        $data['address'] = $address;

        $data['wait_tobe_validate'] = $wait_tobe_validate;

        $user = $this->updateUser($user_id, $data);
        if ($user && $wait_tobe_validate) {
            // 通知管理员
            Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_REALNAME_VALIDATE_ADMIN, $this->_bootstrap_options['mail']['admin'], array('email' => $user->email));
            $result = $user;
        }

        return $result;
    }

    /**
     * 返回等待身份信息需验证的用户
     * @return zend paginator or query dataset
     */
    public function getAllUsersInWaitTobeValidatedList($return_as_paginator = true) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('wait_tobe_validate')->equals(true)
                ->field('active_bln')->equals(true)
                ->sort('createdAt', 'asc');

        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }

        return $result;
    }

    /**
     * 实名认证未通过审核
     * @param string $user_id
     * @param string $reason － 拒绝的原因
     * @return boolean - actually if no exception was thrown, it always return true
     * @throws Angel_Exception_User
     * @throws Angel_Exception_Admin 
     */
    public function refuseIdentityInfo($user_id, $reason) {
        $user = $this->validateUserId($user_id);

        // 必须提供拒绝的原因
        if (empty($reason)) {
            throw new Angel_Exception_Admin(Angel_Exception_Admin::REFUSE_IDENTITY_INFO_REASON_REQUIRED);
        }

        $user->wait_tobe_validate = false;
        $user->addIdentityRefusedReason($reason);

        $this->_dm->persist($user);
        $this->_dm->flush();

        $params = array(
            'name' => $user->username,
            'reason' => $reason
        );

        Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_IDENTITY_INFO_REFUSED, $user->email, $params);

        return true;
    }

    /**
     * 审核通过用户的实名认证
     * @param string $user_id
     * @return boolean - actually if no exception was thrown, it always return true
     */
    public function acceptIdentityInfo($user_id) {
        $user = $this->validateUserId($user_id);

        $user->validated_bln = true;
        $user->wait_tobe_validate = false;

        $this->_dm->persist($user);
        $this->_dm->flush();

        $params = array(
            'name' => $user->username,
            'is_investor' => $user->isInvestor()
        );
        Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_IDENTITY_INFO_ACCEPTED, $user->email, $params);

        return true;
    }

    /**
     * 关注用户，创业者，投资人
     * @param String $user_id - 关注人的ID
     * @param String $target_user_id － 被关注人的ID
     */
    public function followUser($user_id, $target_user_id) {
        $user = $this->validateUserId($user_id);
        $target_user = $this->validateUserId($target_user_id);

        $type = \Documents\Follow::FOLLOW_TARGET_STARTUP;
        if ($target_user->isInvestor()) {
            $type = \Documents\Follow::FOLLOW_TARGET_INVESTOR;
        }

        $follow = new \Documents\Follow();
        $follow->user = $user;
        $follow->target_type = $type;
        $follow->target_user = $target_user;

        $this->_dm->persist($follow);
        $this->_dm->flush();

        $result = $this->getModel('feed')->recordFollowUserFeed($user, $target_user);

        return $result;
    }

    public function getUserByCategoryId($category_id) {
        $result = false;
        
        if ($category_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('category.$id')->equals(new MongoId($category_id))
                    ->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
}
