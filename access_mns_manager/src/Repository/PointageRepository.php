<?php

namespace App\Repository;

use App\Entity\Pointage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pointage>
 */
class PointageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pointage::class);
    }

    /**
     * @return Pointage[] Returns an array of Pointage objects for a specific organisation
     */
    public function findByOrganisation($organisationId, $limit = null): array
    {
        // Use a simpler approach to avoid entity loading issues
        $sql = '
            SELECT p.* FROM pointage p
            INNER JOIN badge b ON p.badge_id = b.id
            INNER JOIN user_badge ub ON b.id = ub.badge_id
            INNER JOIN "user" usr ON ub.utilisateur_id = usr.id
            INNER JOIN travailler t ON usr.id = t.utilisateur_id
            INNER JOIN service s ON t.service_id = s.id
            WHERE s.organisation_id = :organisationId
            ORDER BY p.heure DESC
        ';

        // Add limit if specified
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT :limit';
        }

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $params = ['organisationId' => $organisationId];

        if ($limit !== null && $limit > 0) {
            $params['limit'] = $limit;
        }

        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        // Convert to entities
        $pointages = [];
        foreach ($rows as $row) {
            $pointage = $this->find($row['id']);
            if ($pointage) {
                $pointages[] = $pointage;
            }
        }

        return $pointages;
    }
}
