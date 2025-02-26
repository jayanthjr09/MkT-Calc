<?php

namespace App\Repository;

use App\Entity\TemperatureReading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TemperatureReading>
 *
 * @method TemperatureReading|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemperatureReading|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemperatureReading[]    findAll()
 * @method TemperatureReading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemperatureReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemperatureReading::class);
    }

    // Add custom repository methods here
}