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
        if (!in_array($mimeType, $allowedTypes)) return false;

        // Reject overly large files by size (20 MB limit)
        $maxFileSize = 20 * 1024 * 1024; // 20 MB
        if (filesize($filePath) > $maxFileSize) {
            return false;
        }

        // Get image dimensions and validate
        $info = @getimagesize($filePath);
        if ($info === false) return false;

        $maxWidth = 5000;
        $maxHeight = 5000;
        if ($info[0] > $maxWidth || $info[1] > $maxHeight) {
            return false;
        }

        return true;
    }

    public function load($filename)
    {
        if (!self::isValidImage($filename)) {
            $_SESSION['error'] = "Invalid or unsupported image type.";
            return false;
        }

        $info = @getimagesize($filename);
        if ($info === false) {
            $_SESSION['error'] = "Could not read image information.";
            return false;
        }

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
                $_SESSION['error'] = "Unsupported image type.";
                return false;
        }

        return true;
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

    public function resizeToFit($filePath, $maxWidth, $maxHeight)
    {
        if (!$this->load($filePath)) {
            return false;
        }

        $width = imagesx($this->image);
        $height = imagesy($this->image);

        // Only resize if the image exceeds allowed dimensions
        if ($width > $maxWidth || $height > $maxHeight) {
            $widthRatio = $maxWidth / $width;
            $heightRatio = $maxHeight / $height;

            // Use the smaller ratio to maintain aspect ratio and fit within limits
            $scale = min($widthRatio, $heightRatio);

            $newWidth = (int)($width * $scale);
            $newHeight = (int)($height * $scale);

            $this->resize($newWidth, $newHeight);
            $this->save($filePath, $this->imageType);
        }

        return true;
    }

    public function resizePostImage($filePath)
    {
        $this->resizeToFit($filePath, 800, 800);
    }

    public function resizeAvatarImage($filePath)
    {
        $this->resizeToFit($filePath, 256, 256);
    }
}