<?php

class Angel_Service_Image {

    const POSITION_CENTER = 'center';
    const POSITION_TOP_LEFT = 'top_left';
    const POSITION_BOTTOM_RIGHT = 'bottom_right';
    const NOT_SUPPORTED_IMAGE_TYPE = 'Angelhere only accepts JPG and PNG image';

    protected $_bootstrap_options;
    protected $_utilService;

    public function __construct($bootstrap_options, Angel_Service_Util $utilService) {
        $this->_bootstrap_options = $bootstrap_options;
        $this->_utilService = $utilService;
    }

    /**
     * generate all various thumbnail 
     * @param string $filepath - the original image file path
     * @return mixed - fails false, success array contains all path of thumbnails  
     */
    public function generateThumbnail($filepath, $size_arr, $coord = array()) {
        $result = array();
        list($image_type, $src) = $this->getImageHandler($filepath);

        if ($image_type == IMAGETYPE_JPEG || $image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            foreach ($size_arr as $size) {
                $tmp = explode('*', $size);
                $des_w = $tmp[0];
                $des_h = $tmp[1];

                $tmp = getimagesize($filepath);
                $src_w = $tmp[0];
                $src_h = $tmp[1];

                $dest = imagecreatetruecolor($des_w, $des_h);

                if (empty($coord)) {
                    $coord = $this->getCropCoord($des_w, $des_h, $src_w, $src_h, 'center');
                }

                if ($image_type == IMAGETYPE_PNG) {
                    $src = imagecreatefrompng($filepath);
                    imagesavealpha($src, true); //这里很重要;
                    imagealphablending($dest, false);
                    imagesavealpha($dest, true);
                }
                if (imagecopyresampled($dest, $src, 0, 0, $coord[0], $coord[1], $des_w, $des_h, $coord[2], $coord[3])) {
                    $target_filename = $this->generateImageFilename($filepath, $des_w);
                    if ($this->saveImage($dest, $image_type, $target_filename)) {
                        $result[] = $target_filename;
                    } else {
                        $result = false;
                        break;
                    }
                } else {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * delete all various thumbnail 
     * @param string $filepath - the original image file path
     * @return boolean - fails false, success true  
     */
    public function deleteThumbnail($filepath, $size_arr) {
        $result = false;
        try {
            foreach ($size_arr as $size) {
                $tmp = explode('*', $size);
                $des_w = $tmp[0];
                $target_filename = $this->generateImageFilename($filepath, $des_w);
                unlink($target_filename);
            }
            unlink($filepath);
            $result = true;
        } catch (Exception $e) {
            
        }
        return $result;
    }

    /**
     * 
     * 
     * @param type $filepath
     * @param type $des_w
     * @param type $des_h
     * @return boolean 
     */
    public function resizeImage($filepath, $des_w = 0, $des_h = 0) {
        $des_w = empty($des_w) ? $this->_bootstrap_options['image']['resized_width'] : $des_w;
        $des_h = empty($des_h) ? $this->_bootstrap_options['image']['resized_height'] : $des_h;

        $result = false;
        list($image_type, $src) = $this->getImageHandler($filepath);

        if ($image_type == IMAGETYPE_JPEG || $image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            $tmp = getimagesize($filepath);
            $src_w = $tmp[0];
            $src_h = $tmp[1];

            $dest = imagecreatetruecolor($des_w, $des_h);
            $backgroundColor = imagecolorallocate($dest, 224, 224, 224);
            imagefill($dest, 0, 0, $backgroundColor);

            $coord = $this->getResizeCoord($des_w, $des_h, $src_w, $src_h);

            if (imagecopyresampled($dest, $src, $coord[0], $coord[1], 0, 0, $coord[2], $coord[3], $src_w, $src_h)) {
                imagedestroy($src);

                //把原文件命名为 文件名_orig.jpg, 然后把resized后的图片保存为原文件名
                $target_filename = $this->generateImageFilename($filepath, $this->_bootstrap_options['image']['orig_ext']);
                copy($filepath, $target_filename);

                if ($this->saveImage($dest, $image_type, $filepath)) {
                    $result = $target_filename;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Accoring to size of target image, calucating the coord and size for the original image for cropped and resizing.
     * 
     * @param int $desc_w - the width of the target image
     * @param int $desc_h - the height of the target image
     * @param int $orig_w - the width of the origin image
     * @param int $orig_h - the height of the origin image
     * @param string $position - the start point to calculate
     * @return array - array(x, y, width, height) 
     */
    public function getCropCoord($desc_w, $desc_h, $orig_w, $orig_h, $position = self::POSITION_CENTER) {
        $result = array();

        $w_ration = $orig_w / $desc_w;
        $h_ration = $orig_h / $desc_h;

        if ($w_ration > $h_ration) {
            $tmp_h = $orig_h;
            $tmp_w = intval($desc_w * $h_ration);
        } else {
            $tmp_w = $orig_w;
            $tmp_h = intval($desc_h * $w_ration);
        }

        if ($position == self::POSITION_TOP_LEFT) {
            $result = array(0, 0, $tmp_w, $tmp_h);
        } else if ($position == self::POSITION_BOTTOM_RIGHT) {
            $result = array(($orig_w - $tmp_w), ($orig_h - $tmp_h), $tmp_w, $tmp_h);
        } else {
            $result = array(intval(($orig_w - $tmp_w) / 2), intval(($orig_h - $tmp_h) / 2), $tmp_w, $tmp_h);
        }

        return $result;
    }

    /**
     * Accoring to size of target image, calucating the coord and size for the original image for resizing.
     * 
     * @param int $desc_w - the width of the target image
     * @param int $desc_h - the height of the target image
     * @param int $orig_w - the width of the origin image
     * @param int $orig_h - the height of the origin image
     * @return array - array(x, y, width, height) 
     */
    public function getResizeCoord($desc_w, $desc_h, $orig_w, $orig_h) {
        $result = array();

        $w_ration = $orig_w / $desc_w;
        $h_ration = $orig_h / $desc_h;

        if ($w_ration > $h_ration) {
            $tmp_w = $desc_w;
            $tmp_h = intval($orig_h * $desc_w / $orig_w);
        } else {
            $tmp_h = $desc_h;
            $tmp_w = intval($orig_w * $desc_h / $orig_h);
        }

        return array(intval(($desc_w - $tmp_w) / 2), intval(($desc_h - $tmp_h) / 2), $tmp_w, $tmp_h);
    }

    /**
     *
     * @param type $filepath - the file path of the image
     * @return type - when the file is accepted (jpg||png), return the image handler, otherwise, return error hint 
     */
    public function getImageHandler($filepath) {
        $result = array(FALSE, self::NOT_SUPPORTED_IMAGE_TYPE);

        $type = exif_imagetype($filepath);
        if ($type == IMAGETYPE_JPEG) {
            $result = array(IMAGETYPE_JPEG, imagecreatefromjpeg($filepath));
        } else if ($type == IMAGETYPE_PNG) {
            $result = array(IMAGETYPE_PNG, imagecreatefrompng($filepath));
        }

        return $result;
    }

    public function getImageTypeExtension($filename) {
        $type = exif_imagetype($filename);
        switch ($type) {
            case IMAGETYPE_GIF:
                return '.gif';
            case IMAGETYPE_PNG:
                return '.png';
            case IMAGETYPE_PSD:
                return '.psd';
            case IMAGETYPE_BMP:
                return '.bmp';
            default:
                return '.jpg';
        }
    }

    /**
     * 系统只接受jpg和png图片
     * @param type $filepath
     * @return boolean 
     */
    public function isAcceptedImage($filepath) {
        $result = FALSE;
        $type = exif_imagetype($filepath);
        if ($type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            $result = true;
        }

        return $result;
    }

    /**
     * according to the original file path, generate the new file name
     * @param string $path - the original file path
     * @param string $append - the appendix used to generate the new file name
     * @return string - the new file path 
     */
    public function generateImageFilename($path, $append = '', $with_path = true) {
        $path_info = pathinfo($path);

        $filename = $path_info['filename'] . ($append != '' ? '_' . $append : '') . '.' . $path_info['extension'];

        if ($with_path) {
            $filename = $path_info['dirname'] . DIRECTORY_SEPARATOR . $filename;
        }

        return $filename;
    }

    /**
     * save the image source as image file
     * @param resource $image - image resource
     * @param int $image_type - image type
     * @param string $path - image file path
     * @return boolean - success true, fail false
     */
    public function saveImage($image, $image_type, $path) {
        $result = false;
        if ($image_type == IMAGETYPE_JPEG) {
            $result = imagejpeg($image, $path, 90);
        } else if ($image_type == IMAGETYPE_PNG) {
            $result = imagepng($image, $path);
        } else if ($image_type == IMAGETYPE_GIF) {
            $result = imagegif($image, $path);
        }

        imagedestroy($image);

        return $result;
    }

}
