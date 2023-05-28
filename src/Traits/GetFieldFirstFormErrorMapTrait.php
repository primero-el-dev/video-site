<?php

namespace App\Traits;

use Symfony\Component\Form\FormInterface;

trait GetFieldFirstFormErrorMapTrait
{
	public function getFieldFirstFormErrorMap(FormInterface $form): array
	{
		$errors = [];
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && count($childForm->getErrors(true))) {
                $errors[$childForm->getName()] = $childForm->getErrors(true)[0]->getMessage();
            }
        }

        return $errors;
	}
}