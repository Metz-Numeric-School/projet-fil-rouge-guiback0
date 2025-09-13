<?php

namespace App\Service\User;

use App\Entity\Organisation;

class OrganisationFormatterService
{
    public function formatOrganisationData(Organisation $organisation): array
    {
        return [
            'id' => $organisation->getId(),
            'nom_organisation' => $organisation->getNomOrganisation(),
            'email' => $organisation->getEmail(),
            'telephone' => $organisation->getTelephone(),
            'site_web' => $organisation->getSiteWeb(),
            'siret' => $organisation->getSiret(),
            'adresse' => [
                'numero_rue' => $organisation->getNumeroRue(),
                'suffix_rue' => $organisation->getSuffixRue(),
                'nom_rue' => $organisation->getNomRue(),
                'code_postal' => $organisation->getCodePostal(),
                'ville' => $organisation->getVille(),
                'pays' => $organisation->getPays()
            ]
        ];
    }
}