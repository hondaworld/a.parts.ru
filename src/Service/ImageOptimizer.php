<?php


namespace App\Service;


use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    private $maxWidth;
    private $maxHeight;

    private $imagine;

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    public function resize(string $filename, int $maxWidth = 1600, int $maxHeight = 1600): void
    {
        list($iwidth, $iheight) = getimagesize($filename);
        $ratio = $iwidth / $iheight;
        $width = $maxWidth;
        $height = $maxHeight;

        if ($iwidth > $width || $iheight > $height) {
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $photo = $this->imagine->open($filename);
            $photo->resize(new Box($width, $height))->save($filename);
        }
    }

    public function orientation(string $filename): void
    {
        $exif = @exif_read_data($filename);
        if (false !== $exif) {

            if (isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                $resImage = imagecreatefromstring(file_get_contents($filename));
                switch ($orientation) {
                    case 3:
                        // Поворачиваем изображение на 180 градусов, вверх ногами
                        $resImage = imagerotate($resImage, 180, 0);
                        break;
                    case 6:
                        // Поворачиваем изображение на 90 градусов по часовой стрелки
                        $resImage = imagerotate($resImage, 270, 0);
                        break;
                    case 8:
                        // Поворачиваем изображение на 90 градусов против часовой стрелки
                        $resImage = imagerotate($resImage, 90, 0);
                        break;
                }
                imagejpeg($resImage, $filename);
            }
        }


//        $img = new Imagick($filename);
//        $orientation = $img->getImageOrientation();
//        switch ($orientation) {
//            case Imagick::ORIENTATION_BOTTOMRIGHT:
//                $img->rotateimage("#000", 180); // rotate 180 degrees
//                break;
//            case Imagick::ORIENTATION_RIGHTTOP:
//                $img->rotateimage("#000", 90); // rotate 90 degrees CW
//                break;
//            case Imagick::ORIENTATION_LEFTBOTTOM:
//                $img->rotateimage("#000", -90); // rotate 90 degrees CCW
//                break;
//        }
//        $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
//        $img->writeImage($filename);
//        $img->clear();
//        $img->destroy();
    }
}