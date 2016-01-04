<?php

class Angel_ShowController extends Angel_Controller_Action {

    protected $login_not_required = array('detail', 'save-user-category', 'download-android', 'download-ios', 'upload-file', 'play', 'fi-add', 'fi-list', 'user-category-list', 'remove-user-category', 'phone-play', 'special-program-list', 'special-recommend');

    public function init() {
        parent::init();
        $this->_helper->layout->setLayout('main');
    }

//    public function detailAction() {
//        $id = $this->request->getParam('id');
//        if ($id) {
//            $programModel = $this->getModel('program');
//            $program = $programModel->getById($id);
//            $this->view->model = $program;
//            $this->view->title = $program->name;
////            if ($program->oss_video) {
////                $this->view->video_url = $this->bootstrap_options['oss_prefix'] . $program->oss_video->key;
////            }
////            if ($program->oss_audio) {
////                $this->view->audio_url = $this->bootstrap_options['oss_prefix'] . $program->oss_audio->key;
////            }
//        }
//
//        if (!$this->request->isPost()) {
//            if ($_COOKIE["userId"] == null || $_COOKIE["userId"] == "") {
//                $guidModel = $this->getModel('guid');
//
//                setcookie('userId', $guidModel->toString());
//            }
//        }
//    }

//    public function playAction() {
//        $recommendModel = $this->getModel('recommend');
//        $specialModel = $this->getModel('special');
//        $programModel = $this->getModel('program');
//
//        $specialId = $this->request->getParam('special');
//        $programId = $this->request->getParam('program');
//        
//        $specialBean = false;
//
//        $played_special_id = $_COOKIE["sid"];
//        $played_program_id = $_COOKIE["pid"];
//        //如果没有专辑id或当前url 专辑id等于上一次的播放专辑id，重新获取推荐
//        if (!$specialId || $specialId == $played_special_id && $programId == $played_program_id) {
//            // 未请求专辑ID
//            //未登录且有一次播放记录
//            if (!$this->me && $played_special_id) {
//                $this->view->message = "请先登陆然后继续观看, 谢谢!";
//            } else {
//                // 随机获取一个新的专辑并且redirect到获取到的专辑地址
//                // 如/play?special=xxxxxx
//                $specialBean = $this->getRecommendSpecial($played_special_id);
//
//                $playPath = $this->view->url(array(), 'show-play') . '?special=' . $specialBean->id;
//
//                $this->_redirect($playPath);
//            }
//        } else {
//            $specialId = $this->request->getParam('special');
//            $programId = $this->request->getParam('program');
//            $cur_program = false;
//
//            $specialBean = $specialModel->getById($specialId);
//            // 由于专辑ID一定存在， 而节目ID可能存在
//            // 首先根据专辑ID获取专辑，以及所有专辑包含的节目
//            // 如果获取到了节目ID，指示页面播放指定节目，否则播放第一首节目
//            //如果当前专辑不存在或已被删除
//            if (!$specialBean) {
//                
//            } else {
//                $result = $this->getSpecialInfo($specialBean);
//                if (count($result["programs"])) {
//                    //如果没有查询到节目id就直接播放当前专辑第一个
//                    $cur_program = $result["programs"][0];
//                    //根据program_id 获取当前要播放的节目
//                    if ($programId) {  
//                        foreach ($result["programs"] as $p) {
//                            if ($p['id'] == $programId) {
//                                $cur_program = $p;
//                                break;
//                            }
//                        }
//                    }
//
//                    if ($this->me) {
//                        //获取当前需要推荐的用户ID
//                       $userId = $this->me->getUser()->id;
//                       //保存推荐记录  可能调整一下位置
//                       $recommendModel->addRecommend($specialBean->id, $userId);
//                    }
//                        
//                    setcookie('sid', $specialBean->id);
//                    setcookie('pid', $cur_program->id);
//                    $this->view->cur_program = $cur_program;
//                    $this->view->resource = $result;
//                }
//                else {
//                    //如果为假，就是没有根据专辑id找到对应的专辑，跳到404页面
//                }
//            }
//        }
//        
//        // 判断用户来自于PC端还是手机端，render不同的模板和Layout
//        if ($this->isMobile()) {
//            $this->_helper->layout->setLayout('mobile'); 
//            $this->render('phone-play ');
//        }
//    }
    
