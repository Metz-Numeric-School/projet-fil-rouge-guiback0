<?php

namespace App\Repository;

use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zone>
 */
class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    /**
     * Find zones that are not assigned to a specific service and belong to the same organisation
     * @param array $assignedZoneIds
     * @param Organisation|null $organisation
     * @return Zone[]
     */
    public function findAvailableZonesForService(array $assignedZoneIds = [], $organisation = null): array
    {
        $qb = $this->createQueryBuilder('z')
            ->orderBy('z.nom_zone', 'ASC');
        
        if (!empty($assignedZoneIds)) {
            $qb->andWhere('z.id NOT IN (:assignedIds)')
               ->setParameter('assignedIds', $assignedZoneIds);
        }
        
        if ($organisation) {
            $qb->join('z.serviceZones', 'sz')
               ->join('sz.service', 's')
               ->andWhere('s.organisation = :organisation')
               ->setParameter('organisation', $organisation);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Get query builder for zones that are not assigned to a specific service and belong to the same organisation
     * @param array $assignedZoneIds
     * @param Organisation|null $organisation
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findAvailableZonesForServiceQueryBuilder(array $assignedZoneIds = [], $organisation = null): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('z')
            ->orderBy('z.nom_zone', 'ASC');
        
        if (!empty($assignedZoneIds)) {
            $qb->andWhere('z.id NOT IN (:assignedIds)')
               ->setParameter('assignedIds', $assignedZoneIds);
        }
        
        if ($organisation) {
            $qb->join('z.serviceZones', 'sz')
               ->join('sz.service', 's')
               ->andWhere('s.organisation = :organisation')
               ->setParameter('organisation', $organisation);
        }
        
        return $qb;
    }

    /**
     * Find zones belonging to a specific organisation through ServiceZone relationships
     * @param Organisation $organisation
     * @return Zone[]
     */
    public function findByOrganisation($organisation): array
    {
        return $this->createQueryBuilder('z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->where('s.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->orderBy('z.nom_zone', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
