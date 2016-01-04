<?php

class Angel_UserController extends Angel_Controller_Action {

    public function init() {
        $this->_helper->layout->setLayout('normal');
        parent::init();
    }

    public function indexAction() {
        $usermodel = $this->getModel('user');
        $this->view->user = $this->me->getUser();

        if ($this->me->isStartup()) {
            $this->startUpIndex();
        } else {
            $this->investorIndex();
        }
    }

    public function hobbyAction() {
        $categoryModel = $this->getModel('category');
        $userModel = $this->getModel('user');
        
        $uid = $this->me->getUser()->id;//$this->request->getParam('uid');
        
        $user = $userModel->getUserById($uid);
               
        $this->view->model = $user;
        $this->view->userId = $uid;
        $this->view->categories = $categoryModel->getAll(false);
        $this->view->title = "我的兴趣";    
    }

    /**
     * 更改保存用户收看/收听习惯
     */
    public function playerModeAction() {
        if ($this->request->isPost()) {
            $key = "player_mode";
            $value = $this->request->getParam('value');
            $result = false;
            if (in_array($value, array('audio', 'video'))) {
                $result = $userMode = $this->getModel('user')->setAttribute($this->me->getUser(), $key, $value);
            }

            // JSON FORMAT RESPONSE
            $code = 200;
            if (!result) {
                $code = 500;
            }
            $this->_helper->json(array('code' => $code));
        }
    }

    private function startUpIndex() {
        $company = $this->getModel('company')->getCompanyByUser($this->me->getId());
        $this->view->company = $company;
        $this->_helper->viewRenderer->render('startup-index');
    }

    public function myCompanyAction() {
        $company = $this->getModel('company')->getCompanyByUser($this->me->getId());
        $this->view->company = $company;
    }

    private function investorIndex() {
        $this->view->funding_companies = $this->getModel('company')->getCompanyInFunding();

        $this->_helper->viewRenderer->render('investor-index');
    }

    public function resetPasswordAction() {
        if ($this->request->isPost()) {
            $old_pwd = $this->_request->getParam('password');
            $new_pwd = $this->_request->getParam('new-password');

            try {
                $result = $this->getModel('user')->resetPassword($this->me->getId(), $old_pwd, $new_pwd);
            } catch (\Angel_Exception_User $e) {
                $result = $e->getDetail();
            }

            if ($result == 1) {
                $this->view->reset = 'success';
            } else {
                $this->view->error = $result;
            }
        }
        $this->view->title = "修改密码";
    }

    public function personalThumbnailAction() {
        $this->_helper->layout->disableLayout();

        $result = 0;
        if ($this->request->isPost()) {
            $upload = new Zend_File_Transfer();

            $upload->addValidator('Size', false, 5120000); //5M

            $utilService = $this->_container->get('util');
            $destination = $utilService->getTmpDirectory() . '/' . uniqid();

            $upload->addFilter('Rename', $destination);

            if ($upload->isValid()) {
                if ($upload->receive()) {
                    $userModel = $this->getModel('user');
                    try {
                        $result = $userModel->addProfileImage($this->me->getUser(), $destination);
                        if ($result) {
                            $result = 1;
                            $this->view->path = $this->view->url(array('image' => $this->me->getProfileImage()), 'profile-image');
                        }
                    } catch (Exception $e) {
                        // image is not accepted
                        $result = 2;
                    }
                }
            }
        }

        $this->view->result = $result;
    }

    public function cropThumbnailAction() {
        $userModel = $this->getModel('user');

        $orig = $userModel->getProfileImagePath($this->me->getProfileImage());

        $x = $this->request->getParam('x', 0);
        $y = $this->request->getParam('y', 0);
        $w = $this->request->getParam('w', 180);
        $h = $this->request->getParam('h', 180);

        $coord = array($x, $y, $w, $h);
        $userModel->generateProfileThumbnail($orig, $coord);

        $imageurl_large = $this->view->url(array('image' => $userModel->getProfileImage($this->me->getProfileImage(), 180)), 'profile-image');
        $imageurl_small = $this->view->url(array('image' => $userModel->getProfileImage($this->me->getProfileImage(), 50)), 'profile-image');

        $this->_helper->json(array("large" => $imageurl_large, "small" => $imageurl_small));
    }

