<?php


// Thank you for the code, Kim. I had to modify it just a bit to accomodate my existing codebase. Here is the modified version:

class ImageResizer
{
    protected $image;
    protected $imageType;

    public static function isValidImage($filePath) {
        if (!file_exists($filePath)) return false;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        return in_array($mimeType, $allowedTypes);
    }

    public function load($filename)
    {
        if (!self::isValidImage($filename)) {
            throw new Exception("Invalid or unsupported image type.");
        }

        $info = getimagesize($filename);
        $this->imageType = $info[2];
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filename);
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filename);
                break;
            default:
                throw new Exception("Unsupported image type.");
        }
    }

    public function save($filename, $imageType = IMAGETYPE_JPEG, $compression = 90)
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, $filename, $compression);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image, $filename);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, $filename);
                break;
        }
    }

    public function resize($width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, imagesx($this->image), imagesy($this->image));
        $this->image = $newImage;
    }

    public function resizeToWidth($filePath, $maxWidth)
    {
        $this->load($filePath);
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newHeight = $height * $ratio;
            $this->resize($maxWidth, $newHeight);
            $this->save($filePath, $this->imageType);
        }
    }

    public function resizePostImage($filePath)
    {
        $this->resizeToWidth($filePath, 800);
    }

    public function resizeAvatarImage($filePath)
    {
        $this->resizeToWidth($filePath, 256);
    }
}