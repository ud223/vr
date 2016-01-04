<?php

class Angel_ManageController extends Angel_Controller_Action {

    protected $login_not_required = array(
        'login',
        'register',
        'logout'
    );
    protected $SEPARATOR = ';';

    public function init() {
        parent::init();

        $this->_helper->layout->setLayout('manage');
    }

    public function indexAction() {
        
    }

    /*******************************************
     * 用户处理部分
     *
     * ****************************************/
    public function registerAction() {
        $this->userRegister('manage-login', "注册成为管理员", "admin");
        
        $this->view->ismanage = true;
    }

    public function logoutAction() {
        $this->userLogout('manage-login');
    }

    public function loginAction() {
        $this->userLogin('manage-index', "管理员登录");
    }



    /**********************************************************
     * 图片处理action部分
     *
     * ********************************************************/
    protected function decodePhoto($paramName = 'photo') {
        $paramPhoto = $this->request->getParam($paramName);
        if ($paramPhoto) {
            $paramPhoto = json_decode($paramPhoto);
            $photoModel = $this->getModel('photo');
            $photoArray = array();
            foreach ($paramPhoto as $name => $path) {
                $photoObj = $photoModel->getPhotoByName($name);
                if ($photoObj) {
                    $photoArray[] = $photoObj;
                }
            }
            return $photoArray;
        } else {
            return null;
        }
    }

