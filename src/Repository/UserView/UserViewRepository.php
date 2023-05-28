<?php

namespace App\Repository\UserView;

use App\Entity\UserView\UserView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserView>
 *
 * @method UserView|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserView|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserView[]    findAll()
 * @method UserView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $className = UserView::class)
    {
        parent::__construct($registry, $className);
    }

    public function save(UserView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
