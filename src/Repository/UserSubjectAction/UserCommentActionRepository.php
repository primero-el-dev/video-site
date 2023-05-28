<?php

namespace App\Repository\UserSubjectAction;

use App\Entity\UserSubjectAction\UserCommentAction;
use App\Repository\UserSubjectAction\UserSubjectActionRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends UserSubjectActionRepository<UserCommentAction>
 *
 * @method UserCommentAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCommentAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCommentAction[]    findAll()
 * @method UserCommentAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommentActionRepository extends UserSubjectActionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCommentAction::class);
    }
}
