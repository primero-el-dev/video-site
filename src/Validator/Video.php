<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\File;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Video extends File
{
    public array|string|null $extensions = ['ogg', 'webm', 'mp4', 'wav', 'mp3']; 
    public $maxSizeMessage = 'validator.video.maxSize';
    public string $extensionsMessage = 'validator.video.extensions';
    public $mimeTypesMessage = 'validator.video.mimeTypes';
    public $notFoundMessage = 'validator.video.notFound';
    public $notReadableMessage = 'validator.video.notReadable';
    public $uploadCantWriteErrorMessage = 'validator.video.uploadCantWriteError';
    public $uploadErrorMessage = 'validator.video.uploadError';
    public $uploadExtensionErrorMessage = 'validator.video.uploadExtensionError';
    public $uploadFormSizeErrorMessage = 'validator.video.uploadFormSizeError';
    public $uploadIniSizeErrorMessage = 'validator.video.uploadIniSizeError';
    public $uploadNoFileErrorMessage = 'validator.video.uploadNoFileError';
    public $uploadNoTmpDirErrorMessage = 'validator.video.uploadNoTmpDirError';
    public $uploadPartialErrorMessage = 'validator.video.uploadPartialError';
}
