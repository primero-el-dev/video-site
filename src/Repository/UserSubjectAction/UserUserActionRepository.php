<?php

namespace App\Repository\UserSubjectAction;

use App\Entity\UserSubjectAction\UserUserAction;
use App\Repository\UserSubjectAction\UserSubjectActionRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends UserSubjectActionRepository<UserUserAction>
 *
 * @method UserUserAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUserAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUserAction[]    findAll()
 * @method UserUserAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUserActionRepository extends UserSubjectActionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserUserAction::class);
    }
}