    public function playAction() {
        $recommendModel = $this->getModel('recommend');
        $specialModel = $this->getModel('special');
        $programModel = $this->getModel('program');

        $specialId = $this->request->getParam('special');
        $programId = $this->request->getParam('program');
        
        $specialBean = false;

        $played_special_id = $_COOKIE["sid"];
        $played_program_id = $_COOKIE["pid"];
        
        // 未请求专辑ID
        //未登录且有一次播放记录
         if (!$this->me && $played_special_id) {
                $this->view->message = "请先登陆然后继续观看, 谢谢!";
                
                return;
        } 
        
        //如果没有专辑id或当前url 专辑id等于上一次的播放专辑id，重新获取推荐
        if (!$specialId) {
            // 随机获取一个新的专辑并且redirect到获取到的专辑地址
            // 如/play?special=xxxxxx
            $specialBean = $this->getRecommendSpecial($played_special_id);

            $playPath = $this->view->url(array(), 'show-play') . '?special=' . $specialBean->id;

            $this->_redirect($playPath);
        } else {
            $specialId = $this->request->getParam('special');
            $programId = $this->request->getParam('program');
            $cur_program = false;

            $specialBean = $specialModel->getById($specialId);
            // 由于专辑ID一定存在， 而节目ID可能存在
            // 首先根据专辑ID获取专辑，以及所有专辑包含的节目
            // 如果获取到了节目ID，指示页面播放指定节目，否则播放第一首节目
            //如果当前专辑不存在或已被删除
            if (!$specialBean) {
                
            } else {
                $result = $this->getSpecialInfo($specialBean);
                if (count($result["programs"])) {
                    //如果没有查询到节目id就直接播放当前专辑第一个
                    $cur_program = $result["programs"][0];
                    //根据program_id 获取当前要播放的节目
                    if ($programId) {  
                        foreach ($result["programs"] as $p) {
                            if ($p['id'] == $programId) {
                                $cur_program = $p;
                                break;
                            }
                        }
                    }

                    if ($this->me) {
                        //获取当前需要推荐的用户ID
                       $userId = $this->me->getUser()->id;
                       //保存推荐记录  可能调整一下位置
                       $recommendModel->addRecommend($specialBean->id, $userId);
                    }
                        
                    setcookie('sid', $specialBean->id);
                    setcookie('pid', $cur_program->id);
                    $this->view->cur_program = $cur_program;
                    $this->view->resource = $result;
                }
                else {
                    //如果为假，就是没有根据专辑id找到对应的专辑，跳到404页面
                }
            }
        }
        
        // 判断用户来自于PC端还是手机端，render不同的模板和Layout
        if ($this->isMobile()) {
            $this->_helper->layout->setLayout('mobile'); 
            $this->render('phone-play ');
        }
    }

