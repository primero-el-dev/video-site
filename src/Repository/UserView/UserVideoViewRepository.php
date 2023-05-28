<?php

namespace App\Repository\UserView;

use App\Entity\UserView\UserVideoView;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends UserViewRepository<UserVideoView>
 *
 * @method UserVideoView|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserVideoView|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserVideoView[]    findAll()
 * @method UserVideoView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserVideoViewRepository extends UserViewRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVideoView::class);
    }
}
