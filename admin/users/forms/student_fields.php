<div class="row g-3">
    <input type="hidden" name="user_id" value="">
    <div class="col-md-6">
        <label for="firstname" class="form-label">Prénom</label>
        <input type="text" class="form-control" id="firstname" name="firstname" required>
    </div>
    <div class="col-md-6">
        <label for="lastname" class="form-label">Nom</label>
        <input type="text" class="form-control" id="lastname" name="lastname" required>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>

    <?php
    // Récupération des filières
    $stmt = $db->query("SELECT id_filiere, libelle_filiere FROM filiere ORDER BY libelle_filiere");
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des niveaux
    $stmt = $db->query("SELECT id_niveau, libelle_niveau FROM niveau ORDER BY id_niveau");
    $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="col-md-6">
        <label for="filiere" class="form-label">Filière</label>
        <select class="form-select" id="filiere" name="filiere" required>
            <option value="">Choisir une filière...</option>
            <?php foreach ($filieres as $filiere): ?>
                <option value="<?= htmlspecialchars($filiere['id_filiere']) ?>">
                    <?= htmlspecialchars($filiere['libelle_filiere']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label for="niveau" class="form-label">Niveau</label>
        <select class="form-select" id="niveau" name="niveau" required>
            <option value="">Choisir un niveau...</option>
            <?php foreach ($niveaux as $niveau): ?>
                <option value="<?= htmlspecialchars($niveau['id_niveau']) ?>">
                    <?= htmlspecialchars($niveau['libelle_niveau']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label for="photo" class="form-label">Photo</label>
        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
    </div>
</div>