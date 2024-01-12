<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tenant;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,EntityManagerInterface $manager)
    {
        parent::__construct($registry, Appointment::class);
        $this->manager = $manager;
    }

    /**
     * @return Appointment[]
     */
    public function findByUserForToday(int $userId, string $gmtMinutes): array
    {
        $gmtHours = (-1) * intval($gmtMinutes) / 60;

        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin(Tenant::class, 't', Join::WITH, 'a.tenant = t');
            //->where('DATE_ADD(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') >= CURRENT_DATE()')
            //->andWhere('DATE_ADD(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') < DATE_ADD(CURRENT_DATE(),1,\'DAY\')')

        if($gmtHours >= 0){
            $qb
                ->where('DATE_ADD(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') >= DATE(DATE_ADD(CURRENT_TIMESTAMP(),' . $gmtHours . ',\'HOUR\'))')
                ->andWhere('DATE_ADD(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') < DATE_ADD(DATE(DATE_ADD(CURRENT_TIMESTAMP(),' . $gmtHours . ',\'HOUR\')),1,\'DAY\')');
        }else{
            $gmtHours = (-1) * $gmtHours;
            $qb
                ->where('DATE_SUB(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') >= DATE(DATE_SUB(CURRENT_TIMESTAMP(),' . $gmtHours . ',\'HOUR\'))')
                ->andWhere('DATE_SUB(FROM_UNIXTIME(a.startTime),' . $gmtHours . ',\'HOUR\') < DATE_ADD(DATE(DATE_SUB(CURRENT_TIMESTAMP(),' . $gmtHours . ',\'HOUR\')),1,\'DAY\')');
        }

        $qb
            ->andWhere('t.user = :userId')      
            ->setParameter('userId', $userId)
            ->orderBy('FROM_UNIXTIME(a.startTime)', 'ASC');
            
        $query = $qb->getQuery();

        //dump($query->getSql()); die;

        return $query->getResult();
    }

    /**
     * @return Appointment[]
     */
    public function findByUserForCurrentWeek(int $userId): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a, c')
            ->leftJoin(Tenant::class, 't', Join::WITH, 'a.tenant = t')
            ->leftJoin('a.customer', 'c')
            ->where('FROM_UNIXTIME(a.startTime) >= CURRENT_DATE()')
            ->andWhere('FROM_UNIXTIME(a.startTime) < DATE_ADD(CURRENT_DATE(),7,\'DAY\')')
            ->andWhere('t.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('FROM_UNIXTIME(a.startTime)', 'ASC')
            ->orderBy('a.id', 'ASC');

        $query = $qb->getQuery();

        return $query->getResult();
    }

}