    protected function getRecommendSpecial($curSpecialId) {
        $specialModel = $this->getModel('special');
        $recommendModel = $this->getModel('recommend');
        $categoryModel = $this->getModel('category');
        $hotModel = $this->getModel("hot");
        $userModel = $this->getModel('user');
        
        if ($this->me) {
            //获取当前需要推荐的用户ID
            $userId = $this->me->getUser()->id;

            $user = $userModel->getUserById($userId);

            $special = false;

            //获取该用户已经推荐过的专辑ID集合
            $recommends = $recommendModel->getRecommend($userId);

            $recommend_special_id = array();

            if ($recommends) {
                foreach ($recommends as $r) {
                    $recommend_special_id[] = $r->specialId;
                }
            }

            $like_category_id = array();

            foreach ($user->category as $c) {
                $like_category_id[] = $c->id;
            }

            //获取喜好热点专辑
            $hot = $hotModel->getLikeNotRecommendHot($like_category_id);

            if ($hot) {
                foreach ($hot as $h) {
                    foreach ($h->special as $p) {
                        $isRecommend = false;

                        foreach ($recommend_special_id as $r) {
                            if ($p->id == $r) {
                                $isRecommend = true;
                            }
                        }

                        if (!$isRecommend) {
                            $special = $p;

                            break;
                        }
                    }

                    if ($special)
                        break;
                }
            }

            //获取非喜好热点专辑
            if (!$special) {
                //获取喜好热点专辑
                $hot = $hotModel->getNotRecommendHot($like_category_id);

                if ($hot) {
                    foreach ($hot as $h) {
                        foreach ($h->special as $p) {
                            $isRecommend = false;

                            foreach ($recommend_special_id as $r) {
                                if ($p->id == $r) {

                                    $isRecommend = true;

                                    break;
                                }
                            }

                            if (!$isRecommend) {
                                $special = $p;

                                break;
                            }
                        }

                        if ($special) {
                            break;
                        }
                    }
                }
            }
            //获取喜好分类专辑
            if (!$special) {
                $special = $specialModel->getSpecialByCategoryId($recommend_special_id, $like_category_id);
            }

            //获取非喜好分类专辑
            if (!$special) {
                $special = $specialModel->getSpecialByNotCategoryId($recommend_special_id, $like_category_id);
            }
        }

        //没有热点，也没有没看过的视频，
        if (!$special) {           
            $played_special_id = $_COOKIE["sid"];
            $played_program_id = $_COOKIE["pid"];

            //没有获取到当前视频id的极端情况
            if (!$played_special_id) {
                $special = $specialModel->getLastOne();
            } else {
                $tmpSpecial = $specialModel->getById($played_special_id);

                $special = $specialModel->getNext($tmpSpecial);

                if (!$special) {
                    $special = $specialModel->getLastOne();
                }
            }
        }

        return $special;
    }

    protected function getSpecialInfo($special) {
        $recommendModel = $this->getModel('recommend');
        $programModel = $this->getModel('program');
        $favouriteModel = $this->getModel('favourite');
        $userModel = $this->getModel('user');
        $favouriteModel = $this->getModel('favourite');
        //获取该专辑上传达人
        $author = $userModel->getUserById($special->authorId); //$authorModel->getAuthorById($special->authorId);
        
        $like = 0;
        
        if ($this->me) {
            $favourites = $favouriteModel->getFavouriteByUserId($userId);

            foreach ($favourites->special as $p) {
                if ($p->id == $special->id) {
                    $like = 1;

                    break;
                }
            }
        }
        
        $result["id"] = $special->id;
        $result["name"] = $special->name;
        $result["like"] = $like; 

        if ($author == "")
            $result["author"] = "";
        else
            $result["author"] = $author->name;

        $result["photo"] = $this->bootstrap_options['image_broken_ico']['small'];
        $result["photo_main"] = $this->bootstrap_options['image_broken_ico']['big'];

        if (count($special->photo)) {
            $photo = $special->photo[0];
            $result["photo"] = $this->view->photoImage($photo->name . $photo->type, 'small');
            $result["photo_main"] = $this->view->photoImage($photo->name . $photo->type, 'main');
        }

        foreach ($special->program as $program) {
            $result["programs"][] = array("id" => $program->id, "name" => $program->name, "time" => $program->time, "oss_video" => $this->bootstrap_options['oss_prefix'] . $program->oss_video->key, "oss_audio" => $this->bootstrap_options['oss_prefix'] . $program->oss_audio->key);
        }

        return $result;
    }

