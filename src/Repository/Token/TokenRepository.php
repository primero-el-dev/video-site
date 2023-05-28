<?php

namespace App\Repository\Token;

use App\Entity\Token\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Token>
 *
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $tokenClass = Token::class)
    {
        parent::__construct($registry, $tokenClass);
    }

    public function save(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneValidByValue(string $value): ?Token
    {
        return $this
            ->createQueryBuilder('t')
            ->andWhere('t.value = :value')
            ->andWhere('t.expiry >= :now')
            ->setParameters([
                'value' => $value,
                'now' => new DateTime(),
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function popOneValidByValue(string $value): ?Token
    {
        $token = $this->findOneValidByValue($value);

        if ($token) {
            $this->remove($token);

            return $token;
        } else {
            return null;
        }
    }
}
