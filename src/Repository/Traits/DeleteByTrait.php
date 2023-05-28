<?php

namespace App\Repository\Traits;

trait DeleteByTrait
{
    /**
     * @param array<string, mixed> $criteria
     */
    public function deleteBy(array $criteria): void
    {
        $qb = $this->createQueryBuilder('e');
        $qb->delete();

        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                $qb->andWhere("e.$field = :$field")->setParameter(":$field", $value);
            } else {
                $qb->andWhere("e.$field IS NULL");
            }
        }

        $qb->getQuery()->execute();
    }
}