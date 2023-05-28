<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationForUser;
use App\Repository\Notification\NotificationRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends NotificationRepository<NotificationForUser>
 *
 * @method NotificationForUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationForUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationForUser[]    findAll()
 * @method NotificationForUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationForUserRepository extends NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationForUser::class);
    }
}
