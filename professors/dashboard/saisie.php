<?php
ob_start();
session_start();
require_once '../../config/database.php';

if (!isset($_POST['selectF']) || !isset($_POST['selectS'])) {
  $_SESSION['error'] = "Données manquantes";
  header("Location: ../dashboard");
  exit;
}

$filiereCours = $_POST['selectF'];
$Semestre = $_POST['selectS'];

try {
  $db = Database::getInstance()->getConnection();

  // Modification de la vérification pour accepter soit un id_attribution soit un id_cours
  $stmt = $db->prepare("
        SELECT c.id_cours 
        FROM cours c
        LEFT JOIN attribution_cours ac ON c.id_cours = ac.id_cours
        WHERE c.id_cours = ? OR ac.id_attribution = ?
    ");

  $stmt->execute([$filiereCours, $filiereCours]);

  if ($stmt->rowCount() == 0) {
    throw new Exception("Cours non trouvé");
  }

  // Stocker les données dans la session
  $_SESSION['SaisieNotes'] = [
    "filiereCours" => $filiereCours,
    "Semestre" => $Semestre
  ];

  header("Location: ../grades");
} catch (Exception $e) {
  $_SESSION['error'] = $e->getMessage();
  header("Location: ../dashboard");
  exit;
}

ob_end_flush();
exit;
?>

<?php foreach ($profCours as $profCour) { ?>
  <div class="course-card">
    <div class="course-header">
      <h2 class="course-title"><?= $profCour['libelle_filiere'] ?></h2>
      <span class="course-status">En cours</span>
    </div>
    <div class="course-info">
      <p><i class="bi bi-people"></i>32 Élèves</p>
      <p><i class="bi bi-book"></i><?= $profCour['lib_cours'] ?></p>
      <p><i class="bi bi-clock"></i>6 Heures par semaine</p>
    </div>
    <button class="btn btn-primary w-100">Voir Détails</button>
  </div>
<?php } ?>