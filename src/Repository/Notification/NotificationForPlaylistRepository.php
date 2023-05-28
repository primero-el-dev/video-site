<?php

namespace App\Repository\Notification;

use App\Entity\Notification\NotificationForPlaylist;
use App\Repository\Notification\NotificationRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends NotificationRepository<NotificationForPlaylist>
 *
 * @method NotificationForPlaylist|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationForPlaylist|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationForPlaylist[]    findAll()
 * @method NotificationForPlaylist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationForPlaylistRepository extends NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationForPlaylist::class);
    }
}
