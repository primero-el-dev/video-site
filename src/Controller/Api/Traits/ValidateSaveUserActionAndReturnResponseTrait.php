<?php

namespace App\Controller\Api\Traits;

use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\HasUserActionInterface;
use App\Exception\ApiException;
use App\Form\DynamicType;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Exception;

trait ValidateSaveUserActionAndReturnResponseTrait
{
    use EntityManagerTrait;
    use GetJsonContentOrThrowApiException;
    use GetFieldFirstFormErrorMapTrait;

    /**
     * @param array<string, mixed> $whereFields
     * @param array<string, Constraint[]> $additionalFieldsConstraints
     * @throws Exception
     * @throws ApiException
     */
    private function validateSaveUserActionAndReturnResponse(
        UserActionEnum $action, 
        array $whereFields,
        string $entityClass,
        Request $request, 
        array $additionalFieldsConstraints,
        string $message
    ): JsonResponse {
        if (!is_a($entityClass, HasUserActionInterface::class, true)) {
            throw new Exception("$entityClass isn't an instance of " . HasUserActionInterface::class);
        }
        $content = $this->getJsonContentOrThrowApiException($request);
        $actionEntity = $this->em->getRepository($entityClass)->findOneBy(
            array_merge($whereFields, ['action' => $action])
        );
        $actionEntity ??= new $entityClass();
        $form = $this->createForm(DynamicType::class, null, [
            'fields' => $additionalFieldsConstraints,
        ]);
        
        $form->submit($content);
        if ($form->isValid()) {
            $data = [];
            foreach (array_keys($additionalFieldsConstraints) as $field) {
                $data[$field] = $form->get($field)->getData();
            }

            $actionEntity
                ->setAction($action)
                ->setAdditionalInfo($data);
            
            foreach ($whereFields as $field => $value) {
                $actionEntity->{'set'.ucfirst($field)}($value);
            }

            $this->em->getRepository($entityClass)->save($actionEntity, true);

            return $this->json(
                [
                    'data' => $actionEntity,
                    'message' => $message,
                ],
                context: ['groups' => ['user_action']]
            );
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }
}