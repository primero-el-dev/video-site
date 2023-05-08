<?php

namespace App\Repository;

use App\Entity\UserVideoAction;
use App\Repository\Traits\DeleteByTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserVideoAction>
 *
 * @method UserVideoAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserVideoAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserVideoAction[]    findAll()
 * @method UserVideoAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserVideoActionRepository extends ServiceEntityRepository
{
    use DeleteByTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVideoAction::class);
    }

    public function save(UserVideoAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserVideoAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
