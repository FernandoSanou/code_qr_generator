<?php 
// ÉTAPE 1: SÉCURITÉ - Vérifie si l'administrateur est connecté avant d'afficher la page
require_once 'php/check_session.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Tickets QR - Joré-Culep 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .ticket {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
            border: 2px solid #3b82f6;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        }
        
        .scanner-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            pointer-events: none;
        }
        
        .scanner-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: 70%;
            border: 3px solid #3b82f6;
            box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
            pointer-events: none;
        }
        
        .active-tab {
            background-color: #3b82f6;
            color: white !important;
        }
        
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .attendance-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .day1-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        
        .day2-badge {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .ticket-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
            position: relative;
            overflow: hidden;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .ticket-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #f6a13b, #f6a95c, #ec4848);
        }
        
        .btn-whatsapp {
            background-color: #25D366;
        }
        
        .btn-whatsapp:hover {
            background-color: #128C7E;
        }
        
        .btn-download {
            background-color: #3b82f6;
        }
        
        .btn-download:hover {
            background-color: #2563eb;
        }
        
        .bg-jore-culep {
            background-image: url('assets/bg-code-qr-jore-culep.jpg');
        }
        
        .ticket-image {
            max-width: 100%;
            border: 4px solid #3b82f6;
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <div class="absolute top-4 right-4">
        <a href="php/logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-all duration-300">
            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
        </a>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-blue-600 p-3 rounded-full shadow-lg">
                        <i class="fas fa-qrcode text-white text-3xl"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">
                    Système de Tickets QR
                </h1>
                <p class="text-gray-600">Joré-Culep 2025 - 18-19 Juillet à ENSTP ABOMEY</p>
            </div>

            <div class="flex justify-center mb-8">
                <div class="bg-white rounded-lg shadow-md p-2 flex flex-wrap justify-center gap-2">
                    <button onclick="showSection('generate')" 
                            class="px-6 py-3 rounded-md transition-colors duration-200 flex items-center active-tab" 
                            id="generateBtn">
                        <i class="fas fa-plus mr-2"></i>Générer Ticket
                    </button>
                    <button onclick="showSection('scan')" 
                            class="px-6 py-3 rounded-md transition-colors duration-200 flex items-center text-gray-600 hover:bg-gray-50" 
                            id="scanBtn">
                        <i class="fas fa-camera mr-2"></i>Scanner Ticket
                    </button>
                    <button onclick="showSection('list')" 
                            class="px-6 py-3 rounded-md transition-colors duration-200 flex items-center text-gray-600 hover:bg-gray-50" 
                            id="listBtn">
                        <i class="fas fa-list mr-2"></i>Liste des Données
                    </button>
                </div>
            </div>

            <div id="generateSection" class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                    Générer un Ticket QR
                </h2>
                
                <form id="generateForm" class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-1 text-blue-500"></i>Identifiant Utilisateur
                            </label>
                            <input type="text" id="userId" name="userId" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="ID unique de l'utilisateur">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-blue-500"></i>Nom Complet
                            </label>
                            <input type="text" id="userName" name="userName" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Entrez le nom complet">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-graduation-cap mr-1 text-blue-500"></i>Filière & Année
                            </label>
                            <input type="text" id="userInfo" name="userInfo" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ex: Informatique L3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-university mr-1 text-blue-500"></i>Entité Universitaire
                            </label>
                            <input type="text" id="userUns" name="userUns" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ex: UAC, UNSTIM">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-1 text-blue-500"></i>Numéro WhatsApp
                            </label>
                            <input type="tel" id="phoneNumber" name="phoneNumber" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="+229 XX XX XX XX">
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium flex items-center justify-center">
                        <i class="fas fa-qrcode mr-2"></i>Générer le Ticket
                    </button>
                </form>
                
                <div id="generateResult" class="mt-8 hidden">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800 text-center">Aperçu du Ticket à Générer</h3>

                    <div id="ticketToSave" 
     class="p-4 max-w-md mx-auto border-2 border-dashed border-gray-300 bg-white" 
     >
    <div class="ticketToSave ticket-card p-4 relative bg-white rounded" style="background-image: url('assets/bg-code-qr-jore-culep.jpg'); background-size: cover; background-position: center;">
        <div class="text-center mb-2">
            <h3 class="text-xl font-bold text-orange-500 bg-clip-text text-transparent">
                Joré-Culep 2025
            </h3>
            <p class="text-sm text-gray-600">18-19 Juillet @ ENSTP ABOMEY</p>
        </div>
        <div class="flex flex-row items-center justify-between mt-4">
            <div class="text-left text-sm space-y-1">
                <p><strong>Nom:</strong> <span id="t_name"></span></p>
                <p><strong>Filière:</strong> <span id="t_info"></span></p>
                <p><strong>Entité:</strong> <span id="t_uns"></span></p>
                <p><strong>Tél:</strong> <span id="t_phone"></span></p>
                <p><strong>ID Ticket:</strong> <span id="t_id"></span></p>
            </div>
            <div id="t_qr" class="p-1 border-2 border-orange-500 rounded-lg bg-white">
                <!-- QR Code ici -->
            </div>
        </div>
        <p class="text-xs text-center mt-4 text-gray-500">Ce ticket est personnel et non cessible.</p>
    </div>
</div>

                    <div id="finalTicketDisplay" class="mt-6 text-center"></div>
                    
                    <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
                        <button id="whatsappBtn" 
                                class="btn-whatsapp text-white py-3 px-6 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center">
                            <i class="fab fa-whatsapp mr-2 text-xl"></i>Envoyer via WhatsApp
                        </button>
                        
                        <button id="downloadBtn" 
                                class="btn-download text-white py-3 px-6 rounded-lg transition-colors duration-200 font-medium flex items-center justify-center">
                            <i class="fas fa-download mr-2"></i>Télécharger le Ticket
                        </button>
                    </div>
                </div>
            </div>

            <div id="scanSection" class="bg-white rounded-lg shadow-lg p-6 mb-6 hidden">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-camera text-blue-600 mr-2"></i>
                    Scanner un Ticket QR
                </h2>
                
                <div class="space-y-6">
                    <div class="scanner-container bg-gray-800 mx-auto max-w-2xl">
                        <video id="scannerVideo" class="w-full h-auto" autoplay playsinline></video>
                        <div class="scanner-overlay"></div>
                        <div class="scanner-frame"></div>
                    </div>
                    
                    <div class="text-center">
                        <button onclick="switchCamera()" id="switchCameraBtn" class="mb-4 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Changer de caméra
                        </button>
                        
                        <p class="text-gray-600 mb-4">Ou</p>
                        
                        <div>
                            <label for="qrImageInput" class="w-full bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg py-12 text-gray-500 hover:bg-gray-50 transition-colors cursor-pointer block">
                                <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i><br>
                                Cliquez pour importer une image
                            </label>
                            <input type="file" id="qrImageInput" accept="image/*" class="hidden">
                        </div>
                    </div>
                    
                    <div id="scanResult" class="mt-6"></div>
                </div>
            </div>

            <div id="listSection" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Liste des Données
                </h2>
                
                <div class="mb-6 flex flex-wrap gap-2">
                    <button onclick="loadData('tickets')" 
                            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        <i class="fas fa-ticket-alt mr-2"></i>Tous les Tickets
                    </button>
                    <button onclick="loadData('day1_attendance')" 
                            class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium">
                        <i class="fas fa-calendar-day mr-2"></i>Présences Jour 1
                    </button>
                    <button onclick="loadData('day2_attendance')" 
                            class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                        <i class="fas fa-calendar-check mr-2"></i>Présences Jour 2
                    </button>
                </div>
                
                <div class="table-container bg-gray-50 rounded-lg shadow-inner p-4">
                    <table id="dataTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr id="tableHeaders"></tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                    <div id="emptyTableMessage" class="text-center py-8 text-gray-500 hidden">
                        <i class="fas fa-inbox text-4xl mb-4"></i>
                        <p>Aucune donnée disponible pour cette vue.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentStream = null;
        let currentCamera = 'environment';
        let scanningActive = false;
        let currentDataView = 'tickets';
        let currentTicketId = null;
        let fullTicketRelativeUrl = null; // Stocker l'URL relative du ticket final

        // Fonction pour changer de section
        function showSection(section) {
            document.getElementById('generateSection').classList.add('hidden');
            document.getElementById('scanSection').classList.add('hidden');
            document.getElementById('listSection').classList.add('hidden');
            
            const buttons = ['generateBtn', 'scanBtn', 'listBtn'];
            buttons.forEach(btnId => {
                const btn = document.getElementById(btnId);
                btn.classList.remove('active-tab', 'text-white');
                btn.classList.add('text-gray-600', 'hover:bg-gray-50');
            });
            
            const activeBtn = document.getElementById(section + 'Btn');
            activeBtn.classList.add('active-tab', 'text-white');
            activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-50');

            document.getElementById(section + 'Section').classList.remove('hidden');

            if (section === 'scan') {
                startScanning();
            } else {
                stopScanning();
            }

            if (section === 'list') {
                loadData(currentDataView);
            }
        }

        // --- NOUVEAU FLUX DE GÉNÉRATION DE TICKET ---
        document.getElementById('generateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const generateBtn = e.target.querySelector('button[type="submit"]');
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';

            // Cacher les anciens résultats
            document.getElementById('generateResult').classList.add('hidden');
            document.getElementById('finalTicketDisplay').innerHTML = '';


            const formData = {
                userId: document.getElementById('userId').value,
                userName: document.getElementById('userName').value,
                userInfo: document.getElementById('userInfo').value,
                userUns: document.getElementById('userUns').value,
                phoneNumber: document.getElementById('phoneNumber').value
            };

            try {
                // Étape 1: Envoyer les données au serveur pour créer l'entrée DB et le QR code brut
                const response = await fetch('php/generate_qr.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(formData)
                });
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || 'La génération du QR code a échoué.');
                }
                
                currentTicketId = result.ticketId;

                // Étape 2: Remplir la div du ticket avec les infos reçues pour l'aperçu
                document.getElementById('t_name').textContent = result.userData.userName;
                document.getElementById('t_info').textContent = result.userData.userInfo;
                document.getElementById('t_uns').textContent = result.userData.userUns;
                document.getElementById('t_phone').textContent = result.userData.phoneNumber;
                document.getElementById('t_id').textContent = `JC25-${result.ticketId}`;
                document.getElementById('t_qr').innerHTML = `<img src="${result.qrCodeUrl}" alt="QR Code" class="w-28 h-28">`;
                
                document.getElementById('generateResult').classList.remove('hidden');
                
                // Étape 3: Utiliser html2canvas pour "photographier" la div du ticket
                const ticketElement = document.getElementById('ticketToSave');
                const canvas = await html2canvas(ticketElement, { scale: 2 }); // Augmenter l'échelle pour une meilleure qualité
                const imageData = canvas.toDataURL('image/png');

                // Étape 4: Envoyer cette image au serveur pour la sauvegarder
                const saveResponse = await fetch('php/save_ticket.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        ticketId: currentTicketId,
                        imageData: imageData
                    })
                });
                const saveResult = await saveResponse.json();

                if (!saveResult.success) {
                    throw new Error(saveResult.error || 'La sauvegarde de l\'image a échoué.');
                }

                fullTicketRelativeUrl = saveResult.url; // Stocker l'URL relative finale

                // Afficher l'image finale sauvegardée pour confirmation
                document.getElementById('finalTicketDisplay').innerHTML = `
                    <p class="text-center text-green-700 font-semibold mb-2">Ticket sauvegardé avec succès !</p>
                    <img src="${fullTicketRelativeUrl}" alt="Ticket Final" class="mx-auto border-4 rounded-lg shadow-lg ticket-image">
                `;

                // Activer les boutons d'action
                document.getElementById('whatsappBtn').disabled = false;
                document.getElementById('downloadBtn').disabled = false;
                
            } catch (error) {
                console.error('Erreur dans le processus de génération:', error);
                alert('Une erreur est survenue: ' + error.message);
            } finally {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-qrcode mr-2"></i>Générer le Ticket';
            }
        });

        // --- GESTION WHATSAPP ET TÉLÉCHARGEMENT ---
        document.getElementById('whatsappBtn').addEventListener('click', function() {
            if (!fullTicketRelativeUrl) {
                alert("L'URL du ticket n'est pas encore prête.");
                return;
            }
            
            const userName = document.getElementById('userName').value;
            const phoneNumber = document.getElementById('phoneNumber').value;
            const fullUrl = window.location.origin + '/' + fullTicketRelativeUrl;

            const message = encodeURIComponent(
                `Bonjour ${userName},\n\nVoici votre ticket de participation pour la Joré-Culep 2025.\n` +
                `Conservez-le précieusement.\n\n` +
                `Vous pouvez le voir et le télécharger ici : ${fullUrl}\n\n` +
                `NB: Ce code QR est aussi important qu'un ticket physique.`
            );
            
            const whatsappLink = `https://wa.me/${phoneNumber}?text=${message}`;
            window.open(whatsappLink, '_blank');
        });

        document.getElementById('downloadBtn').addEventListener('click', function() {
            if (!fullTicketRelativeUrl) {
                alert("L'URL du ticket n'est pas encore prête.");
                return;
            }

            const link = document.createElement('a');
            link.href = fullTicketRelativeUrl;
            link.download = `ticket_joreculep_${currentTicketId}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
        
        // --- LOGIQUE DU SCANNER (inchangée) ---
        async function startScanning() {
            if (scanningActive) return;
            scanningActive = true;
            document.getElementById('scanResult').innerHTML = ''; // Nettoyer les anciens résultats
            
            const video = document.getElementById('scannerVideo');
            
            try {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
                
                currentStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: currentCamera } 
                });
                
                video.srcObject = currentStream;
                await video.play();
                
                requestAnimationFrame(tick);
            } catch (error) {
                console.error("Erreur d'accès à la caméra:", error);
                document.getElementById('scanResult').innerHTML = `<div class="bg-red-100 text-red-700 p-4 rounded">Erreur d'accès à la caméra. Veuillez autoriser l'accès dans les paramètres de votre navigateur.</div>`;
                scanningActive = false;
            }
        }

        function stopScanning() {
            scanningActive = false;
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
        }

        function switchCamera() {
            currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
            stopScanning();
            setTimeout(startScanning, 100); // petit délai pour assurer la libération de la caméra
        }

        function tick() {
            if (!scanningActive) return;
            
            const video = document.getElementById('scannerVideo');
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                const canvasElement = document.createElement('canvas');
                const canvas = canvasElement.getContext('2d');
                canvasElement.height = video.videoHeight;
                canvasElement.width = video.videoWidth;
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert'
                });

                if (code) {
                    stopScanning();
                    verifyQRCode(code.data);
                }
            }
            requestAnimationFrame(tick);
        }

        document.getElementById('qrImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img, 0, 0, canvas.width, canvas.height);
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert'
                    });
                    
                    if (code) {
                        verifyQRCode(code.data);
                    } else {
                        document.getElementById('scanResult').innerHTML = `<div class="bg-yellow-100 text-yellow-800 p-4 rounded">Aucun QR code valide détecté dans l'image.</div>`;
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        async function verifyQRCode(qrData) {
            document.getElementById('scanResult').innerHTML = `<div class="bg-blue-100 text-blue-800 p-4 rounded">Vérification en cours...</div>`;
            try {
                const response = await fetch('php/verify_qr.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ qrData: qrData })
                });
                const result = await response.json();
                
                const resultDiv = document.getElementById('scanResult');
                let resultClass, iconClass, title;

                if (result.success) {
                    if (result.already_used) {
                        resultClass = 'bg-yellow-100 border-yellow-500 text-yellow-800';
                        iconClass = 'fa-exclamation-triangle';
                        title = 'Ticket déjà utilisé !';
                    } else {
                        resultClass = 'bg-green-100 border-green-500 text-green-800';
                        iconClass = 'fa-check-circle';
                        title = 'Ticket Valide !';
                    }
                } else {
                    resultClass = 'bg-red-100 border-red-500 text-red-800';
                    iconClass = 'fa-times-circle';
                    title = 'Ticket Invalide';
                }

                resultDiv.innerHTML = `
                    <div class="${resultClass} border-l-4 p-4" role="alert">
                        <p class="font-bold flex items-center"><i class="fas ${iconClass} mr-2"></i>${title}</p>
                        <p>${result.message}</p>
                        ${result.user_name ? `<p class="mt-2">Participant: <strong>${result.user_name}</strong></p>` : ''}
                    </div>
                `;
                
                // Relancer le scanner après un délai pour permettre une nouvelle lecture
                setTimeout(startScanning, 5000);
            } catch (error) {
                console.error('Erreur de vérification:', error);
                document.getElementById('scanResult').innerHTML = `<div class="bg-red-100 text-red-700 p-4 rounded">Erreur de connexion au serveur de vérification.</div>`;
            }
        }

        // --- GESTION DE LA LISTE DE DONNÉES (inchangée) ---
        async function loadData(dataType) {
            currentDataView = dataType;
            try {
                const response = await fetch('php/list_data.php');
                const data = await response.json();
                
                const tableHeaders = document.getElementById('tableHeaders');
                const tableBody = document.getElementById('tableBody');
                const emptyMessage = document.getElementById('emptyTableMessage');
                
                tableHeaders.innerHTML = '';
                tableBody.innerHTML = '';
                
                let dataSet, headers;
                
                switch(dataType) {
                    case 'tickets':
                        dataSet = data.tickets;
                        headers = ['ID', 'Nom', 'Filière', 'Entité', 'Téléphone', 'Jour 1', 'Jour 2', 'Créé le'];
                        break;
                    case 'day1_attendance':
                        dataSet = data.day1_attendance;
                        headers = ['ID', 'Nom', 'Filière', 'Entité', 'Téléphone', 'Scanné le'];
                        break;
                    case 'day2_attendance':
                        dataSet = data.day2_attendance;
                        headers = ['ID', 'Nom', 'Filière', 'Entité', 'Téléphone', 'Scanné le'];
                        break;
                }
                
                if (!dataSet || dataSet.length === 0) {
                    emptyMessage.classList.remove('hidden');
                    return;
                }
                
                emptyMessage.classList.add('hidden');
                
                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
                    th.textContent = header;
                    tableHeaders.appendChild(th);
                });
                
                dataSet.forEach(item => {
                    const row = document.createElement('tr');
                    
                    let columns;
                    if (dataType === 'tickets') {
                        columns = [
                            item.id, item.user_name, item.user_info, item.user_uns, item.phone_number,
                            item.day_1 ? `<span class="attendance-badge day1-badge">Validé</span>` : `<span class="text-gray-400">Non</span>`,
                            item.day_2 ? `<span class="attendance-badge day2-badge">Validé</span>` : `<span class="text-gray-400">Non</span>`,
                            new Date(item.created_at).toLocaleString('fr-FR')
                        ];
                    } else {
                        columns = [
                            item.ticket_id, item.user_name, item.user_info, item.user_uns, item.phone_number,
                            new Date(item.scanned_at).toLocaleString('fr-FR')
                        ];
                    }
                    
                    columns.forEach(col => {
                        const td = document.createElement('td');
                        td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-600';
                        td.innerHTML = col;
                        row.appendChild(td);
                    });
                    
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Erreur de chargement des données:', error);
                document.getElementById('emptyTableMessage').textContent = "Erreur lors du chargement des données.";
                document.getElementById('emptyTableMessage').classList.remove('hidden');
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('whatsappBtn').disabled = true;
            document.getElementById('downloadBtn').disabled = true;
            stopScanning();
            
            // Permet de cliquer sur le label pour uploader un fichier
            const fileLabel = document.querySelector('label[for="qrImageInput"]');
            if(fileLabel) {
                fileLabel.addEventListener('click', () => document.getElementById('qrImageInput').click());
            }
        });
    </script>
</body>
</html>