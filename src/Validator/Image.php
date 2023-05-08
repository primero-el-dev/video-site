<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\File;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Image extends File
{
    public array|string|null $extensions = ['apng', 'png', 'avif', 'jpg', 'jpeg', 'webp', 'svg', 'bmp']; 
    public $maxSizeMessage = 'validator.image.maxSize';
    public string $extensionsMessage = 'validator.image.extensions';
    public $mimeTypesMessage = 'validator.image.mimeTypes';
    public $notFoundMessage = 'validator.image.notFound';
    public $notReadableMessage = 'validator.image.notReadable';
    public $uploadCantWriteErrorMessage = 'validator.image.uploadCantWriteError';
    public $uploadErrorMessage = 'validator.image.uploadError';
    public $uploadExtensionErrorMessage = 'validator.image.uploadExtensionError';
    public $uploadFormSizeErrorMessage = 'validator.image.uploadFormSizeError';
    public $uploadIniSizeErrorMessage = 'validator.image.uploadIniSizeError';
    public $uploadNoFileErrorMessage = 'validator.image.uploadNoFileError';
    public $uploadNoTmpDirErrorMessage = 'validator.image.uploadNoTmpDirError';
    public $uploadPartialErrorMessage = 'validator.image.uploadPartialError';
}
