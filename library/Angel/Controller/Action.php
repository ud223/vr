<?php

/**
 * Description of Action
 *
 * @author powerdream5
 */
class Angel_Controller_Action extends Zend_Controller_Action {

    protected $bootstrap = null;
    protected $bootstrap_options = null;
    protected $request = null;
    protected $session = null;
    protected $me = null;
    protected $models = array();
    // service container
    protected $_container = null;
    protected $_logger = null;
    protected $login_not_required = array();

    public function init() {
        parent::init();

        $this->bootstrap = $this->getInvokeArg('bootstrap');
        $this->bootstrap_options = $this->bootstrap->getOptions();

        $this->request = $this->getRequest();
        $this->session = new Zend_Session_Namespace();

        // get the DI service container
        $this->_container = $this->bootstrap->getResource('serviceContainer');

        $this->_logger = $this->bootstrap->getResource('logger');

        // diable the layout and set no render when the request is an ajax request
        if ($this->request->isXMLHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        }

        // some global variable
        $this->view->currency = $this->bootstrap_options['currency'];
        $this->view->currency_symbol = $this->bootstrap_options['currency_symbol'];


        $contactModel = $this->getModel('contact');
        $result = $contactModel->getAll(false);
        $count = count($result);
        if ($count > 0) {
            $id = null;
            foreach ($result as $r) {
                $id = $r->id;
                break;
            }
            $target = $contactModel->getById($id);
            $this->view->contact = $target;
        }
    }

    /**
     * 对用户的各项状态进行检测 
     */
    public function preDispatch() {
//        echo $this->request->getActionName(); var_dump($this->login_not_required);exit;
        // 正常情况下的登录和注册地址
        $registerRoute = "register";
        $loginRoute = "login";
        $requestManage = ($this->request->controller == 'manage');
        if ($requestManage) {
            // 后台管理系统的登录和注册地址
            $registerRoute = "manage-register";
            $loginRoute = "manage-login";
        }
        $registerPath = $this->view->url(array(), $registerRoute);
        $loginPath = $this->view->url(array(), $loginRoute) . '?goto=' . $this->request->getRequestUri();

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $this->getModel('user')->getUserById($auth->getIdentity());
            if (!$user) {

                if (!in_array($this->request->getActionName(), $this->login_not_required)) {
                    $auth->clearIdentity();
                    $this->_redirect($loginPath);
                }
            } else {
                $this->me = new Angel_Me($user);
                $this->view->me = $this->me;
                if ($requestManage && $user->user_type != 'admin') {
                    $this->_redirect($this->view->url(array(), 'forbidden'));
                }
            }
        } else {
            if ($this->checkRememberMe() === true) {
                $user = $this->getModel('user')->getUserById($auth->getIdentity());
                $this->me = new Angel_Me($user);
                $this->view->me = $this->me;
                if ($requestManage && $user->user_type != 'admin') {
                    $this->_redirect($this->view->url(array(), 'forbidden'));
                }
            } else {
                if (!in_array($this->request->getActionName(), $this->login_not_required)) {
                    $this->_redirect($loginPath);
                }
            }
        }