    public function specialRecommendAction() {
        $recommendModel = $this->getModel('recommend');
        $specialModel = $this->getModel('special');
        $programModel = $this->getModel('program');
        $authorModel = $this->getModel('author');
        $categoryModel = $this->getModel('category');
        $hotModel = $this->getModel("hot");
        $userModel = $this->getModel('user');
        $favouriteModel = $this->getModel('Favourite');
        
        $like = 0;
        
        if ($this->me) {
            //获取当前需要推荐的用户ID
            $userId = $this->me->getUser()->id;

            $user = $userModel->getUserById($userId);

            $curSpecialId = $this->request->getParam('special');

            if ($curSpecialId == "none")
                $curSpecialId = false;

            $special = false;

            //获取该用户已经推荐过的专辑ID集合
            $recommends = $recommendModel->getRecommend($userId);

            $recommend_special_id = array();

            if ($recommends) {
                foreach ($recommends as $r) {
                    $recommend_special_id[] = $r->specialId;
                }
            }

            $like_category_id = array();

            foreach ($user->category as $c) {
                $like_category_id[] = $c->id;
            }

            //获取喜好热点专辑
            $hot = $hotModel->getLikeNotRecommendHot($like_category_id);

            if ($hot) {
                foreach ($hot as $h) {
                    foreach ($h->special as $p) {
                        $isRecommend = false;

                        foreach ($recommend_special_id as $r) {
                            if ($p->id == $r) {
                                $isRecommend = true;
                            }
                        }

                        if (!$isRecommend) {
                            $special = $p;

                            break;
                        }
                    }

                    if ($special)
                        break;
                }
            }

            //获取非喜好热点专辑
            if (!$special) {
                //获取喜好热点专辑
                $hot = $hotModel->getNotRecommendHot($like_category_id);

                if ($hot) {
                    foreach ($hot as $h) {
                        foreach ($h->special as $p) {
                            $isRecommend = false;

                            foreach ($recommend_special_id as $r) {
                                if ($p->id == $r) {

                                    $isRecommend = true;

                                    break;
                                }
                            }

                            if (!$isRecommend) {
                                $special = $p;

                                break;
                            }
                        }

                        if ($special) {
                            break;
                        }
                    }
                }
            }
            //获取喜好分类专辑
            if (!$special) {
                $special = $specialModel->getSpecialByCategoryId($recommend_special_id, $like_category_id);
            }

            //获取非喜好分类专辑
            if (!$special) {
                $special = $specialModel->getSpecialByNotCategoryId($recommend_special_id, $like_category_id);
            }
        }

        //没有热点，也没有没看过的视频，
        if (!$special) {
            //没有获取到当前视频id的极端情况
            if (!$curSpecialId) {
                $special = $specialModel->getLastOne();
            } else {
                $tmpSpecial = $specialModel->getById($curSpecialId);

                $special = $specialModel->getNext($tmpSpecial);

                if (!$special)
                    $special = $specialModel->getLastOne();
            }
        }
//-------------------------------------------------------------------------------
        //获取该专辑上传达人
        $author = $userModel->getUserById($special->authorId); //$authorModel->getAuthorById($special->authorId);
        //首先判断当前用户是否登录，如果登录再判断当前专辑是否当前已收藏的专辑
        if ($this->me) {
            $favourites = $favouriteModel->getFavouriteByUserId($userId);

            foreach ($favourites->special as $p) {
                if ($p->id == $special->id) {
                    $like = 1;

                    break;
                }
            }
        }
        
        $result["id"] = $special->id;
        $result["name"] = $special->name;

        if ($author == "")
            $result["author"] = "";
        else
            $result["author"] = $author->username;

        $result["photo"] = $this->bootstrap_options['image_broken_ico']['small'];
        $result["photo_main"] = $this->bootstrap_options['image_broken_ico']['big'];

        if (count($special->photo)) {
            $photo = $special->photo[0];
            $result["photo"] = $this->view->photoImage($photo->name . $photo->type, 'small');
            $result["photo_main"] = $this->view->photoImage($photo->name . $photo->type, 'main');
        }

        foreach ($special->program as $program) {
            $result["programs"][] = array("id" => $program->id, "name" => $program->name, "time" => $program->time, "like"=>$like, "oss_video" => $this->bootstrap_options['oss_prefix'] . $program->oss_video->key, "oss_audio" => $this->bootstrap_options['oss_prefix'] . $program->oss_audio->key);
        }

        if ($this->me) {
            //保存推荐记录
            $recommendModel->addRecommend($special->id, $userId);
        }
        setcookie('specialId', $special->id);
        $this->_helper->json(array('data' => $result, 'code' => 200));
    }

