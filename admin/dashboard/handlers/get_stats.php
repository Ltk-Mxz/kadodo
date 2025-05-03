<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Stats de base avec colonnes existantes
    $baseStats = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM utilisateur u 
             INNER JOIN role r ON u.id_role = r.id_role 
             WHERE r.nom_role = 'etudiant') as total_etudiants,
            
            (SELECT COUNT(*) FROM utilisateur u 
             INNER JOIN role r ON u.id_role = r.id_role 
             WHERE r.nom_role = 'professeur') as total_professeurs,
            
            (SELECT COUNT(*) FROM utilisateur u 
             INNER JOIN role r ON u.id_role = r.id_role 
             WHERE r.nom_role = 'professeur' 
             AND u.statut_compte = 'actif') as professeurs_actifs,
            
            (SELECT COUNT(*) FROM salle) as total_salles,
            
            (SELECT COUNT(*) FROM salle 
             WHERE disponible = 1) as salles_disponibles,
            
            (SELECT COUNT(*) FROM filiere) as total_filieres,
            
            (SELECT COUNT(*) FROM classe) as total_classes,

            (SELECT COUNT(*) FROM cours) as total_cours,
            
            (SELECT COUNT(*) FROM cours 
             WHERE id_professeur > 0) as cours_actifs
    ")->fetch(PDO::FETCH_ASSOC);

    // Stats étudiants année précédente
    $lastYearStudents = $db->query("
        SELECT COUNT(*) FROM utilisateur u 
        JOIN role r ON u.id_role = r.id_role 
        WHERE r.nom_role = 'etudiant' 
        AND YEAR(u.date_mise_a_jour) = YEAR(CURRENT_DATE - INTERVAL 1 YEAR)
    ")->fetchColumn();

    // Calcul évolution étudiants
    $currentStudents = $baseStats['total_etudiants'];
    $evolutionEtudiants = $lastYearStudents > 0
        ? (($currentStudents - $lastYearStudents) / $lastYearStudents) * 100
        : 0;

    // Stats financières
    $financeStats = $db->query("
        SELECT 
            COALESCE(SUM(montant), 0) as revenu_annuel,
            COUNT(CASE WHEN statut_paiement = 'en_retard' THEN 1 END) as paiements_retard,
            COALESCE(SUM(CASE WHEN statut_paiement = 'en_retard' THEN montant ELSE 0 END), 0) as montant_retard
        FROM transactions 
        WHERE YEAR(date_paiement) = YEAR(CURRENT_DATE)
    ")->fetch(PDO::FETCH_ASSOC);

    // Évolution mensuelle des revenus
    $evolutionRevenu = $db->query("
        SELECT 
            COALESCE(((THIS_MONTH.total - LAST_MONTH.total) / NULLIF(LAST_MONTH.total, 0) * 100), 0) as evolution
        FROM (
            SELECT COALESCE(SUM(montant), 0) as total 
            FROM transactions 
            WHERE DATE_FORMAT(date_paiement, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
        ) THIS_MONTH,
        (
            SELECT COALESCE(SUM(montant), 0) as total 
            FROM transactions 
            WHERE DATE_FORMAT(date_paiement, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m')
        ) LAST_MONTH
    ")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'effectifs' => [
                'total_etudiants' => (int)$baseStats['total_etudiants'],
                'total_professeurs' => (int)$baseStats['total_professeurs'],
                'professeurs_actifs' => (int)$baseStats['professeurs_actifs'],
                'total_salles' => (int)$baseStats['total_salles'],
                'salles_disponibles' => (int)$baseStats['salles_disponibles'],
                'total_filieres' => (int)$baseStats['total_filieres'],
                'total_classes' => (int)$baseStats['total_classes']
            ],
            'finances' => [
                'revenu_annuel' => (float)$financeStats['revenu_annuel'],
                'paiements_retard' => (int)$financeStats['paiements_retard'],
                'montant_retard' => (float)$financeStats['montant_retard'],
                'evolution_revenu' => round($evolutionRevenu['evolution'], 1)
            ],
            'academic' => [
                'total_cours' => (int)$baseStats['total_cours'],
                'cours_actifs' => (int)$baseStats['cours_actifs']
            ],
            'evolution_etudiants' => round($evolutionEtudiants, 1)
        ]
    ]);
} catch (Exception $e) {
    error_log("Erreur dans get_stats.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de la récupération des statistiques'
    ]);
}
