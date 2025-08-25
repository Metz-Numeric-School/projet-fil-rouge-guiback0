<?php

require_once './vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger l'environnement
$dotenv = new Dotenv();
$dotenv->load('.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'] ?? 'dev', false);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer l'entity manager
$entityManager = $container->get('doctrine')->getManager();

// 1. Lister tous les services
echo "=== SERVICES ===\n";
$services = $entityManager->createQuery(
    'SELECT s.id, s.nom_service, s.is_principal FROM App\Entity\Service s ORDER BY s.id'
)->getResult();

foreach ($services as $service) {
    $principal = $service['is_principal'] ? 'OUI' : 'NON';
    echo "ID: {$service['id']} - {$service['nom_service']} - Principal: $principal\n";
}

// 2. Lister les badgeuses et leurs services associés via Acces
echo "\n=== BADGEUSES ET SERVICES ===\n";
$badgeuses = $entityManager->createQuery(
    'SELECT b.id, b.reference, 
            z.nom_zone, 
            s.nom_service, s.is_principal
     FROM App\Entity\Badgeuse b
     JOIN b.acces a
     JOIN a.zone z
     JOIN z.serviceZones sz
     JOIN sz.service s
     ORDER BY b.id, s.id'
)->getResult();

foreach ($badgeuses as $badgeuse) {
    $principal = $badgeuse['is_principal'] ? 'OUI' : 'NON';
    echo "Badgeuse {$badgeuse['id']}: {$badgeuse['reference']} - Zone: {$badgeuse['nom_zone']} - Service: {$badgeuse['nom_service']} (Principal: $principal)\n";
}

echo "\n=== UTILISATEUR TEST ET SES SERVICES ===\n";
$user = $entityManager->createQuery(
    'SELECT u.id, u.email FROM App\Entity\User u WHERE u.email = :email'
)->setParameter('email', 'test@test.com')->getOneOrNullResult();

if ($user) {
    echo "User ID: {$user['id']} - Email: {$user['email']}\n";
    
    // Services de l'utilisateur
    $userServices = $entityManager->createQuery(
        'SELECT s.id, s.nom_service, s.is_principal 
         FROM App\Entity\Service s
         JOIN s.travail t
         WHERE t.user = :user'
    )->setParameter('user', $user['id'])->getResult();
    
    echo "Services assignés:\n";
    foreach ($userServices as $service) {
        $principal = $service['is_principal'] ? 'OUI' : 'NON';
        echo "  - Service {$service['id']}: {$service['nom_service']} (Principal: $principal)\n";
    }
}

?>