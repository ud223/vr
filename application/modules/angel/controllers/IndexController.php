<?php

class Angel_IndexController extends Angel_Controller_Action {

    protected $login_not_required = array(
        'index',
        'about',
        'profile',
        'application',
        'error',
        'result',
        'case-list-home',
        'case-list',
        'case-info',
        'product-info',
        'news-list-home',
        'news-list',
        'news-detail',
        'product-list-home',
        'product-list',
        'awards',
        'contact',
        'login',
        'register',
        'email-validation',
        'is-email-can-be-used',
        'forgot-password');

    public function init() {
        $this->_helper->layout->setLayout('normal');
        parent::init();
    }

    public function indexAction() {
        $productModel = $this->getModel('product');
        $newsModel = $this->getModel('news');
        $showModel = $this->getModel('show');
        $profileModel = $this->getModel('companyprofile');
        $classiccase = $this->getModel('classiccase');

        $tmp_products = $productModel->getAll(false);//getLastByCount("4");

        $products = array();

        foreach ($tmp_products as $p) {
            $path = "";

            if (count($p->photo)) {
                try {
                    if ($p->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($p->photo[0]->name . $p->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $products[] = array("id"=>$p->id, "name"=>$p->name, "name_en"=>$p->name_en, "photo"=> $path);
        }

        $tmp_news = $newsModel->getLastByCount("2");

        $news = array();

        foreach ($tmp_news as $n) {
            if ($n->photo && count($n->photo)) {
                try {
                    if ($n->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($n->photo[0]->name . $n->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $news[] = array("id"=>$n->id, "title"=>$n->title, "title_en"=>$n->title_en, "create_date"=>date_format($n->created_at, 'Y年m月d日'), "create_date_en"=>date_format($n->created_at, 'm/d/Y'), "photo"=>$path);
        }

        $tmp_show = $showModel->getAll(false);

        $show = array();

        foreach ($tmp_show as $n) {
            if (count($n->photo)) {
               foreach ($n->photo as $p) {
                   $show[] =  "/photo/image/" . $p->name . $p->type;
               }
            }

            break;
        }

        $tmp_profile = $profileModel->getLastByCount("1");

        $profile = array();

        foreach ($tmp_profile as $n) {
            if (count($n->photo)) {
                try {
                    if ($n->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($n->photo[0]->name . $n->photo[0]->type, '');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $profile[] = array("id"=>$n->id, "simple_content"=>$n->simple_content,  "simple_content_en"=>$n->simple_content_en, "photo"=>$path);

            break;
        }

        $tmp_case = $classiccase->getLastByCount("3");

        $cases = array();

        foreach ($tmp_case as $n) {
            if (count($n->photo)) {
                try {
                    if ($n->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($n->photo[0]->name . $n->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $cases[] = array("id"=>$n->id, "name"=>$n->name, "name_en"=>$n->name_en, "simple_content"=>$n->simple_content, "simple_content_en"=>$n->simple_content_en, "photo"=>$path);
        }

        $this->view->cases = $cases;
        $this->view->profile = $profile;
        $this->view->show = $show;
        $this->view->news = $news;
        $this->view->products = $products;

//
//        $contactModel = $this->getModel('contact');
//        $result = $contactModel->getAll(false);
//        $count = count($result);
//        if ($count > 0) {
//            $id = null;
//            foreach ($result as $r) {
//                $id = $r->id;
//                break;
//            }
//            $target = $contactModel->getById($id);
//            $this->view->contact = $target;
//        }
    }

    /***************************************************************
     * 用户处理
     *
     * *************************************************************/
    public function upgradeAction() {
        $this->_helper->layout->setLayout('upgrade');
    }
    
    public function subscribeAction() {
        if ($this->request->isXmlHttpRequest() && $this->request->isPost()) {
            try {
                $email = $this->request->getParam("email");
                $subscribeModel = $this->getModel('subscribe');
                $subscribeModel->addSubscribe($email);
                echo 1;
                exit;
            } catch (Angel_Exception_User $e) {
                echo 0;
                exit;
            }
        } else {
            
        }
    }

    /**
     * 登录
     */
    public function loginAction() {
        if ($this->request->isPost()) {
            $this->userLogin('show-play', "登录芝士电视");
        }
        else {
            //第一次请求先判断是否移动端浏览器,如果是移动端浏览器就跳转到移动端注册页面
            if ($this->isMobile()) {
                $loginPath = $this->view->url(array(), 'phone-login') ;

                $this->_redirect($loginPath);
            }
        }
    }

    /**
     * 注册
     */
    public function registerAction() {
        if ($this->request->isPost()) {
            $this->userRegister('login', "注册芝士电视", "user");
        }
        else {
            //第一次请求先判断是否移动端浏览器,如果是移动端浏览器就跳转到移动端注册页面
            if ($this->isMobile()) {
                $registerPath = $this->view->url(array(), 'phone-register') ;

                $this->_redirect($registerPath);
            }
        }
    }

    public function isEmailCanBeUsedAction() {
        if ($this->request->isXmlHttpRequest() && $this->request->isPost()) {

            $email = $this->request->getParam('email');
            $result = false;
            try {
                $userModel = $this->getModel('user');
                $result = $userModel->isEmailExist($email);
            } catch (Angel_Exception_User $e) {
                $this->_helper->json(0);
            }
            // email已经存在返回false，不存在返回true
            $this->_helper->json($result ? false : true);
        }
    }

    public function forgotPasswordAction() {
        if ($this->request->isPost()) {
            $email = $this->request->getParam('email');
            $result = false;
            try {
                $userModel = $this->getModel('user');
                $result = $userModel->forgotPassword($email);
            } catch (Angel_Exception_User $e) {
                $this->view->error = $e->getDetail();
                $this->view->re_email = $email;
                $result = false;
            }
            if ($result) {
                $this->view->send = "success";
            }
        }
        $this->view->title = "找回密码";
    }

    public function logoutAction() {
        $this->userLogout('login');
    }

    /**************************************************************
     * 前台产品处理
     *
     * ***********************************************************/
    public function productListAction() {
        $productModel = $this->getModel('product');
        $categoryModel = $this->getModel('category');

        $category_id = $this->getParam('category_id');
        $page = $this->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $paginator = $productModel->getAll();
        $paginator->setItemCountPerPage(9);
        $paginator->setCurrentPageNumber($page);

        $products = array();

        foreach ($paginator as $p) {
            $path = "";

            if ($category_id != "all" && $p->category_id != $category_id)
                continue;

            if (count($p->photo)) {
                try {
                    if ($p->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($p->photo[0]->name . $p->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $products[] = array("id"=>$p->id, "name"=>$p->name, "name_en"=>$p->name_en, "photo"=>$path);
        }

        $this->view->products = $products;
        $this->view->paginator = $paginator;

        $categorys = $categoryModel->getAll(false);

        $this->view->category = "全部";
        $this->view->category_en = "ALL";

        foreach ($categorys as $c) {
            if ($c->id == $category_id) {
                $this->view->category = $c->name;
                $this->view->category_en = $c->name_en;

                break;
            }
        }

        $this->view->category_id = $category_id;
        $this->view->categorys = $categorys;
    }

    public function productInfoAction() {
        $notFoundMsg = '未找到目标产品';
        $productModel = $this->getModel('product');
        $categoryModel = $this->getModel('category');
        $classiccase = $this->getModel('classiccase');

        $id = $this->getParam('id');

        if ($id) {
            $target = $productModel->getById($id);

            if (!$target) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }

            $this->view->model = $target;
            $photo = $target->photo;
            $first_photo = false;

            if ($photo) {
                $saveObj = array();
                foreach ($photo as $p) {
                    try {
                        $name = $p->name;
                    } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                        $this->view->imageBroken = true;
                        continue;
                    }

                    if (!$first_photo) {
                        $first_photo = $this->view->photoImage($p->name . $p->type);
                    }

                    $saveObj[] = $this->view->photoImage($p->name . $p->type, 'small');
                }
                if (!count($saveObj))
                    $saveObj = false;
                $this->view->photo = $saveObj;
            }

            $this->view->first_photo = $first_photo;
            $categorys = $categoryModel->getAll(false);

            foreach ($categorys as $c) {
                if ($c->id == $target->category_id) {
                    $this->view->category_id = $c->id;
                    $this->view->category = $c->name;
                    $this->view->category_en = $c->name_en;

                    break;
                }
            }

            $cases = array();

            $tmp_cases = $classiccase->getAll(false);

            foreach ($tmp_cases as $casae) {
                foreach ($casae->product as $product) {
                    if ($product->id == $target->id) {
                        $case_path = "";

                        if (count($casae->photo)) {
                            try {
                                if ($casae->photo[0]->name) {
                                    $case_path = $this->bootstrap_options['image.photo_path'];

                                    $case_path = $this->view->photoImage($casae->photo[0]->name . $casae->photo[0]->type, 'small');
                                }
                            } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                                // 图片被删除的情况
                            }
                        }

//                        exit($case_path);

                        $cases[] = array("id"=>$casae->id, "name"=>$casae->name, "name_en"=>$casae->name_en, "photo"=>$case_path);

                        break;
                    }
                }
            }

            $this->view->cases = $cases;

            $tmp_other_product = $productModel->getProductByCategory($target->category_id);

            $other_product = array();

            foreach ($tmp_other_product as $p) {
                $path = "";

                if (count($other_product) == 5) {
                    break;
                }

                if ($p->id == $target->id) {
                    continue;
                }

                if (count($p->photo)) {
                    try {
                        if ($p->photo[0]->name) {
                            $path = $this->bootstrap_options['image.photo_path'];

                            $path = $this->view->photoImage($p->photo[0]->name . $p->photo[0]->type, 'main');
                        }
                    } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                        // 图片被删除的情况
                    }
                }

                $other_product[] = array("id"=>$p->id, "name"=>$p->name, "name_en"=>$p->name_en, "photo"=>$path);
            }

            $this->view->other_product = $other_product;
        } else {
            $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
        }
    }

    /***********************************************
     * 案例处理代码
     *
     * *********************************************/
    public function caseListAction() {
        $classiccaseModel = $this->getModel('classiccase');

        $page = $this->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $paginator = $classiccaseModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);//
        $paginator->setCurrentPageNumber($page);

        $cases = array();

        foreach ($paginator as $p) {
            $path = "";

            if (count($p->photo)) {
                try {
                    if ($p->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($p->photo[0]->name . $p->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $cases[] = array("id"=>$p->id, "name"=>$p->name, "name_en"=>$p->name_en, "photo"=>$path);
        }

        $this->view->resource = $cases;
        $this->view->paginator = $paginator;
    }

    public function caseInfoAction() {
        $notFoundMsg = '未找到目标案例';
        $productModel = $this->getModel('product');
        $classiccaseModel = $this->getModel('classiccase');

        $id = $this->getParam('id');

        if ($id) {
            $target = $classiccaseModel->getById($id);

            if (!$target) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }

            $this->view->model = $target;
            $photo = $target->photo;

            $path = "";

            if (count($photo)) {
                try {
                    if ($photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($photo[0]->name . $photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $this->view->first_photo = $path;

            $products = array();
            $count = 0;

            foreach ($target->product as $p) {
                if ($count == 6) {
                    break;
                }

                $path = "";

                if (count($p->photo)) {
                    try {
                        if ($p->photo[0]->name) {
                            $path = $this->bootstrap_options['image.photo_path'];

                            $path = $this->view->photoImage($p->photo[0]->name . $p->photo[0]->type, 'main');
                        }
                    } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                        // 图片被删除的情况
                    }
                }

                $products[] = array("id"=>$p->id, "name"=>$p->name, "name_en"=>$p->name_en, "photo"=>$path);
                $count++;
            }
//            var_dump(($products)); exit;
            $this->view->products = $products;
        } else {
            $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
        }
    }

    /************************************************
     * 应用处理
     *
     * *********************************************/
    public function applicationAction() {

    }

    /************************************************
     * 网站新闻处理部分
     *
     * **********************************************/
    public function newsListAction() {
        $newsModel = $this->getModel('news');

        $page = $this->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $paginator = $newsModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);

        $resource = array();

        foreach ($paginator as $p) {

            $photo = $p->photo;
            $first_photo = false;

            if ($photo) {
                foreach ($photo as $px) {
                    $first_photo = $this->view->photoImage($px->name . $px->type);
                }
            }

            $resource[] = array(
                'id' => $p->id,
                'title' => $p->title,
                'title_en' => $p->title_en,
                'subtitle' => $p->subtitle,
                'subtitle_en' => $p->subtitle_en,
                'date'=>date_format($p->created_at, "Y年m月d日"),
                'date_en'=>date_format($p->created_at, "m/d/Y"),
                'photo' => $first_photo
            );
        }

        $this->view->resource = $resource;
        $this->view->paginator = $paginator;
    }

    public function newsDetailAction() {
        $notFoundMsg = '未找到目标新闻';
        $newModel = $this->getModel('news');

        $id = $this->getParam('id');

        if ($id) {
            $target = $newModel->getById($id);

            if (!$target) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }

            $this->view->model = $target;
            $photo = $target->photo;
            $first_photo = false;

            if ($photo) {
                $saveObj = array();
                foreach ($photo as $p) {
                    try {
                        $name = $p->name;
                    } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                        $this->view->imageBroken = true;
                        continue;
                    }

                    if (!$first_photo) {
                        $first_photo = $this->view->photoImage($p->name . $p->type);
                    }

                    $saveObj[] = $this->view->photoImage($p->name . $p->type, 'main');
                }
                if (!count($saveObj))
                    $saveObj = false;
                $this->view->photo = $saveObj;
            }

            $this->view->first_photo = $first_photo;
        } else {
            $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
        }
    }

    /*******************************************************
     * 其他action
     *
     * *****************************************************/
    public function awardsAction() {

    }

    public function profileAction() {
        $profileModel = $this->getModel('companyprofile');

        $tmp_profile = $profileModel->getLastByCount("1");

        $profile = array();

        foreach ($tmp_profile as $n) {
            $profile[] = array("id"=>$n->id, "title"=>$n->title, "title_en"=>$n->title_en, "content"=>$n->content, "content_en"=>$n->content_en);

            break;
        }

        $this->view->model = $profile;
    }

    public function aboutAction() {
//        $aboutModel = $this->getModel('about');
//
//        $tmp_about = $aboutModel->getLastByCount("1");
//
//        $about = array();
//
//        foreach ($tmp_about as $n) {
//            $about[] = array("id"=>$n->id, "title"=>$n->title, "title_en"=>$n->title_en, "content"=>$n->content, "content_en"=>$n->content_en);
//
//            break;
//        }
//
//        $this->view->model = $about;

        $profileModel = $this->getModel('companyprofile');

        $tmp_profile = $profileModel->getLastByCount("1");

        $profile = array();

        foreach ($tmp_profile as $n) {
            $path = "";

            if (count($n->photo)) {
                try {
                    if ($n->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($n->photo[0]->name . $n->photo[0]->type, 'main');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $profile[] = array("id"=>$n->id, "title"=>$n->title, "title_en"=>$n->title_en, "content"=>$n->content, "content_en"=>$n->content_en, "photo"=>$path);

            break;
        }

        $this->view->model = $profile;
    }

    public function contactAction() {
        $contactModel = $this->getModel('contact');

        $result = $contactModel->getAll(false);

        $count = count($result);

        if ($count == 0) {

        }
        else {
            $id = null;

            foreach ($result as $r) {
                $id = $r->id;

                break;
            }

            $target = $contactModel->getById($id);

            if($this->request->getParam("type") == 'json') {
                $this->_helper->json($target);
            } else {
                $this->view->model = $target;
            }

        }
    }
}
