let html5QrCode = null;
let currentCameraId = null;

// Navigation entre sections
function showSection(section) {
    // Masquer toutes les sections
    document.getElementById('generateSection').classList.add('hidden');
    document.getElementById('scanSection').classList.add('hidden');
    document.getElementById('listSection').classList.add('hidden');
    
    // Réinitialiser les boutons
    document.getElementById('generateBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-gray-600 hover:bg-gray-50';
    document.getElementById('scanBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-gray-600 hover:bg-gray-50';
    document.getElementById('listBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-gray-600 hover:bg-gray-50';
    
    // Afficher la section sélectionnée
    if (section === 'generate') {
        document.getElementById('generateSection').classList.remove('hidden');
        document.getElementById('generateBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-indigo-600 bg-indigo-50 border-2 border-indigo-200';
        stopCamera();
    } else if (section === 'scan') {
        document.getElementById('scanSection').classList.remove('hidden');
        document.getElementById('scanBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-indigo-600 bg-indigo-50 border-2 border-indigo-200';
        document.getElementById('manualInput').classList.add('hidden');
    } else if (section === 'list') {
        document.getElementById('listSection').classList.remove('hidden');
        document.getElementById('listBtn').className = 'px-6 py-3 rounded-md transition-colors duration-200 text-indigo-600 bg-indigo-50 border-2 border-indigo-200';
        loadData('tickets');
        stopCamera();
    }
}

// Gestion de la caméra
function startCamera(facingMode) {
    stopCamera();
    
    const readerElement = document.getElementById('reader');
    const placeholder = document.getElementById('cameraPlaceholder');
    readerElement.innerHTML = '';
    placeholder.textContent = 'Initialisation de la caméra...';
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        facingMode: facingMode
    };
    
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ deviceId: { exact: currentCameraId } }, config, onScanSuccess, onScanError)
        .then(() => {
            placeholder.remove();
        })
        .catch(err => {
            console.error("Erreur caméra:", err);
            placeholder.textContent = `Erreur: ${err.message}`;
        });
}

function stopCamera() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop()
            .then(() => console.log("Caméra arrêtée"))
            .catch(err => console.error("Erreur arrêt caméra", err));
    }
}

// Callback scan QR
function onScanSuccess(decodedText) {
    document.getElementById('qrCodeInput').value = decodedText;
    document.getElementById('manualInput').classList.remove('hidden');
    verifyQRCode();
}

function onScanError(errorMessage) {
    // Erreurs ignorées pendant le scan
}

// Gestion fichier image
document.getElementById('qrImageFile').addEventListener('change', function(e) {
    if (e.target.files.length === 0) return;
    
    const file = e.target.files[0];
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }
    
    html5QrCode.scanFile(file, true)
        .then(decodedText => {
            document.getElementById('qrCodeInput').value = decodedText;
            document.getElementById('manualInput').classList.remove('hidden');
        })
        .catch(err => {
            console.error("Erreur scan fichier:", err);
            alert("Erreur lors de la lecture du QR code: " + err);
        });
});

// Génération de QR code
document.getElementById('generateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('user_id', document.getElementById('user_id').value);
    formData.append('user_name', document.getElementById('user_name').value);
    formData.append('user_info', document.getElementById('user_info').value);
    formData.append('user_uns', document.getElementById('user_uns').value);
    formData.append('phone_number', document.getElementById('phone_number').value);
    
    try {
        const response = await fetch('php/generate_qr.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        const resultDiv = document.getElementById('generateResult');
        if (result.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-3xl mr-2"></i>
                        <h3 class="text-green-800 font-medium text-xl">Ticket généré avec succès!</h3>
                    </div>
                    <p class="text-green-700 mt-2">Envoyé à: ${result.phone_number}</p>
                </div>
                <div id="ticketPreview" class="w-full max-w-md mx-auto">
                    <img src="${result.qr_url}" alt="QR Code" class="w-48 h-48 mx-auto mb-4">
                    <div class="text-center">
                        <p class="text-blue-700 font-bold text-xl">${result.user_name}</p>
                        <p class="text-gray-700">${result.phone_number}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="${result.whatsapp_url}" target="_blank" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        <i class="fab fa-whatsapp text-2xl mr-2"></i>
                        Ouvrir dans WhatsApp
                    </a>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        <h3 class="text-red-800 font-medium">Erreur</h3>
                    </div>
                    <p class="text-red-700 mt-2">${result.message}</p>
                </div>
            `;
        }
        resultDiv.classList.remove('hidden');
    } catch (error) {
        console.error('Erreur:', error);
        alert("Une erreur est survenue lors de la génération du ticket");
    }
});

// Vérification QR code
async function verifyQRCode() {
    const qrCode = document.getElementById('qrCodeInput').value.trim();
    if (!qrCode) {
        alert('Veuillez scanner ou entrer un code QR');
        return;
    }
    
    try {
        const response = await fetch('php/verify_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_code: qrCode })
        });
        
        const result = await response.json();
        const resultDiv = document.getElementById('scanResult');
        
        if (result.success) {
            if (result.already_used) {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-600 mr-2"></i>
                            <h3 class="text-red-800 font-medium">Ticket déjà utilisé!</h3>
                        </div>
                        <p class="text-red-700 mt-2">${result.message}</p>
                        <p class="text-red-700">Participant: ${result.user_name}</p>
                        <p class="text-red-700">Date: ${result.used_at}</p>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <h3 class="text-green-800 font-medium">Ticket validé!</h3>
                        </div>
                        <p class="text-green-700 mt-2">${result.message}</p>
                        <p class="text-green-700">Participant: ${result.user_name}</p>
                        <p class="text-green-700">Filière: ${result.user_info}</p>
                    </div>
                `;
            }
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        <h3 class="text-red-800 font-medium">Ticket invalide</h3>
                    </div>
                    <p class="text-red-700 mt-2">${result.message}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert("Erreur lors de la vérification du ticket");
    }
}

// Chargement des données
async function loadData(type) {
    try {
        const response = await fetch(`php/list_data.php?type=${type}`);
        const result = await response.json();
        const dataList = document.getElementById('dataList');
        
        if (result.success && result.data.length > 0) {
            let tableHTML = `
                <table class="qr-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Filière</th>
                            <th>Entité</th>
                            ${type === 'tickets' ? '<th>Status</th><th>Créé le</th>' : '<th>Scanné le</th>'}
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            result.data.forEach(item => {
                tableHTML += `
                    <tr>
                        <td>${item.user_name}</td>
                        <td>${item.phone_number}</td>
                        <td>${item.user_info}</td>
                        <td>${item.user_uns}</td>
                `;
                
                if (type === 'tickets') {
                    const status = item.day_1 || item.day_2 ? 
                        `<span class="used-badge">Utilisé</span>` : 
                        `<span class="valid-badge">Valide</span>`;
                    tableHTML += `
                        <td>${status}</td>
                        <td>${item.created_at}</td>
                    `;
                } else {
                    tableHTML += `<td>${item.scanned_at}</td>`;
                }
                
                tableHTML += `</tr>`;
            });
            
            tableHTML += `</tbody></table>`;
            dataList.innerHTML = tableHTML;
        } else {
            dataList.innerHTML = `
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>Aucune donnée disponible</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert("Erreur lors du chargement des données");
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    showSection('generate');
});