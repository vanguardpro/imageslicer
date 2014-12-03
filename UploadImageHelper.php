<?php

class UploadImageHelper
{

    const IMAGE_WIDTH_ALLOWED_RESIZE = 570;
    const IMAGE_HEIGHT_ALLOWED_RESIZE = 300;
    
    private $imgToSave = array(
        0 => array(
            'path' => 'large/mdpi',
            'width' => 460, 
            'height' => 242,
        ),
        1 => array(
            'path' => 'large/ldpi',
            'width' => 345, 
            'height' => 182,
        ),
        2 => array(
            'path' => 'normal/hdpi',
            'width' => 450, 
            'height' => 237,
        ),
        3 => array(
            'path' => 'normal/mdpi',
            'width' => 300, 
            'height' => 158,
        ),
        4 => array(
            'path' => 'normal/ldpi',
            'width' => 225, 
            'height' => 118,
        ),
        5 => array(
            'path' => 'small/hdpi',
            'width' => 450,
            'height' => 237,
        ),    
        6 => array(
            'path' => 'small/mdpi',
            'width' => 300,
            'height' => 158,
        ),
        7 => array(
            'path' => 'small/ldpi',
            'width' => 225,
            'height' => 118,
        ),
        8 => array(
            'path' => 'xlarge/hdpi/r',
             'width' => 510,
             'height' => 284,
        ),
        9 => array(
            'path' => 'xlarge/mdpi/r',
            'width' => '340',
            'height' => '179',
        ),
        10 => array(
            'path' => 'large/xhdpi/r',
            'width' => 440,
            'height' => 232,
        ),
        11 => array(
            'path' => 'large/hdpi/r',
            'width' => 330,
            'height' => 174,
        ),
        12 => array(
            'path' => 'large/mdpi/r',
            'width' => 220,
            'height' => 115,
        ),
        13 => array(
            'path' => 'normal/xhdpi/r',
            'width' => 280,
            'height' => 147,
        ),
        14 => array(
            'path' => 'normal/hdpi/r',
            'width' => 210,
            'height' => 111,
        ),
        15 => array(
            'path' => 'normal/mdpi/r',
            'width' => 140,
            'height' => 74,
        ),
    );