        // 如果用户还没被激活，跳转到激活页
//        if ($this->me) {
//            if (!$this->me->isActivated()) {
//                $router = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();
//                if (!($router == 'wait-to-be-activated' || $router == 'email-validation')) {
//                    $this->_redirect($this->view->url(array(), 'wait-to-be-activated'));
//                }
//            }
//        }
    }

    protected function checkRememberMe() {
        $result = false;

        $angel = $this->request->getCookie($this->bootstrap_options['cookie']['remember_me']);
        if (!empty($angel)) {
            $result = $this->getModel('user')->isRemembered($angel, $this->getRealIpAddr());
        }

        return $result;
    }

    /**
     * A helper method to get the model object in controller
     * @param string $modelName
     * @return Model Object 
     */
    protected function getModel($modelName) {
        $modelName = 'Angel_Model_' . ucwords($modelName);
        if (!isset($models[$modelName])) {
            $models[$modelName] = new $modelName($this->bootstrap);
        }

        return $models[$modelName];
    }

    /**
     * 获得访问用户的真实ip
     * @return type 
     */
    protected function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * 当用户下载或查看文件 
     */
    protected function outputFile($fd, $filepath, $filename) {
        $fsize = filesize($filepath);
        $path_parts = pathinfo($filepath);
        $ext = strtolower($path_parts['extension']);

        switch ($ext) {
            case 'pdf':
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                break;
            case 'doc':
            case 'docx':
                header('Content-type: application/msword');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                break;
            case 'xls':
            case 'xlsx':
                header('Content-type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-type: image/jpeg');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                break;
            case 'png':
                header('Content-type: image/png');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                break;
            default:
                header('Content-type: application/octet-stream');
                header('Content-Disposition: filename="' . $filename . '"');
        }
        header('Content-length: ' . $fsize);
        header('Cache-control: private');

        while (!feof($fd)) {
            $buffer = fread($fd, 2048);
            echo $buffer;
        }
    }

    protected function userLogout($defaultRedirectRoute) {
        Zend_Auth::getInstance()->clearIdentity();

        $angel = $this->request->getCookie($this->bootstrap_options['cookie']['remember_me']);
        if (!empty($angel)) {
            $this->getModel('token')->disableToken($angel);
        }

        $this->_redirect($this->view->url(array(), $defaultRedirectRoute));
    }

    protected function userRegister($defaultRedirectRoute, $pageTitle, $userType) {
        $errorMsg = "登录失败，请重试或联系管理员";
        if ($this->request->isPost()) {
            $msg = "注册成功!";
            $code = 200;
            $error = "";
            // POST METHOD
            $email = $this->request->getParam('email');

            if ($email) {
                $email = strtolower($email);
            }

            $username = $this->request->getParam('username');
            $password = $this->request->getParam('password');
            $age = 0;
            $gender = '男';
            
            if ($userType == 'user') {
                $age = $this->request->getParam('age');
                $gender = $this->request->getParam('gender');
            }
            
            $result = false;

            try {
                $userModel = $this->getModel('user');
                $isEmailExist = $userModel->isEmailExist($email);

                if ($isEmailExist) {
                    $error = "该邮箱已经存在，不能重复注册";
                } else {
                    $result = null;

                    if ($userType == 'user') {
                        $result = $userModel->addUser($email, $password, $username,  $age, $gender, Zend_Session::getId(), false);
                    } else if ($userType == 'admin') {
                        $result = $userModel->addManageUser($email, $password,  Zend_Session::getId(), false);
                    } else {
                        $error = "invalid request";
                    }
                }
            } catch (Angel_Exception_User $e) {
                $error = $e->getDetail();
            }

            if ($error != "") {
                $msg = $error;
                $code = 500;
            }

            if ($this->getParam('format') == 'json') {
                $this->_helper->json(array('data' => $msg, 'code' => $code));
            } else {
                if ($result) {
//                    $this->_redirect($this->view->url(array(), $defaultRedirectRoute) . '?register=success');
                    // 直接登录(START)
                    $remember = 'on';
                    try {
                        $userModel = $this->getModel('user');
                        $auth = $userModel->auth($email, $password);

                        if ($auth['valid'] === true) {
                            $ip = $this->getRealIpAddr();
                            $r = $userModel->updateLoginInfo($auth['msg'], $ip);

                            if ($r) {
                                if ($remember == 'on') {
                                    setcookie($this->bootstrap_options['cookie']['remember_me'], $userModel->getRememberMeValue($auth['msg'], $ip), time() + $this->bootstrap_options['token']['expiry']['remember_me'] * 60, '/', $this->bootstrap_options['site']['domain']);
                                }
                                
                                $user = $userModel->getUserByEmail($email);
                                
                                // 跳转至兴趣设置页面
                                $this->_redirect($this->view->url(array(), 'hobby') . '?register=success');//"uid"=> $user->id

                            } else {
                                $this->view->error = $errorMsg;
                            }
                        }
                    } catch (Angel_Exception_User $e) {
                        $this->view->error = $e->getMessage();
                    }
                    // 直接登录(END)
                } else {
                    $this->view->error = $msg;
                }
            }
        }

        // GET METHOD
        $this->view->title = $pageTitle;
    }

    protected function userLogin($defaultRedirectRoute, $pageTitle) {
        $errorMsg = "登录失败，请重试或联系管理员";
        $code = 200;
        $uid = "";

        if ($this->request->isPost()) {
            $email = $this->request->getParam('email');
            if ($email) {
                $email = strtolower($email);
            }
            $password = $this->request->getParam('password');
            // remember's value: on or null
            $remember = $this->request->getParam('remember', 'on');

            try {
                $userModel = $this->getModel('user');
                $auth = $userModel->auth($email, $password);

                $success = false;
                $error = $errorMsg;
                if ($auth['valid'] === true) {
                    $ip = $this->getRealIpAddr();
                    $result = $userModel->updateLoginInfo($auth['msg'], $ip);

                    if ($result) {
                        if ($remember == 'on') {
                            setcookie($this->bootstrap_options['cookie']['remember_me'], $userModel->getRememberMeValue($auth['msg'], $ip), time() + $this->bootstrap_options['token']['expiry']['remember_me'] * 60, '/', $this->bootstrap_options['site']['domain']);
                        }
                        $success = true;
                    }
                }
            } catch (Angel_Exception_User $e) {
                $error = $e->getMessage();
                $errorMsg = $error;
            }

            $url = "";

            if ($success) {
                $goto = $this->getParam('goto');
                $url = $this->view->url(array(), $defaultRedirectRoute);

                if ($goto) {
                    $url = $goto;
                }

                $errorMsg = "success";
                $uid = $auth["msg"];
            } else {
                $code = 500;
            }

            if ($this->getParam('format') == 'json') {
                $this->_helper->json(array('data' => $errorMsg, 'uid' => $uid, 'code' => $code));
            } else {
                if ($code == 200) {
                    $this->_redirect($url);
                } else {
                    $this->view->error = $errorMsg;
                }
            }
        } else {
            if ($this->getParam('register') == 'success') {
                $this->view->register = 'success';
            }
        }

        $this->view->title = $pageTitle;
    }

    protected function isMobile() {
        $mobile = array();
        static $mobilebrowser_list ='Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
        //note 获取手机浏览器
        if(preg_match("/$mobilebrowser_list/i", $_SERVER['HTTP_USER_AGENT'], $mobile)) {
            return true;
        }else{
            if(preg_match('/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT'])) {
                return false;
            }else{
                if($_GET['mobile'] === 'yes') {
                    return true;
                }else{
                    return false;
                }
            }
        }
    }
}