    public function saveUserCategoryAction() {
        $categoryModel = $this->getModel('category');
        $userModel = $this->getModel('user');

        //获取当前需要推荐的用户ID
        $user_id = $this->me->getUser()->id;
        $categorys_id = $this->request->getParam('category');

        $categorys = null;

        if ($categorys_id != 'none') {
            $tmpCategorys_id = explode(";", $categorys_id);

            if (is_array($tmpCategorys_id)) {
                $categorys = $categoryModel->getByIds($tmpCategorys_id);
            }
        }

        try {
            $userModel->saveUser($user_id, $categorys);

            $this->_helper->json(array('data' => 'save success!', 'code' => 200));
        } catch (Exception $e) {
            $this->_helper->json(array('data' => $e->getMessage(), 'code' => 0));
        }
    }

    public function userCategoryListAction() {
        $userModel = $this->getModel('user');
        $categoryModel = $this->getModel('category');
        
        $user_id = $this->me->getUser()->id;
        
        $result = $categoryModel->getAll(false);
        $user = $userModel->getById($user_id);

        $category = array();
        
        foreach ($result as $p) {
            $like = 0;
            
            foreach ($user->category as $c) {
                if ($p->id == $c->id) {
                    $like = 1;
                    
                    break;
                }   
            }
            
            $category[] = array('id' => $p->id, 'name' => $p->name, 'like'=>$like);
        }

        $this->_helper->json(array('data' => $category, 'code' => 200));
    }
    
    public function removeUserCategoryAction() {
        if ($this->request->isPost()) {
            $result = 0;
            // POST METHOD
            $category_id = $this->getParam('id');
            $user_id = $this->me->getUser()->id;
            
            if ($category_id) {
                $userModel = $this->getModel('user');
                
                $result = $userModel->removeUserCategory($user_id, $category_id);
            }
            echo $result;
            exit;
        }
    }

    public function categoryListAction() {
        if ($this->request->isPost()) {
            $categoryModel = $this->getModel('category');
            $resource = $categoryModel->getAll(false);
            $category = array();
            foreach ($resource as $c) {
                $category[] = array('id' => $c->id, 'name' => $c->name);
            }

            $this->_helper->json(array('data' => $category, 'code' => 200));
        }
    }

    public function keywordVoteAction() {
        $voteModel = $this->getModel('vote');
        $programModel = $this->getModel('program');

        $program_id = $this->getParam('pid');
        $time = intval($this->getParam('time'));
        $user_id = $this->me->getUser()->id;

        $program = $programModel->getById($program_id);

        foreach ($program->keyword as $p) {
            $vote = $voteModel->getByKeywordIdAndUid($p->id, $user_id);
            $score = 0;

            $score = $vote->score;

            if (!$score)
                $score = 0;

            if ($time < 20) {
                $score = $score - 1;
            } elseif ($time > 49 && $time < 80) {
                $score = $score + 1;
            } elseif ($time > 79) {
                $score = $score + 2;
            } else {
                $score = $score;
            }

            try {
                if ($vote) {
                    $voteModel->saveVote($vote->id, $user_id, $p->id, $score);
                } else {
                    $voteModel->addVote($user_id, $p->id, $score);
                }
            } catch (Exception $e) {
                $this->_helper->json(array('data' => $e->getMessage(), 'code' => 0));
            }

            $this->_helper->json(array('data' => 'success', 'code' => 200));
        }
    }

    public function downloadAndroidAction() {
        $version = $this->getParam('version');
        $file_url = $_SERVER['DOCUMENT_ROOT'] . '/download/android/CheeseTV' . $version . '.apk';

        if (!file_exists($file_url)) {
            echo 'file not found!';

            exit;
        } else {
            echo 'file downloading...';
        }

        $file = fopen($file_url, "r");

        Header("Content-type:application/octet-stream");
        Header("Accept-Ranges:bytes");
        Header("Accept-Length:" . filesize($file_url));
        Header("Content-Disposition:attachment;filename=" . 'CheeseTV' . $version . '.apk');

        echo fread($file, filesize($file_url));

        fclose($file);

        exit();
    }

    public function downloadIosAction() {
        $version = $this->getParam('version');
        $file_url = $_SERVER['DOCUMENT_ROOT'] . '/download/ios/CheeseTV' . $version . '.ipa';

        if (!file_exists($file_url)) {
            echo 'file not found!';

            exit;
        } else {
            echo 'file downloading...';
        }

        $file = fopen($file_url, "r");

        Header("Content-type:application/octet-stream");
        Header("Accept-Ranges:bytes");
        Header("Accept-Length:" . filesize($file_url));
        Header("Content-Disposition:attachment;filename=" . 'CheeseTV' . $version . '.ipa');

        echo fread($file, filesize($file_url));

        fclose($file);

        exit();
    }

