<?php

namespace App\Util;

use App\Util\FileHandlerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileHandler implements FileHandlerInterface
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {}

    /**
     * @throws FileException
     */
    public function uploadImage(UploadedFile $file): string
    {
        return $this->uploadFile($file, $_ENV['IMAGES_PATH']);
    }

    /**
     * @throws FileException
     */
    public function uploadVideo(UploadedFile $file): string
    {
        return $this->uploadFile($file, $_ENV['VIDEOS_PATH']);
    }

    /**
     * @throws FileException
     */
    public function uploadFile(UploadedFile $file, string $path): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $this->slugger->slug($originalFilename).'-'.uniqid().'.'.$file->guessExtension();
        $file->move($path, $newFilename);

        return $newFilename;
    }

    public function deleteImage(string $filename): bool
    {
        return $this->deleteFile($filename, $_ENV['IMAGES_PATH']);
    }

    public function deleteVideo(string $filename): bool
    {
        return $this->deleteFile($filename, $_ENV['VIDEOS_PATH']);
    }

    public function deleteFile(string $filename, string $path): bool
    {
        $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        
        return unlink($fullPath);
    }
}