<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationForComment;
use App\Repository\Notification\NotificationRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends NotificationRepository<NotificationForComment>
 *
 * @method NotificationForComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationForComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationForComment[]    findAll()
 * @method NotificationForComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationForCommentRepository extends NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationForComment::class);
    }
}
