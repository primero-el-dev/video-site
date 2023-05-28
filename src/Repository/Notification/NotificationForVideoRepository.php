<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationForVideo;
use App\Repository\Notification\NotificationRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends NotificationRepository<NotificationForVideo>
 *
 * @method NotificationForVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationForVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationForVideo[]    findAll()
 * @method NotificationForVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationForVideoRepository extends NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationForVideo::class);
    }
}
