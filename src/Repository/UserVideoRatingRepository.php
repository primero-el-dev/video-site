<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserVideoRating;
use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserVideoRating>
 *
 * @method UserVideoRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserVideoRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserVideoRating[]    findAll()
 * @method UserVideoRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserVideoRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVideoRating::class);
    }

    public function save(UserVideoRating $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserVideoRating $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return array<string, int>
    */
    public function countRatings(Video $video): array
    {
        $sql = 'SELECT COUNT(*) AS likes_count 
            FROM user_video_rating 
            WHERE video_id = :video AND rating = :like
            UNION ALL SELECT COUNT(*) AS dislikes_count 
            FROM user_video_rating 
            WHERE video_id = :video AND rating = :dislike
        ';

        $result = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery([
                ':video' => $video->getId(),
                ':like' => UserVideoRating::LIKE,
                ':dislike' => UserVideoRating::DISLIKE,
            ])
            ->fetchAllNumeric()
        ;

        return [
            'likes_count' => $result[0][0],
            'dislikes_count' => $result[1][0],
        ];
    }
}
