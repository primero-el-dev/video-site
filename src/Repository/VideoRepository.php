<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return Video[]
     */
    public function findByConstraints(array $params): array
    {
        $qb = $this->createQueryBuilder('v');

        if (isset($params['owner'])) {
            $qb->andWhere('v.owner = :owner')->setParameter(':owner', $params['owner']);
        }
        if (isset($params['name'])) {
            $qb->andWhere('v.name LIKE :name')->setParameter(':name', '%'.$params['name'].'%');
        }
        if (isset($params['description'])) {
            $qb->andWhere('v.description LIKE :description')->setParameter(':description', '%'.$params['description'].'%');
        }
        if (isset($params['limit'])) {
            $qb->setMaxResults((int) $params['limit']);
        }
        if (isset($params['offset'])) {
            $qb->setFirstResult((int) $params['offset']);
        }
        if (isset($params['order'])) {
            $orderBy = match($params['order-by'] ?? null) {
                'id' => 'v.id',
                'name' => 'v.name',
                'description' => 'v.description',
                'created-at' => 'v.createdAt',
                'views' => 'COUNT(v.userVideoView)',
                default => 'v.id',
            };
            $qb->orderBy($orderBy, ($params['order'] === 'desc') ? 'DESC' : 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
