<?php

namespace App\Repository;

use App\Entity\DataSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DataSet>
 *
 * @method DataSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataSet[]    findAll()
 * @method DataSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataSet::class);
    }

    // Add custom repository methods below
}