    public function favouriteAddAction() {
        $favouriteModel = $this->getModel('favourite');
        $specialModel = $this->getModel('special');

        $special_id = $this->getParam('sid');
        $user_id = $this->me->getUser()->id;

        $special = $specialModel->getById($special_id);
        $favourite = $favouriteModel->getFavouriteByUserId($user_id);


        try {
            if ($favourite) {
                $favouriteModel->saveFavourite($favourite, $special);
            } else {
                $favouriteModel->addFavourite($user_id, $special);
            }
        } catch (Exception $e) {
            $this->_helper->json(array('data' => $e->getMessage(), 'code' => 0));
        }

        $this->_helper->json(array('data' => 'success', 'code' => 200));
    }

    public function favouriteRemoveAction() {
        $favouriteModel = $this->getModel('favourite');
        $specialModel = $this->getModel('special');

        $special_id = $this->getParam('sid');
        $user_id = $this->me->getUser()->id;

        $favourite = $favouriteModel->getFavouriteByUserId($user_id);

        try {
            if ($favourite) {
                $favouriteModel->RemoveFavouriteByUserId($favourite, $special_id);
            } else {
                $this->_helper->json(array('data' => '没有找到任何所属收藏！', 'code' => 0));
            }
        } catch (Exception $e) {
            $this->_helper->json(array('data' => $e->getMessage(), 'code' => 0));
        }

        $this->_helper->json(array('data' => 'success', 'code' => 200));
    }

    //根据当前用户的id来获取收藏专辑
    public function favouriteListAction() {
        $favouriteModel = $this->getModel('favourite');
        $user_id = $this->me->getUser()->id;
        $favourite = $favouriteModel->getFavouriteByUserId($user_id);

        if ($favourite) {
            foreach ($favourite->special as $p) {
                $result["specials"][] = array("id" => $p->id, "name" => $p->name);
            }

            $this->_helper->json(array('data' => $result, 'code' => 200));
        } else {
            $this->_helper->json(array('data' => "没有找到任何收藏！", 'code' => 0));
        }
    }

    // 判断用户所否收藏了某个专辑
    public function isUserFavouriteAction() {
        if ($this->request->isPost()) {
            if ($this->me) {
                $favouriteModel = $this->getModel('favourite');
                $user_id = $this->me->getUser()->id;
                $special_id = $this->request->getParam('sid');
                $result = $favouriteModel->isUserFavourite($user_id, $special_id);
                if ($result) {
                    $this->_helper->json(array('code' => 200));
                }
            }
            $this->_helper->json(array('code' => 404));
        }
    }

    public function uploadFileAction() {
        $ossModel = $this->getModel('oss');

        $name = $this->getParam('filename');
        $fsize = $this->getParam('size');
        $key = $this->getParam('key');
        $type = $this->getParam('type');
        $ext = $this->getParam('ext');

        try {
            $ossModel->addOss($name, '', $status, $key, $fsize, $type, $ext, null);
        } catch (Exception $e) {
            $this->_helper->json(array('data' => $e->getMessage(), 'code' => 0));
        }

        $this->_helper->json(array('data' => 'success', 'code' => 200));
    }

    public function specialProgramListAction() {
        $specialModel = $this->getModel('special');

        $special_id = $this->getParam('sid');

        $programs = array();

        if ($special_id) {
            $special = $specialModel->getById($special_id);

            if ($special) {
                foreach ($special->program as $program) {
                    $programs[] =array("id" => $program->id, "name" => $program->name, "time" => $program->time, "oss_video" => $this->bootstrap_options['oss_prefix'] . $program->oss_video->key, "oss_audio" => $this->bootstrap_options['oss_prefix'] . $program->oss_audio->key);
                }
            }
        }
        
        $this->_helper->json(array('data' => $programs, 'code' => 200));
    }
}
