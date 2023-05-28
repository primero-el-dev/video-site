<?php

namespace App\Repository\UserSubjectAction;

use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Repository\Traits\DeleteByTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSubjectAction>
 *
 * @method UserSubjectAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSubjectAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSubjectAction[]    findAll()
 * @method UserSubjectAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSubjectActionRepository extends ServiceEntityRepository
{
    use DeleteByTrait;

    public function __construct(ManagerRegistry $registry, $entityClass = UserSubjectAction::class)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(UserSubjectAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserSubjectAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<string, mixed|mixed[]> $values
     * @return UserSubjectAction[]
     */
    public function findByValuesLists(array $values): array
    {
        $qb = $this->createQueryBuilder('e');

        foreach ($values as $field => $values) {
            if (is_array($values)) {
                $qb->andWhere("e.$field IN (:$field)")->setParameter($field, $values);
            } else {
                $qb->andWhere("e.$field = :$field")->setParameter($field, $values);
            }
        }
        
        return $qb->getQuery()->getResult();
    }
}
