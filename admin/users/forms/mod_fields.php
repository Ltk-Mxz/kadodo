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
    <div class="col-12">
        <label for="permissions" class="form-label">Permissions</label>
        <select class="form-select" id="permissions" name="permissions[]" multiple required>
            <option value="FORUM">Forum</option>
            <option value="BLOG">Blog</option>
            <option value="CONTENT">Contenu</option>
            <option value="REPORTS">Signalements</option>
        </select>
    </div>
    <div class="col-12">
        <label for="photo" class="form-label">Photo de profil</label>
        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
    <button type="submit" class="btn btn-primary">Ajouter</button>
</div>