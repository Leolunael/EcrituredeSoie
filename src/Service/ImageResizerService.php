<?php
// src/Service/ImageResizerService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageResizerService
{
    private string $uploadDirectory;
    private int $maxWidth = 1200;
    private int $maxHeight = 1200;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadDirectory = $params->get('images_directory');
    }

    public function resize(UploadedFile $file): string
    {

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        $filePath = $this->uploadDirectory . '/' . $fileName;

        $imageInfo = getimagesize($file->getPathname());
        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($file->getPathname());
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($file->getPathname());
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($file->getPathname());
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($file->getPathname());
                break;
            default:
                throw new \Exception('Format d\'image non supporté');
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        if ($origWidth > $this->maxWidth || $origHeight > $this->maxHeight) {
            $ratio = min($this->maxWidth / $origWidth, $this->maxHeight / $origHeight);
            $newWidth = (int)($origWidth * $ratio);
            $newHeight = (int)($origHeight * $ratio);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $origWidth, $origHeight
        );

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, $filePath, 95);
                break;
            case 'image/png':
                imagepng($newImage, $filePath, 6);
                break;
            case 'image/gif':
                imagegif($newImage, $filePath);
                break;
            case 'image/webp':
                imagewebp($newImage, $filePath, 95);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $fileName;
    }

    public function setMaxDimensions(int $width, int $height): void
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
    }

    public function setUploadDirectory(string $directory): void
    {
        $this->uploadDirectory = $directory;
    }
}
