<?php

class Angel_Model_Photo extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Photo';
    protected $_document_user_class = '\Documents\User';

    public function removePhoto($id) {
        $result = false;
        $photo = $this->getById($id);
        try {
            // remove document
            $result = $this->remove($id);
            
            // delete files
            $imageService = $this->_container->get('image');
            $filename = $photo->name . $photo->type;
            $target = $this->getPhotoPath($filename);
            $imageService->deleteThumbnail($target, $this->_bootstrap_options['size']['photo']);
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function savePhoto($id, $title, $description, $phototype) {
        $data = array("title" => $title, "description" => $description, "phototype" => $phototype);
        $result = $this->save($id, $data, Angel_Exception_Photo, Angel_Exception_Photo::PHOTO_NOT_FOUND);
        return $result;
    }

    public function getPhotoByPhototype($phototype_id, $return_as_paginator = true) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('phototype.$id')->equals(new MongoId($phototype_id))
                ->sort('created_at', -1);
        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }
        return $result;
    }

    public function addPhoto($photo, $title, $description, $phototype, $thumbnail, $owner) {
        $result = false;
        $imageService = $this->_container->get('image');
        if (!$imageService->isAcceptedImage($photo)) {
            throw new Angel_Exception_Common(Angel_Exception_Common::IMAGE_NOT_ACCEPTED);
        } else {
            $extension = $imageService->getImageTypeExtension($photo);
            $utilService = $this->_container->get('util');
            $filename = $utilService->generateFilename($extension);
            $destination = $this->getPhotoPath($filename);
            if (copy($photo, $destination)) {
                $generated = true;
                if ($thumbnail) {
                    $generated = $imageService->generateThumbnail($destination, $this->_bootstrap_options['size']['photo']);
                }
                if ($generated) {
                    $data = array("name" => basename($filename, $extension),
                        "type" => $extension,
                        "title" => $title,
                        "description" => $description,
                        "phototype" => $phototype,
                        "thumbnail" => $thumbnail,
                        "owner" => $owner);
                    $result = $this->add($data);
                }
            }
        }
        return $result;
    }

    /**
     * 根据name获取photo document
     * 
     * @param string $name
     * @return mix - when the photo found, return the photo document
     */
    public function getPhotoByName($name) {
        $result = false;
        $photo = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('name')->equals($name)
                ->getQuery()
                ->getSingleResult();

        if (!empty($photo)) {
            $result = $photo;
        }

        return $result;
    }

    /**
     * 获取photo的位置 (＊这个方法还需要根据environment不同做修改)
     * @param string $photoname
     * @return string 
     */
    public function getPhotoPath($photoname) {
        return APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . $this->_bootstrap_options['image']['photo_path'] . DIRECTORY_SEPARATOR . $photoname;
    }

    /**
     * 获取photo的url (＊这个方法还需要根据environment不同做修改)
     * @param string $photoname
     * @return string 
     */
    public function getPhotoUrl($photoname) {
        return $this->_bootstrap_options['image']['photo_path'] . DIRECTORY_SEPARATOR . $photoname;
    }

    /**
     * 获取某size图片名字
     * @param string $name - image type, could be company logo or company image
     * @param string $image － 基本文件名称
     * @param int $version - 文件size
     * @return string 
     */
    public function getPhotoByVersion($image, $version) {
        $imageService = $this->_container->get('image');
        return $imageService->generateImageFilename($this->getImagePath($image), $version, false);
    }

    /**
     * 获取photo的位置 (＊这个方法还需要根据environment不同做修改)
     * @param string $photoname
     * @return string 
     */
    public function getImagePath($photoname) {
        $dir = $this->_bootstrap_options['image']['photo_path'];
        return $dir . DIRECTORY_SEPARATOR . $photoname;
    }

}
