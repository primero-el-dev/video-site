<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return Comment[]
     */
    public function findByConstraints(array $params): array
    {
        $qb = $this->createQueryBuilder('c');

        if (isset($params['video'])) {
            $qb->andWhere('c.video = :video')->setParameter(':video', $params['video']);
        }
        if (isset($params['parent'])) {
            $qb->andWhere('c.parent = :parent')->setParameter(':parent', $params['parent']);
        } else {
            $qb->andWhere('c.parent IS NULL');
        }
        if (isset($params['owner'])) {
            $qb->andWhere('c.owner = :owner')->setParameter(':owner', $params['owner']);
        }
        if (isset($params['content'])) {
            $qb->andWhere('c.content LIKE :content')->setParameter(':content', '%'.$params['owner'].'%');
        }
        if (isset($params['min-order'])) {
            $qb->andWhere('c.order >= :minNumber')->setParameter(':minNumber', (int) $params['min-order']);
        }
        if (isset($params['max-order'])) {
            $qb->andWhere('c.order <= :maxNumber')->setParameter(':maxNumber', (int) $params['max-order']);
        }
        if (isset($params['limit'])) {
            $qb->setMaxResults((int) $params['limit']);
        }
        if (isset($params['order-dir'])) {
            $qb->orderBy('c.order', strcasecmp($params['order-dir'], 'desc') === 0 ? 'DESC' : 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
