<?php

namespace App\Repository\Token;

use App\Entity\Token\RegistrationConfirmationToken;
use App\Repository\Token\TokenRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegistrationConfirmationToken>
 *
 * @method RegistrationConfirmationToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationConfirmationToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationConfirmationToken[]    findAll()
 * @method RegistrationConfirmationToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationConfirmationTokenRepository extends TokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationConfirmationToken::class);
    }
}