    public function photoCreateAction() {
        $phototypeModel = $this->getModel('phototype');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $tmp = $this->getParam('tmp');
            $title = $this->getParam('title');
            $description = $this->getParam('description');
            $phototypeId = $this->getParam('phototype');
            $thumbnail = $this->getParam('thumbnail') == "1" ? true : false;

            $phototype = null;
            if ($phototypeId) {
                $phototype = $phototypeModel->getById($phototypeId);
                if (!$phototype) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error="notfound"');
                }
            }
            $owner = $this->me->getUser();
            $photoModel = $this->getModel('photo');
            try {
                $destination = $this->getTmpFile($tmp);
                $result = $photoModel->addPhoto($destination, $title, $description, $phototype, $thumbnail, $owner);
                if ($result) {
                    $result = 1;
                }
            } catch (Exception $e) {
                // image is not accepted
                $result = 2;
            }
            echo $result;
            exit;
        } else {
            // GET METHOD
            $fs = $this->getParam('fs');

            if ($fs) {
                $this->view->fileList = array();
                $f = explode("|", $fs);
                foreach ($f as $k => $v) {
                    $this->view->fileList[] = array('v' => $v, 'p' => $this->getTmpFile($v));
                }
            }
            $this->view->title = "确认保存图片";
            $this->view->phototype = $phototypeModel->getAll(false);
        }
    }

    public function photoUploadAction() {
        if ($this->request->isPost()) {
            // POST METHOD
            $result = 0;
            $upload = new Zend_File_Transfer();

            $upload->addValidator('Size', false, 5120000); //5M

            $uid = uniqid();
            $destination = $this->getTmpFile($uid);

            $upload->addFilter('Rename', $destination);

            if ($upload->isValid()) {
                if ($upload->receive()) {
                    $result = $uid;
                }
            }
            echo $result;
            exit;
        } else {
            // GET METHOD
            $this->view->title = "上传图片";
        }
    }

    public function photoClearcacheAction() {
        if ($this->request->isPost()) {
            // POST METHOD
            $result = 0;
            $utilService = $this->_container->get('util');
            $tmp = $utilService->getTmpDirectory();

            try {
                if ($od = opendir($tmp)) {
                    while ($file = readdir($od)) {
                        unlink($tmp . DIRECTORY_SEPARATOR . $file);
                    }
                }
                $result = 1;
            } catch (Exception $e) {
                $result = 0;
            }
            echo $result;
            exit;
        }
    }

    public function photoListAction() {
        $page = $this->request->getParam('page');
        $phototype = $this->request->getParam('phototype');
        if (!$page) {
            $page = 1;
        }
        $photoModel = $this->getModel('photo');

        $paginator = null;
        if (!$phototype) {
            $paginator = $photoModel->getAll();
        } else {
            $paginator = $photoModel->getPhotoByPhototype($phototype);
        }
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);
        $resource = array();
        foreach ($paginator as $r) {
            $resource[] = array('path' => array('orig' => $this->view->photoImage($r->name . $r->type), 'main' => $this->view->photoImage($r->name . $r->type, 'main'), 'small' => $this->view->photoImage($r->name . $r->type, 'small'), 'large' => $this->view->photoImage($r->name . $r->type, 'large')),
                'name' => $r->name,
                'id' => $r->id,
                'type' => $r->type,
                'thumbnail' => $r->thumbnail,
                'owner' => $r->owner);
        }
        // JSON FORMAT
        if ($this->getParam('format') == 'json') {
            $this->_helper->json(array('data' => $resource,
                'code' => 200,
                'page' => $paginator->getCurrentPageNumber(),
                'count' => $paginator->count()));
        } else {
            $this->view->paginator = $paginator;
            $this->view->resource = $resource;
            $this->view->title = "图片列表";
            $this->view->specialModel = $this->getModel('special');
            $this->view->authorModel = $this->getModel('author');
        }
    }

    public function photoSaveAction() {
        $notFoundMsg = '未找到目标图片';
        $photoModel = $this->getModel('photo');
        $phototypeModel = $this->getModel('phototype');
        $id = $this->request->getParam('id');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $title = $this->request->getParam('title');
            $description = $this->request->getParam('description');
            $phototypeId = $this->request->getParam('phototype');
            $phototype = null;
            if ($phototypeId) {
                $phototype = $phototypeModel->getById($phototypeId);
                if (!$phototype) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error="notfound"');
                }
            }
            try {
                $result = $photoModel->savePhoto($id, $title, $description, $phototype);
            } catch (Angel_Exception_Photo $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-photo-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑图片";

            if ($id) {
                $target = $photoModel->getById($id);
                $phototype = $phototypeModel->getAll(false);
                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }
                $this->view->model = $target;
                $this->view->phototype = $phototype;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    public function photoRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $photoModel = $this->getModel('photo');
                $result = $photoModel->removePhoto($id);
            }
            echo $result;
            exit;
        }
    }

    /***************************************************
     * 图片类型acton部分
     *
     * ************************************************/
    public function phototypeListAction() {
        $page = $this->request->getParam('page');
        if (!$page) {
            $page = 1;
        }
        $phototypeModel = $this->getModel('phototype');
        $photoModel = $this->getModel('photo');
        $paginator = $phototypeModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);
        $resource = array();
        foreach ($paginator as $r) {
            $resource[] = array('id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'owner' => $r->owner);
        }
        // JSON FORMAT
        if ($this->getParam('format') == 'json') {
            $this->_helper->json(array('data' => $resource,
                'code' => 200,
                'page' => $paginator->getCurrentPageNumber(),
                'count' => $paginator->count()));
        } else {
            $this->view->paginator = $paginator;
            $this->view->resource = $resource;
            $this->view->title = "图片分类列表";
            $this->view->photoModel = $photoModel;
        }
    }

    public function phototypeCreateAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $name = $this->request->getParam('name');
            $description = $this->request->getParam('description');
            $owner = $this->me->getUser();
            $phototypeModel = $this->getModel('phototype');
            try {
                $result = $phototypeModel->addPhototype($name, $description, $owner);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-phototype-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "创建图片分类";
        }
    }

    public function phototypeSaveAction() {
        $notFoundMsg = '未找到目标图片分类';

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->request->getParam('id');
            $name = $this->request->getParam('name');
            $description = $this->request->getParam('description');
            $phototypeModel = $this->getModel('phototype');
            try {
                $result = $phototypeModel->savePhototype($id, $name, $description);
            } catch (Angel_Exception_Phototype $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-phototype-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑图片分类";

            $id = $this->request->getParam("id");
            if ($id) {
                $phototypeModel = $this->getModel('phototype');
                $target = $phototypeModel->getById($id);
                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }
                $this->view->model = $target;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    public function phototypeRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $phototypeModel = $this->getModel('phototype');
                $result = $phototypeModel->remove($id);
            }
            echo $result;
            exit;
        }
    }


    /****************************************************
     * 分类action部分
     *
     * *************************************************/
    public function categoryCreateAction() {

        $categoryModel = $this->getModel('category');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $name = $this->request->getParam('name');
            $name_en = $this->request->getParam('name_en');
            $description = $this->request->getParam('description');
            $parentId = $this->request->getParam('parent');

            try {
                $result = $categoryModel->addCategory($name, $description, $parentId, $name_en);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-category-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "创建分类";
            $this->view->categories = $categoryModel->getAll(false);
        }
    }

    public function categoryListAction() {
        $categoryModel = $this->getModel('category');
        $programModel = $this->getModel('program');
        $userModel = $this->getModel('User');
        
        $resource = $categoryModel->getAll(false);
        
        $this->view->title = "分类列表";
        $this->view->categoryModel = $categoryModel;
        $this->view->programModel = $programModel;
        $this->view->userModel = $userModel;
        $this->view->resource = $resource;
        $this->view->specialMode = $this->getModel('special');
    }

    public function categoryRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');

            if ($id) {
                $categoryModel = $this->getModel('category');
                $result = $categoryModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function categorySaveAction() {
        $notFoundMsg = '未找到目标分类';
        $categoryModel = $this->getModel('category');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->request->getParam('id');
            $name = $this->request->getParam('name');
            $name_en = $this->request->getParam('name_en');
            $description = $this->request->getParam('description');
            $parentId = $this->request->getParam('parent');
            
            try {
                $result = $categoryModel->saveCategory($id, $name, $description, $parentId, $name_en);
            } catch (Angel_Exception_Category $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-category-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑分类";
            $this->view->categories = $categoryModel->getAll(false);

            $id = $this->request->getParam("id");
            if ($id) {
                $categoryModel = $this->getModel('category');
                $target = $categoryModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }
                $this->view->model = $target;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /*********************************************************
     * 产品action代码部分
     *
     * ******************************************************/
    public function productCreateAction() {
        $productModel = $this->getModel('product');
        $categoryModel = $this->getModel('category');

        if ($this->request->isPost()) {
            $result = 0;

            $name = $this->getParam('name');
            $name_en = $this->getParam('name_en');
            $width = $this->getParam('width');
            $width_en = $this->getParam('width_en');
            $thickness = $this->getParam('thickness');
            $thickness_en = $this->getParam('thickness_en');
            $height = $this->getParam('height');
            $height_en = $this->getParam('height_en');
            $style = $this->getParam('style');
            $style_en = $this->getParam('style_en');
            $other_thickness = $this->getParam('other_thickness');
            $other_thickness_en = $this->getParam('other_thickness_en');
            $steel_core = $this->getParam('steel_core');
            $steel_core_en = $this->getParam('steel_core_en');
            $steel_belt = $this->getParam('steel_belt');
            $steel_belt_en = $this->getParam('steel_belt_en');
            $structure = $this->getParam('structure');
            $structure_en = $this->getParam('structure_en');
            $description = $this->getParam('description');
            $description_en = $this->getParam('description_en');
            $photo = $this->decodePhoto();
            $category_id = $this->getParam('category_id');

            if (!$name) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=必须填写产品名称');
            }
            else if ($category_id == "0") {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=请选择产品分类');
            }
            else {
                try {
                    $result = $productModel->addProduct($name, $name_en, $width, $width_en, $thickness, $thickness_en, $height, $height_en, $style, $style_en, $other_thickness, $other_thickness_en, $steel_core, $steel_core_en, $steel_belt, $steel_belt_en,  $structure, $structure_en, $description, $description_en, $photo, $category_id);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                if ($result) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-product-list-home'));
                } else {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
                }
            }
        } else {
            $this->view->title = "创建产品信息";
            $this->view->categorys = $categoryModel->getAll(false);
        }
    }

    public function productListAction() {
        $productModel = $this->getModel('product');

        $page = $this->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $paginator = $productModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);

        $resource = array();

        foreach ($paginator as $r) {
            $path = "";

            if (count($r->photo)) {
                try {
                    if ($r->photo[0]->name) {
                        $path = $this->bootstrap_options['image.photo_path'];

                        $path = $this->view->photoImage($r->photo[0]->name . $r->photo[0]->type, 'small');
                    }
                } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                    // 图片被删除的情况
                }
            }

            $resource[] = array(
                'id' => $r->id,
                'name' => $r->name,
                'name_en'=>$r->name_en,
                'photo' => $path
            );
        }

        $this->view->resource = $resource;
        $this->view->title = "产品列表";
        $this->view->paginator = $paginator;
    }

    public function productRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $productModel = $this->getModel('product');
                $result = $productModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function productSaveAction() {
        $notFoundMsg = '未找到目标产品';
        $productModel = $this->getModel('product');
        $categoryModel = $this->getModel('category');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $name = $this->getParam('name');
            $name_en = $this->getParam('name_en');
            $width = $this->getParam('width');
            $width_en = $this->getParam('width_en');
            $thickness = $this->getParam('thickness');
            $thickness_en = $this->getParam('thickness_en');
            $height = $this->getParam('height');
            $height_en = $this->getParam('height_en');
            $style = $this->getParam('style');
            $style_en = $this->getParam('style_en');
            $other_thickness = $this->getParam('other_thickness');
            $other_thickness_en = $this->getParam('other_thickness_en');
            $steel_core = $this->getParam('steel_core');
            $steel_core_en = $this->getParam('steel_core_en');
            $steel_belt = $this->getParam('steel_belt');
            $steel_belt_en = $this->getParam('steel_belt_en');
            $structure = $this->getParam('structure');
            $structure_en = $this->getParam('structure_en');
            $description = $this->getParam('description');
            $description_en = $this->getParam('description_en');
            $photo = $this->decodePhoto();
            $category_id = $this->getParam('category_id');

            try {
                $result = $productModel->saveProduct($id, $name, $name_en, $width, $width_en, $thickness, $thickness_en, $height, $height_en, $style, $style_en, $other_thickness, $other_thickness_en, $steel_core, $steel_core_en, $steel_belt, $steel_belt_en, $structure, $structure_en, $description, $description_en, $photo, $category_id);
            } catch (Angel_Exception_Product $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-product-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑产品";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $productModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;
                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }

                $categorys = $categoryModel->getAll(false);

                $this->view->categorys = $categorys;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /************************************************************
     * 典型案例代码部分
     *
     * *********************************************************/
    public function caseCreateAction() {
        $classiccaseModel = $this->getModel('classiccase');
        $productModel = $this->getModel('product');

        if ($this->request->isPost()) {
            $result = 0;

            $name = $this->getParam('name');
            $name_en = $this->getParam('name_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();
            $product_id = $this->getParam('product');

            $products = array();

            if ($product_id) {
                $tmp_products = $productModel->getProductByIds($product_id);

                if ($tmp_products) {
                    foreach ($tmp_products as $p) {
                        $products[] = $p;
                    }
                }
            }

            if (count($products) == 0) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=案例必须包含一个产品'); exit;
            }
            else {
                if (!$name || !$name_en) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=必须填写案例名称');
                }
                else {
                    try {
                        $result = $classiccaseModel->addCase($name, $name_en, $simple_content, $simple_content_en, $content, $content_en, $photo, $products);
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                    if ($result) {
                        $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-case-list-home'));
                    } else {
                        $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
                    }
                }
            }
        } else {
            $products = $productModel->getAll(false);

            $this->view->products = $products;
            $this->view->title = "创建案例";
        }
    }

    public function caseListAction() {
        $classiccaseModel = $this->getModel('classiccase');

        $page = $this->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $paginator = $classiccaseModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);

        $resource = array();

        foreach ($paginator as $r) {
            $resource[] = array(
                'id' => $r->id,
                'name' => $r->name
            );
        }

        $this->view->resource = $resource;
        $this->view->title = "案例列表";
        $this->view->paginator = $paginator;
    }

    public function caseSaveAction() {
        $notFoundMsg = '未找到目标产品';
        $classiccaseModel = $this->getModel('classiccase');
        $productModel = $this->getModel('product');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $name = $this->getParam('name');
            $name_en = $this->getParam('name_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();
            $product_id = $this->getParam('product');

            $products = array();

            if ($product_id) {
                $tmp_products = $productModel->getProductByIds($product_id);

                if ($tmp_products) {
                    foreach ($tmp_products as $p) {
                        $products[] = $p;
                    }
                }
            }

            if (count($products) == 0) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=案例必须包含一个产品');
            }
            else {
                if (!$name || !$name_en) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=必须填写案例名称');
                }
                else {
                    try {
                        $result = $classiccaseModel->saveCase($id, $name, $name_en, $simple_content, $simple_content_en, $content, $content_en, $photo, $products);
                    } catch (Angel_Exception_News $e) {
                        $error = $e->getDetail();
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }

                if ($result) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-case-list-home'));
                } else {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
                }
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑案例";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $classiccaseModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;

                $products_id = array();

                foreach ($target->product as $p) {
                    $products_id[] = $p->id;
                }

                $this->view->own_product = $products_id;

                $products = $productModel->getAll(false);

                $this->view->products = $products;

                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    public function caseRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $classiccaseModel = $this->getModel('classiccase');
                $result = $classiccaseModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    /*************************************************************
     * 新闻管理代码部分
     *
     * **********************************************************/
    public function newsCreateAction() {
        $newsModel = $this->getModel('news');

        if ($this->request->isPost()) {
            $result = 0;

            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();
            $subtitle = $this->getParam('subtitle');
            $subtitle_en = $this->getParam('subtitle_en');

            if (!$title || !$title_en) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=必须填写新闻标题');
            }
            else {
                try {
                    $result = $newsModel->addNews($title, $title_en, $content, $content_en, $photo, $subtitle, $subtitle_en);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                if ($result) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-news-list-home'));
                } else {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
                }
            }
        } else {
            $this->view->title = "创建新闻";
        }
    }

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

        foreach ($paginator as $r) {
            $resource[] = array(
                'id' => $r->id,
                'title' => $r->title
            );
        }

        $this->view->resource = $resource;
        $this->view->title = "新闻列表";
        $this->view->paginator = $paginator;
    }

    public function newsRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $newsModel = $this->getModel('news');
                $result = $newsModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function newsSaveAction() {
        $notFoundMsg = '未找到目标新闻';
        $newsModel = $this->getModel('news');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $subtitle = $this->getParam('subtitle');
            $subtitle_en = $this->getParam('subtitle_en');
            $photo = $this->decodePhoto();

            try {
                $result = $newsModel->saveNews($id, $title, $title_en, $content, $content_en, $photo, $subtitle, $subtitle_en);
            } catch (Angel_Exception_News $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-news-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑新闻";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $newsModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;

                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /*********************************************************
     * 展示图代码部分
     *
     * *******************************************************/
    public function imageCreateAction() {
        $showModel = $this->getModel('show');

        if ($this->request->isPost()) {
            $result = 0;

            $remark = $this->getParam('remark');
            $remark_en = $this->getParam('remark_en');
            $photo = $this->decodePhoto();

            try {
                $result = $showModel->addShow($remark, $remark_en, $photo);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-show-image-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            $this->view->title = "添加幻灯片图片";
        }
    }

    public function imageListAction() {
        $showModel = $this->getModel('show');

        $result = $showModel->getAll(false);

        $resource = array();

        foreach ($result as $p) {
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

            $resource[] = array(
                'id' => $p->id,
                'title' =>"首页幻灯片",
                'photo'=>$path
            );
        }
        $this->view->resource = $resource;
        $this->view->title = "幻灯片图片";
    }

    public function imageRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $showModel = $this->getModel('show');
                $result = $showModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function imageSaveAction() {
        $notFoundMsg = '未找到目标产品';
        $showModel = $this->getModel('show');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $remark = $this->getParam('remark');
            $remark_en = $this->getParam('remark_en');
            $photo = $this->decodePhoto();

            try {
                $result = $showModel->saveShow($id, $remark, $remark_en, $photo);
            } catch (Angel_Exception_Show $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-show-image-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑幻灯片";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $showModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;

                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /************************************************************
     * 公司简介代码
     *
     * **********************************************************/
    public function profileCreateAction() {
        $profileModel = $this->getModel('companyprofile');

        if ($this->request->isPost()) {
            $result = 0;

            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();

            try {
                $result = $profileModel->addAbout($title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-profile-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            $this->view->title = "创建公司简介";
        }
    }

    public function profileListAction() {
        $profileModel = $this->getModel('companyprofile');

        $paginator = $profileModel->getAll(false);

        $resource = array();

        foreach ($paginator as $r) {
            $resource[] = array(
                'id' => $r->id,
                'title' => $r->title
            );
        }

        $this->view->resource = $resource;
        $this->view->title = "公司简介";
    }

    public function profileRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $profileModel = $this->getModel('companyprofile');
                $result = $profileModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function profileSaveAction() {
        $notFoundMsg = '未找到公司简介';
        $profileModel = $this->getModel('companyprofile');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();

            try {
                $result = $profileModel->saveAbout($id, $title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo);
            } catch (Angel_Exception_About $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-profile-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑公司简介";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $profileModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;

                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /************************************************************
     * 关于我们代码
     *
     * **********************************************************/
    public function aboutCreateAction() {
        $aboutModel = $this->getModel('about');

        if ($this->request->isPost()) {
            $result = 0;

            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();

            try {
                $result = $aboutModel->addAbout($title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-about-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            $this->view->title = "创建关于我们";
        }
    }

    public function aboutListAction() {
        $aboutModel = $this->getModel('about');

        $paginator = $aboutModel->getAll(false);

        $resource = array();

        foreach ($paginator as $r) {
            $resource[] = array(
                'id' => $r->id,
                'title' => $r->title
            );
        }

        $this->view->resource = $resource;
        $this->view->title = "关于我们";
    }

    public function aboutRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $aboutModel = $this->getModel('about');
                $result = $aboutModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function aboutSaveAction() {
        $notFoundMsg = '未找到关于我们信息';
        $aboutModel = $this->getModel('about');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $title = $this->getParam('title');
            $title_en = $this->getParam('title_en');
            $simple_content = $this->getParam('simple_content');
            $simple_content_en = $this->getParam('simple_content_en');
            $content = $this->getParam('content');
            $content_en = $this->getParam('content_en');
            $photo = $this->decodePhoto();

            try {
                $result = $aboutModel->saveAbout($id, $title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo);
            } catch (Angel_Exception_About $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-about-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑关于我们";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $aboutModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;

                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /********************************************************
     * 其他代码action部分
     *
     * *******************************************************/
    public function resultAction() {
        $this->view->error = $this->request->getParam('error');
        $this->view->redirectUrl = $this->request->getParam('redirectUrl');
    }

    protected function getTmpFile($uid) {
        $utilService = $this->_container->get('util');
        $result = $utilService->getTmpDirectory() . '/' . $uid;
        return $result;
    }

    /**************************************
     * 测试 api action
     *
     * ************************************/
    public function apiTestAction() {

    }


    /********************************************************
     * 专辑aciton部分，用于代码模板，后面删除
     *
     * *****************************************************/
    public function specialCreateAction() {
        $specialModel = $this->getModel('special');
        $authorModel = $this->getModel('author');
        $programModel = $this->getModel('program');
        $categoryModel = $this->getModel('category');
        $userModel = $this->getModel('user');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $name = $this->request->getParam('name');
            $authorId = $this->request->getParam('authorId');
            $photo = $this->decodePhoto();
            $categoryId = $this->request->getParam('categoryId');
            $tmp_program_id = $this->request->getParam('programs');

            $programs_id = explode(",", $tmp_program_id);
            
            $programs = array();
            
            if (is_array($programs_id) && $programs_id[0] != "") {
                foreach ($programs_id as $p) {
                    $programs[] = $programModel->getById($p);
                }
            }

            try {
                $result = $specialModel->addSpecial($name, $authorId, $photo, $programs, $categoryId);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-special-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            $result = $specialModel->getAll(false);
                
            $ownProgramIds = "";

            foreach ($result as $special) {
                if (!$special->program) {
                    continue;
                }

                foreach ($special->program as $p) {
                    if ($ownProgramIds != "")
                        $ownProgramIds = $ownProgramIds . ",";

                    $ownProgramIds = $ownProgramIds . $p->id;
                }
            }

            $programIds = explode(",", $ownProgramIds);
            
            $not_own_programs = $programModel->getProgramNotOwn($programIds);
            
            $this->view->title = "创建专辑";
            $this->view->authors = $userModel->getVipList(false);
            $this->view->not_own_programs = $not_own_programs;
            $this->view->categorys = $categoryModel->getAll(false);
        }
    }

    public function specialListAction() {
        $specialModel = $this->getModel('special');
        $page = $this->request->getParam('page');

        if (!$page) {
            $page = 1;
        }

        $root = $specialModel->getRoot();
        $paginator = $specialModel->getAll();
        $paginator->setItemCountPerPage($this->bootstrap_options['default_page_size']);
        $paginator->setCurrentPageNumber($page);

        $resource = array();
        setcookie("userId", "");
        
        foreach ($root as $r) {
            $resource[] = array(
                'id' => $r->id,
                'name' => $r->name//,
                    // 'photo' => $r->cover_path
            );
        }

        // JSON FORMAT
        if ($this->getParam('format') == 'json') {
            $this->_helper->json(array('data' => $resource,
                'code' => 200));
        } else {
            $this->view->resource = $resource;
            $this->view->title = "专辑列表";
            $this->view->paginator = $paginator;
        }
    }

    public function specialRemoveAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $id = $this->getParam('id');
            if ($id) {
                $specialModel = $this->getModel('special');
                $result = $specialModel->remove($id);
            }
            echo $result;
            exit;
        }
    }

    public function specialSaveAction() {
        $notFoundMsg = '未找到目标专辑';
        $specialModel = $this->getModel('special');
        $authorModel = $this->getModel('author');
        $programModel = $this->getModel('program');
        $categoryModel = $this->getModel('category');
        $userModel = $this->getModel('user');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            $name = $this->request->getParam('name');
            $authorId = $this->request->getParam('authorId');

            $photo = $this->decodePhoto();
            $categoryId = $this->request->getParam('categoryId');
            $tmp_program_id = $this->request->getParam('programs');
            
            $programs_id = explode(",", $tmp_program_id);
            
            $programs = array();

            if (is_array($programs_id) && $programs_id[0] != "") {
                foreach ($programs_id as $p) {
                    $programs[] = $programModel->getById($p);
                }
            }

            try {
                $result = $specialModel->saveSpecial($id, $name, $authorId, $photo, $programs, $categoryId);
            } catch (Angel_Exception_Special $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-special-list-home'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑专辑";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $specialModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $result = $specialModel->getAll(false);
                
                $ownProgramIds = "";

                foreach ($result as $special) {
                    if (!$special->program) {
                        continue;
                    }
                    
                    foreach ($special->program as $p) {
                        if ($ownProgramIds != "")
                            $ownProgramIds = $ownProgramIds . ",";

                        $ownProgramIds = $ownProgramIds . $p->id;
                    }
                }

                $programIds = explode(",", $ownProgramIds);

                $this->view->model = $target;
                $photo = $target->photo;

                if ($photo) {
                    $saveObj = array();
                    foreach ($photo as $p) {
                        try {
                            $name = $p->name;
                        } catch (Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                            $this->view->imageBroken = true;
                            continue;
                        }
                        $saveObj[$name] = $this->view->photoImage($p->name . $p->type, 'small');
                        if (!$p->thumbnail) {
                            $saveObj[$name] = $this->view->photoImage($p->name . $p->type);
                        }
                    }
                    if (!count($saveObj))
                        $saveObj = false;
                    $this->view->photo = $saveObj;
                }

                $own_programs = $programModel->getProgramOwn($programIds);
                $not_own_programs = $programModel->getProgramNotOwn($programIds);
                
                $categorys = $categoryModel->getAll(false);
  
                $this->view->categorys = $categorys;
                $this->view->authors = $userModel->getVipList(false);
                $this->view->own_programs = $target->program;
                $this->view->not_own_programs = $not_own_programs;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }

    /*********************************************************
     *联系我们
     *
     * ******************************************************/
    public function contactCreateAction() {
        $contactModel = $this->getModel('contact');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $name = $this->request->getParam('name');
            $name_en = $this->request->getParam('name_en');
            $sale_tel = $this->request->getParam('sale_tel');
            $fax = $this->request->getParam('fax');
            $email = $this->request->getParam('email');
            $company_address = $this->request->getParam('company_address');
            $company_address_en = $this->request->getParam('company_address_en');
            $factory_address_1 = $this->request->getParam('factory_address_1');
            $factory_address_1_en = $this->request->getParam('factory_address_1_en');
            $factory_address_2 = $this->request->getParam('factory_address_2');
            $factory_address_2_en = $this->request->getParam('factory_address_2_en');

            if (!$name) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=请输入公司名称'); exit;
            }

            if (!$name_en) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=请输入公司英文名称'); exit;
            }

            try {
                $result = $contactModel->addContact($name, $name_en, $sale_tel, null, $fax, null, $company_address, $company_address_en, $factory_address_1, $factory_address_1_en, $factory_address_2, $factory_address_2_en, $email, null);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-index'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            $result = $contactModel->getAll(false);

            $count = count($result);

            if ($count == 0) {
                $this->view->title = "创建联系我们";
            }
            else {
                $id = null;

                foreach ($result as $r) {
                    $id = $r->id;

                    break;
                }

                $this->_redirect("/manage/contact/save/". $id);
            }
        }
    }

    public function contactSaveAction() {
        $notFoundMsg = '未找到联系我们';
        $contactModel = $this->getModel('contact');

        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD

            $id = $this->request->getParam('id');
            // POST METHOD
            $name = $this->request->getParam('name');
            $name_en = $this->request->getParam('name_en');
            $sale_tel = $this->request->getParam('sale_tel');
            $fax = $this->request->getParam('fax');
            $email = $this->request->getParam('email');
            $company_address = $this->request->getParam('company_address');
            $company_address_en = $this->request->getParam('company_address_en');
            $factory_address_1 = $this->request->getParam('factory_address_1');
            $factory_address_1_en = $this->request->getParam('factory_address_1_en');
            $factory_address_2 = $this->request->getParam('factory_address_2');
            $factory_address_2_en = $this->request->getParam('factory_address_2_en');

            if (!$name) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=请输入公司名称'); exit;
            }

            if (!$name_en) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=请输入公司英文名称'); exit;
            }

            try {
                $result = $contactModel->saveContact($id, $name, $name_en, $sale_tel, null, $fax, null, $company_address, $company_address_en, $factory_address_1, $factory_address_1_en, $factory_address_2, $factory_address_2_en, $email, null);
            } catch (Angel_Exception_Contact $e) {
                $error = $e->getDetail();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($result) {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?redirectUrl=' . $this->view->url(array(), 'manage-index'));
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $error);
            }
        } else {
            // GET METHOD
            $this->view->title = "编辑专辑";

            $id = $this->request->getParam("id");

            if ($id) {
                $target = $contactModel->getById($id);

                if (!$target) {
                    $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
                }

                $this->view->model = $target;
            } else {
                $this->_redirect($this->view->url(array(), 'manage-result') . '?error=' . $notFoundMsg);
            }
        }
    }
}
