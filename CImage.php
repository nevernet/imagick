<?php
/**
 * Image helper for yii framework
 * https://github.com/nevernet/imagick
 *
 * Copyright 2013, Daniel Qin <xin.qin@qinx.org>
 *
 * Licensed under the MIT license
 * Redistributions of part of code must retain the above copyright notice.
 *
 * @author Daniel Qin <xin.qin@qinx.org>
 * @version 1.0.0
 * @copyright Copyright 2010, Daniel Qin <xin.qin@qinx.org>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * usage:
 * <code>
 * $ci = new CImage;
 *     $array = array(
 *          "p/14/08/18/share_test25.jpg",
 *          "p/14/08/18/share_test29.jpg",
 *          "p/14/08/18/share_test20.jpg",
 *
 *          "p/14/08/18/share_test21.jpg",
 *          "p/14/08/18/share_test22.jpg",
 *          "p/14/08/18/share_test23.jpg",
 *
 *          "p/14/08/18/share_test24.jpg",
 *
 *           "p/14/08/18/share_test26.jpg",
 *           "p/14/08/18/share_test27.jpg",
 *       );
 *       $result = $ci->composite($array);
 *       if(!empty($result)){
 *           $result = FILE_BASE_URL_ . $result;
 *           echo "<img src='{$result}' />";
 *       }
 *  </code>
 */

class CImage extends CApplicationComponent{

    public $width = 64;
    public $height = 64;

    public $margin = 4;

    /**
     * 生成一个九宫格
     *
     * @param array $images
     * @throws Exception
     */
    public function composite($images, $type=3, $withFullUrl=false){
        if(empty($images)) return '';

        $pathRoot = Yii::getPathOfAlias('webroot');
        $targetPath = $pathRoot . '/images/www/pg_bg1.png';
        $relativePath_ = "p/". date('y/m/d/', time());
        $namePrefix = "p{$type}_";
        $writePath = FILE_STORAGE_ROOT_ . $relativePath_;
        if (!File::ensureDir($writePath))
            return false;

        try{
            //Creating two Imagick object
            $target = new Imagick($targetPath);
            $offsetX = 0;
            $offsetY = 0;

            if(count($images) == 1){
                $offsetX = $this->width + $this->margin;
                $offsetY = $this->height + $this->margin;

                $second = new Imagick($secondPath);
                $second->resizeimage($this->width, $this->height, Imagick::FILTER_CATROM, 1);
                $target->setImageColorspace($second->getImageColorspace());
                $target->compositeImage($second, $second->getImageCompose(), $offsetX, $offsetY);
                $second->clear();
                $second->destroy();
            }else{
                $i = 0;
                $y = 0;
                $x = 0;
                foreach ($images as $img){
                    if($i>1 && $i%3 == 0){
                        $y++;
                    }
                    $x = $i%3; //x - coordinate
                    if($i>1 && $i%3 == 0){
                        $x = 0;
                    }

                    $offsetX = ($this->width + $this->margin) * $x;
                    $offsetY = ($this->height + $this->margin) * $y;
                    $secondPath = FILE_STORAGE_ROOT_ . $img;
                    if(!file_exists($secondPath)){
                        throw new Exception("{$secondPath} does not exists.");
                    }

                    $second = new Imagick($secondPath);
                    $second->resizeimage($this->width, $this->height, Imagick::FILTER_CATROM, 1);
                    $target->setImageColorspace($second->getImageColorspace());
                    $target->compositeImage($second, $second->getImageCompose(), $offsetX, $offsetY);
                    $second->clear();
                    $second->destroy();

                    $i++;
                }
            }

            $name = $namePrefix.md5(Utility::uniqid());
            $storedFileName = $name . ".png";
            $result = $relativePath_ . $storedFileName;
            if($withFullUrl)
                $result = FILE_BASE_URL_ . $result;

            $target->writeImage($writePath . $storedFileName);
            $target->clear();
            $target->destroy();

            return $result;

        }catch(Exception $exc){
            throw $exc;
        }
    }
}