    /**
     * 上传用户文件的action 
     */
    public function uploadUserDocAction() {
        $this->_helper->layout->disableLayout();
        $doctype = $this->request->getParam('doctype');
        $this->view->doctype = $doctype;

        $result = 0;
        if ($this->request->isPost()) {
            $upload = new Zend_File_Transfer();

            $upload->addValidator('Size', false, 10240000); //10M

            $utilService = $this->_container->get('util');
            $fileService = $this->_container->get('file');

            $filename = $utilService->getFilename($upload->getFileName());
            $extension = $fileService->getExtensionByFilename($filename);

            $destination = $utilService->getTmpDirectory() . DIRECTORY_SEPARATOR . uniqid();

            $upload->addFilter('Rename', $destination);

            if ($upload->isValid()) {
                if ($upload->receive()) {
                    $userModel = $this->getModel('user');

                    $mimetype = $upload->getMimeType();
                    if ($fileService->isAcceptedDocument($mimetype, $extension)) {
                        $user = $userModel->getUserById($this->me->getId());
                        if ($user) {
                            $doc = null;

                            if ($doctype == \Angel_Model_User::FILETYPE_IDENTITY_FRONT || $doctype == \Angel_Model_User::FILETYPE_IDENTITY_BACK) {
                                $doc = $userModel->addUserDoc($user, $doctype, $destination, $filename, $mimetype);
                            }

                            if ($doc) {
                                $result = 1;
                                $this->view->filename = $doc->filename;
                                $this->view->path = $this->view->url(array('doctype' => $doctype, 'user_id' => $user->id, 'doc_id' => $doc->id), 'user-doc');
                            }
                        }
                    } else {
                        // 上传的文件格式不接受
                        $result = 2;
                    }
                }
            }
        }

        $this->view->result = $result;
    }

    /**
     * 当用户查看用户的文档 
     */
    public function userDocAction() {

        $doctype = $this->request->getParam('doctype');
        $user_id = $this->request->getParam('user_id');
        $doc_id = $this->request->getParam('doc_id');

        $doc = $this->getModel('user')->getUserDoc($user_id, $doctype, $doc_id);
        if ($doc && ($fd = fopen($doc->path, 'r'))) {
            $filepath = $doc->path;
            $filename = $doc->filename;

            $this->outputFile($fd, $filepath, $filename);

            fclose($fd);
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        } else {
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
    }

    public function personalInfoAction() {
        $userModel = $this->getModel('user');
        $companyModel = $this->getModel('company');
        if ($this->request->isXmlHttpRequest() && $this->request->isPost()) {
            $data = array();
            $data['username'] = $this->request->getParam('username', '');
            $data['identity_id'] = $this->request->getParam('identity_id', '');
            $data['phone'] = $this->request->getParam('phone', '');
            $data['address'] = $this->request->getParam('address', '');
            $validation = $this->request->getParam('validation', 0);

            $result = false;

            try {
                if ($validation == 1) {
                    $user = $userModel->updateIndentityInfo($this->me->getUser(), $data['username'], $data['identity_id'], $data['phone'], $data['address']);
                } else {
                    $user = $userModel->updateUser($this->me->getUser(), $data);
                }

                if ($user) {
                    $this->me->setUser($user);
                    $result = true;
                }
            } catch (Angel_Exception_User $e) {
                $result = false;
            }

            $this->_helper->json($result ? 1 : 0);
        } else {
            $this->view->type_identity_front = \Angel_Model_User::FILETYPE_IDENTITY_FRONT;
            $this->view->type_identify_back = \Angel_Model_User::FILETYPE_IDENTITY_BACK;
            $this->view->imageUrl = $this->view->profileImage($this->me->getProfileImage(), 'medium');
            $myCompany = $companyModel->getCompanyByUser($this->me->getUser()->id);

            if (count($myCompany) > 0) {
                $com;
                foreach ($myCompany as $myc) {
                    $com = $myc;
                    break;
                }
                $this->view->company_id = $com->id;
            } else {
                $this->view->company_id = false;
            }
        }
    }

    public function userHomeAction() {
        $myself = false;
        $id = $this->request->getParam('id');

        if ($this->me) {
            $my_id = $this->me->getId();
            if ($my_id == $id) {
                // 本人
                $myself = true;
            }
        }
        $userModel = $this->getModel('user');
        $user = $userModel->getUserById($id);
        if (!$user) {
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
        $companyModel = $this->getModel('company');
        $this->view->imageUrl = $this->view->profileImage($user->profile_image, 'main');
        $this->view->user = $user;
        $this->view->myself = $myself;

        if ($user->isStartup()) {
            $this->view->company = $companyModel->getCompanyByUser($user->id);
        }
        if ($user->isInvestor()) {
            $this->view->company = array();
            foreach ($user->invested_companies as $invested_company) {
                $this->view->company[] = $invested_company->company;
            }
        }
    }

    public function profileAction() {
        
    }

}
