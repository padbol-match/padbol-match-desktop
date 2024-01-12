<?php

namespace App\Repository;

use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Tenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tenant[]    findAll()
 * @method Tenant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,EntityManagerInterface $manager)
    {
        parent::__construct($registry, Tenant::class);
        $this->manager = $manager;
    }

    /**
     * @return Tenant[]
     */
    public function findTenantByUserEmail(string $email): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $this->createQueryBuilder('t')
            ->where('t.email = :email')
            ->setParameter('email', $email);

        $query = $qb->getQuery();

        return $query->getResult();
    }

}