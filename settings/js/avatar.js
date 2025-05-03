document.getElementById('photo-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Vérifier la taille
        if (file.size > 2 * 1024 * 1024) {
            alert('Le fichier est trop volumineux. Maximum 2MB.');
            this.value = '';
            return;
        }
        
        // Vérifier le type
        if (!['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/jfif'].includes(file.type)) {
            alert('Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou JFIF.');
            this.value = '';
            return;
        }
        
        // Prévisualiser l'image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-image').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});