    public static $en_letters = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    );
    
    public function add($path, $filename){
        $ret = false;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        $new_file = $path . "/$filename";
        $imageInfo = getimagesize($new_file);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        if ($width == self::IMAGE_WIDTH_ALLOWED_RESIZE && $height == self::IMAGE_HEIGHT_ALLOWED_RESIZE) {
            //$name = self::save_uploaded_file('async-upload', WP_CONTENT_DIR . '/uploads/');
            foreach ($this->imgToSave as $imageItem) {
                if (!file_exists($path . '/' . $imageItem['path'])) {
                    mkdir($path . '/' . $imageItem['path'] . '/', 0777, true);
                }
                copy($new_file, $path . '/' . $imageItem['path'] . '/' . $filename);
                self::resize_image($filename, $path . '/' . $imageItem['path'] . '/', $imageItem['width'], $imageItem['height']);
                @chmod($path . '/' . $imageItem['path'] . '/' . $filename, 0777);                    
            }
            self::chmod_r(WP_CONTENT_DIR . '/uploads/', 0777, 0777);
            $ret = true;
        }
        
        return $ret;
    }

    public static function save_uploaded_file($field_name, $upload_folder, $new_name = '') {
        if ($_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES[$field_name]['tmp_name'];
            $name = $_FILES[$field_name]['name'];
            $name = str_replace(array(" ", "%20", "+"), "_", $name);
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777);
            }
            $prefix = 1;
            $ext = strtolower(substr(strrchr($name, "."), 1));
            //$name = self::generate_random_string(15).'.'.$ext;
            if (!empty($new_name)) {
                $name = $new_name . '.' . $ext;
            }
            clearstatcache();
            while (file_exists($upload_folder . $name)) {
                $name = substr($name, 0, strrpos($name, ".")) . "_[" . $prefix . "]" . "." . $ext;
                $prefix++;
            }
            move_uploaded_file($tmp_name, $upload_folder . $name);
            @chmod($upload_folder . $name, 0777);
            return $name;
        }
        return '';
    }

    public static function create_thumbnail_from_video($path, $filename, $gallery_id, $targ_w = 664, $targ_h = 378) {
        $sCommand = FFMPEG_PATH . 'ffmpeg -i ' . $path . $filename . ' -an -ss 5 -vframes 1 -s ' . $targ_w . 'x' . $targ_h . ' -y -f mjpeg ' . $path . $filename . '.jpg';
        $res = array();
        @exec($sCommand, $res, $ret);
        if (!file_exists($path . $filename . '.jpg') || filesize($path . $filename . '.jpg') == 0)
            return false;
        self::create_thumbnail_for_video($filename . '.jpg', $filename . 's.jpg', $gallery_id);
        return true;
    }

    public static function resize_image_for_page($filename, $page_id, $targ_w = 664, $targ_h = 378, $change_proportion = true) {
        if (empty($page_id))
            return '';
        $path = SITE_PATH . 'public/images/pages/' . $page_id . '/';
        self::resize_image($filename, $path, $targ_w, $targ_h, $change_proportion);
    }

    public static function resize_image($filename, $path, $targ_w = 800, $targ_h = 800) {
        $jpeg_quality = 90;
        $info = pathinfo($filename);
        $prop = $targ_w / $targ_h;
        $ext = $info['extension'];
        $src = $path . $filename;
        $newfilename = $src;
        $info = getimagesize($src);
        //echo $targ_w .'---'. $targ_h; exit;
        if (empty($info))
            return '';
        $w = $info[0];
        $h = $info[1];
        if($w < $targ_w && $h < $targ_h) {
            return true;
        }
        
        $prw = $w / $targ_w;
        $prh = $h / $targ_h;
        
        if($prw > $prh) {
            $new_w = (int) ($w / $prw);
            $new_h = (int) ($h / $prw);
        } else {
            $new_w = (int) ($w / $prh);
            $new_h = (int) ($h / $prh);
        }

        if (strtoupper($ext) == strtoupper("gif")) {
            $img_r = imagecreatefromgif($src);
            $dst_r = ImageCreate($new_w, $new_h);
            imagefilledrectangle($dst_r, 0, 0, $targ_w, $targ_h, $black);
            imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else if (strtoupper($ext) == strtoupper("png")) {
            $img_r = imagecreatefrompng($src);
            $dst_r = ImageCreateTrueColor($new_w, $new_h);
            imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else {
            $img_r = imagecreatefromjpeg($src);
            $dst_r = ImageCreateTrueColor($new_w, $new_h);
            imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        }
    }

    public static function resize_image1($filename, $path, $targ_w = 664, $targ_h = 378, $change_proportion = true) {
        $jpeg_quality = 90;
        $info = pathinfo($filename);
        $prop = $targ_w / $targ_h;
        $ext = $info['extension'];
        $src = $path . $filename;
        $newfilename = $src;
        $info = getimagesize($src);
        if (empty($info))
            return '';
        $w = $info[0];
        $h = $info[1];
        $prop1 = $w / $h;
        if ($prop1 > $prop) {
            $add_black = false;
        } else {
            $add_black = true;
        }
        if ($targ_h == 0) {
            $targ_h = round(($h * $targ_w) / $w);
        }
        $startX = 0;
        $startY = 0;
        if (!$change_proportion) {
            $pr1 = $targ_w / $w;
            $pr2 = $targ_h / $h;
            if ($add_black) {
                if ($pr1 < $pr2) {
                    $w = $w * $pr1;
                    $h = $h * $pr1;
                    $startY = (int) (($targ_h - $h) / 2);
                } else {
                    $w = (int) ($w * $pr2);
                    $h = (int) ($h * $pr2);
                    $startX = (int) (($targ_w - $w) / 2);
                }
                $new_w = $info[0];
                $new_h = $info[1];
                $new_dest_w = $w;
                $new_dest_h = $h;
            } else {
                if ($w > $h) {
                    $pr = $w / $h;
                    if ($pr < $prop) {
                        $h = (int) ($w / $prop);
                    } else {
                        $w = (int) ($h * $prop);
                    }
                } else {
                    $pr = $w / $h;
                    if ($pr < $prop) {
                        $h = (int) ($w / $prop);
                    } else {
                        $w = (int) ($h * $prop);
                    }
                }
                $new_w = $w;
                $new_h = $h;
                $new_dest_w = $targ_w;
                $new_dest_h = $targ_h;
            }
        } else {
            $new_w = $w;
            $new_h = $h;
            $new_dest_w = $targ_w;
            $new_dest_h = $targ_h;
        }
        if (strtoupper($ext) == strtoupper("gif")) {
            $img_r = imagecreatefromgif($src);
            $dst_r = ImageCreate($targ_w, $targ_h);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($dst_r, 0, 0, $targ_w, $targ_h, $black);
            imagecopyresampled($dst_r, $img_r, $startX, $startY, 0, 0, $new_dest_w, $new_dest_h, $new_w, $new_h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else if (strtoupper($ext) == strtoupper("png")) {
            $img_r = imagecreatefrompng($src);
            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);
            imagecopyresampled($dst_r, $img_r, $startX, $startY, 0, 0, $new_dest_w, $new_dest_h, $new_w, $new_h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else {
            $img_r = imagecreatefromjpeg($src);
            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);
            imagecopyresampled($dst_r, $img_r, $startX, $startY, 0, 0, $new_dest_w, $new_dest_h, $new_w, $new_h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        }
    }

    public static function create_thumbnail_for_video($filename, $th_name = '', $gallery_id, $targ_w = 93, $targ_h = 78) {
        if (empty($gallery_id))
            return '';
        if (!empty($th_name))
            $new_file_name = $th_name;
        else
            $new_file_name = 's' . $filename;
        $path = SITE_PATH . 'public/videos/' . $gallery_id . '/';
        self::create_thumbnail($path, $filename, $new_file_name, $targ_w, $targ_h);
    }

    public static function create_thumbnail_for_page($filename, $page_id, $targ_w = 93, $targ_h = 78) {
        if (empty($page_id))
            return '';
        $path = SITE_PATH . 'public/images/pages/' . $page_id . '/';
        self::create_thumbnail($path, $filename, 's' . $filename, $targ_w, $targ_h);
    }

    public static function create_thumbnail_for_gallery($filename, $cat_name, $targ_w = 93, $targ_h = 78) {
      
        if (empty($cat_name))
            return '';
        $info = pathinfo($filename);
        $filename = $info['basename'];
        $path = SITE_PATH . 'public/images/categories/' . $cat_name . '/';
        self::create_thumbnail($path, $filename, 'small.jpg', $targ_w, $targ_h);
    }

    public static function create_thumbnail($path, $filename, $newfilename, $targ_w = 93, $targ_h = 78) {
        $src = $path . $filename;
        $newfilename = $path . $newfilename;
        $jpeg_quality = 90;
        $prop = $targ_w / $targ_h;
        $info = pathinfo($src);
        $ext = $info['extension'];
        $info = getimagesize($src);
        if (empty($info))
            return '';
        $w = $info[0];
        $h = $info[1];
        if ($targ_h == 0) {
            $targ_h = round(($h * $targ_w) / $w);
        }
        if ($w > $h) {
            $pr = $w / $h;
            if ($pr < $prop) {
                $h = (int) ($w / $prop);
            } else {
                $w = (int) ($h * $prop);
            }
        } else {
            $pr = $w / $h;
            if ($pr < $prop) {
                $h = (int) ($w / $prop);
            } else {
                $w = (int) ($h * $prop);
            }
        }
        $startX = 0;
        $startY = 0;
        if ($w < $info[0]) {
            $startX = (int) (($info[0] - $w) / 2);
        }
        if ($h < $info[1]) {
            $startY = (int) (($info[1] - $h) / 2);
        }
        if (strtoupper($ext) == strtoupper("gif")) {
            $img_r = imagecreatefromgif($src);
            $dst_r = ImageCreate($targ_w, $targ_h);
            imagecopyresampled($dst_r, $img_r, 0, 0, $startX, $startY, $targ_w, $targ_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else if (strtoupper($ext) == strtoupper("png")) {
            $img_r = imagecreatefrompng($src);
            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);
            imagecopyresampled($dst_r, $img_r, 0, 0, $startX, $startY, $targ_w, $targ_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        } else {
            $img_r = imagecreatefromjpeg($src);
            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);
            imagecopyresampled($dst_r, $img_r, 0, 0, $startX, $startY, $targ_w, $targ_h, $w, $h);
            if (imagejpeg($dst_r, $newfilename, $jpeg_quality)) {
                
            }
        }
    }

    public static function create_grayscale($filename, $project_id) {
        $info = pathinfo($filename);
        $ext = $info['extension'];

        $oldfilename = SITE_PATH . 'public/images/thumbnails/' . $project_id . '.' . $ext;
        $newfilename = SITE_PATH . 'public/images/thumbnails_gs/' . $project_id . '.jpg';

        // Get the dimensions
        list($width, $height) = getimagesize($oldfilename);

        //echo $width . ' ' . $height; exit;
        // Define our source image
        if (strtoupper($ext) == strtoupper("gif")) {
            $source = imagecreatefromgif($oldfilename);
        } else if (strtoupper($ext) == strtoupper("png")) {
            $source = imagecreatefrompng($oldfilename);
        } else {
            $source = imagecreatefromjpeg($oldfilename);
        }

        // Creating the Canvas 
        $bwimage = imagecreate($width, $height);

        //Creates the 256 color palette
        for ($c = 0; $c < 256; $c++) {
            $palette[$c] = imagecolorallocate($bwimage, $c, $c, $c);
        }

        //Reads the origonal colors pixel by pixel 
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($source, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                //This is where we actually use yiq to modify our rbg values, and then convert them to our grayscale palette
                $gs = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
                imagesetpixel($bwimage, $x, $y, $palette[$gs]);
            }
        }

        // Outputs a jpg image, but you can change this to png or gif if that is what you are working with
        unlink($newfilename);
        imagejpeg($bwimage, $newfilename);
    }

    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    public static function refresh() {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    public static function get_global($varname) {
        if (empty($varname))
            return "";
        if (!empty($_GET[$varname]))
            return $_GET[$varname];
        else if (!empty($_POST[$varname]))
            return $_POST[$varname];
    }

    public static function convert_date_from_datepicker_to_mysql($date) {
        $p = explode('/', $date);
        return sizeof($p) == 3 ? $p[2] . '-' . $p[0] . '-' . $p[1] : '';
    }

    public static function convert_date_from_mysql_to_datepicker($date) {
        $p = explode('-', $date);
        return sizeof($p) == 3 ? $p[1] . '/' . $p[2] . '/' . $p[0] : '';
    }

    public static function generateRandomName($length = 20) {
        $str = '';
        $char_set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 1; $i <= $length; $i++) {
            $str .= substr($char_set, mt_rand(0, strlen($char_set) - 1), 1);
        }
        return $str;
    }

    public static function check_file_extention($field_name) {
        if ($_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
            $path_parts = pathinfo($_FILES[$field_name]['name']);
            if (in_array($path_parts ['extension'], array('jpg', 'gif', 'png'))) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }
    
    public static function generate_random_string($length) {
        $password = '';
        $char_set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 1; $i <= $length; $i++) {
            $password .= substr($char_set, mt_rand(0, strlen($char_set)-1), 1);
        }
        return $password;
    }
    
    public static function chmod_r($dir, $dirPermissions, $filePermissions) {
      $dp = opendir($dir);
       while($file = readdir($dp)) {
         if (($file == ".") || ($file == ".."))
            continue;

        $fullPath = $dir."/".$file;

         if(is_dir($fullPath)) {
            //echo('DIR:' . $fullPath . "\n");
            chmod($fullPath, $dirPermissions);
            self::chmod_r($fullPath, $dirPermissions, $filePermissions);
         } else {
            //echo('FILE:' . $fullPath . "\n");
            chmod($fullPath, $filePermissions);
         }

       }
     closedir($dp);
  }
}