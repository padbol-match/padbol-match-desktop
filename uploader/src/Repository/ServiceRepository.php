<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        parent::__construct($registry, Service::class);
        $this->manager = $manager;
    }

    /**
     * @return Service[]
     */
    public function findByUserId(string $userId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin(Tenant::class, 't', Join::WITH, 's.tenant = t')
            ->andWhere('t.user = :userId')
            ->setParameter('userId', $userId);

        $query = $qb->getQuery();

        return $query->getResult();
    }

}