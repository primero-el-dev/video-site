<?php

namespace App\Repository\Notification;

use App\Entity\Notification\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $className = Notification::class)
    {
        parent::__construct($registry, $className);
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Notification[]
     */
    public function findForUser(User $user, bool $withSeen = true): array
    {
        $qb = $this->createQueryBuilder('n');
        $qb->where('n.user = :user')->setParameter('user', $user);

        for ($i = 0; $i < count($user->getRoles()); $i++) {
            $qb->orWhere("n.role LIKE :role_$i")->setParameter("role_$i", '%"' . $user->getRoles()[$i] . '"%');
        }

        return $qb
            ->addOrderBy('n.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
