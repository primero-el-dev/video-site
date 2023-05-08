<?php

namespace App\Repository;

use App\Entity\UserCommentAction;
use App\Repository\Traits\DeleteByTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCommentAction>
 *
 * @method UserCommentAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCommentAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCommentAction[]    findAll()
 * @method UserCommentAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommentActionRepository extends ServiceEntityRepository
{
    use DeleteByTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCommentAction::class);
    }

    public function save(UserCommentAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserCommentAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserCommentAction[] Returns an array of UserCommentAction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserCommentAction
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
