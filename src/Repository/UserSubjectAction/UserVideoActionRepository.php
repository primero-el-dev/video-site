<?php

namespace App\Repository\UserSubjectAction;

use App\Entity\UserSubjectAction\UserVideoAction;
use App\Repository\UserSubjectAction\UserSubjectActionRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends UserSubjectActionRepository<UserVideoAction>
 *
 * @method UserVideoAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserVideoAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserVideoAction[]    findAll()
 * @method UserVideoAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserVideoActionRepository extends UserSubjectActionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVideoAction::class);
    }
}
