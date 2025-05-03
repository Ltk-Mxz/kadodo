document.addEventListener('DOMContentLoaded', function() {
    const qrContainer = document.getElementById("qrcode");
    qrContainer.innerHTML = '';
    
    // Créer le conteneur principal pour le QR code et le bouton
    const qrWrapper = document.createElement('div');
    qrWrapper.style.display = 'flex';
    qrWrapper.style.flexDirection = 'column';
    qrWrapper.style.alignItems = 'center';
    qrWrapper.style.gap = '10px';
    qrContainer.appendChild(qrWrapper);
    
    // Créer le conteneur pour le QR code
    const qrImageContainer = document.createElement('div');
    qrWrapper.appendChild(qrImageContainer);
    
    // Créer le QR code
    const qrcode = new QRCode(qrImageContainer, {
        text: userId,
        width: 130,
        height: 130,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    // Ajouter le bouton de téléchargement
    const downloadButton = document.createElement('button');
    downloadButton.className = 'download-qr';
    downloadButton.innerHTML = 'Télécharger QR';
    qrWrapper.appendChild(downloadButton);

    // Fonction de téléchargement
    downloadButton.addEventListener('click', function() {
        const qrImage = qrImageContainer.querySelector('img');
        const link = document.createElement('a');
        link.download = `qr-code-${userId}.png`;
        link.href = qrImage.src;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});