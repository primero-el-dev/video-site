<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileHandlerInterface
{
    /**
     * @throws FileException
     */
    public function uploadImage(UploadedFile $file): string;

    /**
     * @throws FileException
     */
    public function uploadVideo(UploadedFile $file): string;

    /**
     * @throws FileException
     */
    public function uploadFile(UploadedFile $file, string $path): string;
}