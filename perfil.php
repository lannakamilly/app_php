<?php
// Conexão com banco de dados (ajuste conforme suas credenciais)
$host = "localhost";
$dbname = "conectatech";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Simulação de usuário logado (em um sistema real, use $_SESSION['user_id'])
$user_id = 1;

// Consulta ao banco
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou usuário
if (!$user) {
    die("Usuário não encontrado.");
}

// Se não tiver foto, define uma padrão
if (empty($user['foto_perfil'])) {
    $user['foto_perfil'] = "uploads/default.png";
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?php echo htmlspecialchars($user['nome']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #5565aeff 0%, #2f36b8ff 100%); }
        .glass-effect { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.1); }
        .profile-card { transition: all 0.3s ease; }
        .profile-card:hover { transform: translateY(-5px); }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: scale(1.05); }
        .edit-overlay { display: none; }
        .edit-overlay.active { display: flex; }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Header -->
    <nav class="glass-effect border-b border-white/20 p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-code text-white text-2xl"></i>
                <h1 class="text-white text-xl font-bold">ConectaTech</h1>
            </div>
            <!-- <div class="flex items-center space-x-4">
                <button class="text-white hover:text-blue-200 transition-colors">
                    <i class="fas fa-bell text-lg"></i>
                </button>
                <button class="text-white hover:text-blue-200 transition-colors">
                    <i class="fas fa-cog text-lg"></i>
                </button>
            </div> -->
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6">
        <!-- Profile Header -->
        <div class="profile-card bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                <!-- Profile Photo -->
                <div class="relative group">
                    <img id="profilePhoto" src="<?php echo htmlspecialchars($user['foto_perfil']); ?>" 
                         alt="Foto de perfil" 
                         class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-full border-4 border-blue-500 shadow-lg"
                         onerror="this.src='https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face';">
                    <button onclick="changePhoto()" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-camera text-white text-xl"></i>
                    </button>
                </div>

                <!-- Profile Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div>
                            <h2 id="userName" class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($user['nome']); ?></h2>
                            <p id="userBio" class="text-gray-600 mb-3"><?php echo htmlspecialchars($user['bio'] ?? "Nenhuma bio cadastrada."); ?></p>
                        </div>
                        <a href="editar_perfil.php?id=<?php echo $user['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center space-x-2 no-underline">
                            <i class="fas fa-edit"></i>
                            <span>Editar Perfil</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div class="flex items-center justify-center md:justify-start space-x-2">
                            <i class="fas fa-envelope text-blue-500"></i>
                            <span id="userEmail"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-2">
                            <i class="fas fa-calendar text-green-500"></i>
                            <span>Membro desde <span id="memberSince"><?php echo date("d/m/Y", strtotime($user['data_criacao'])); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-2">
                            <i class="fas fa-map-marker-alt text-red-500"></i>
                            <span id="userLocation"><?php echo htmlspecialchars($user['localizacao'] ?? 'Não informado'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="stat-card bg-white rounded-xl p-6 shadow-lg text-center">
                <div class="text-3xl font-bold text-blue-500 mb-2" id="projectsCount">12</div>
                <div class="text-gray-600">Projetos</div>
            </div>
            <div class="stat-card bg-white rounded-xl p-6 shadow-lg text-center">
                <div class="text-3xl font-bold text-green-500 mb-2" id="connectionsCount">248</div>
                <div class="text-gray-600">Conexões</div>
            </div>
            <div class="stat-card bg-white rounded-xl p-6 shadow-lg text-center">
                <div class="text-3xl font-bold text-purple-500 mb-2" id="skillsCount">15</div>
                <div class="text-gray-600">Habilidades</div>
            </div>
            <div class="stat-card bg-white rounded-xl p-6 shadow-lg text-center">
                <div class="text-3xl font-bold text-orange-500 mb-2" id="achievementsCount">8</div>
                <div class="text-gray-600">Conquistas</div>
            </div>
        </div>

        <!-- Content Tabs -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200">
                <button onclick="showTab('projects')" class="tab-btn flex-1 py-4 px-6 text-center font-medium text-blue-500 border-b-2 border-blue-500 bg-blue-50">
                    <i class="fas fa-folder-open mr-2"></i>Projetos
                </button>
                <button onclick="showTab('skills')" class="tab-btn flex-1 py-4 px-6 text-center font-medium text-gray-500 hover:text-blue-500 transition-colors">
                    <i class="fas fa-code mr-2"></i>Habilidades
                </button>
                <button onclick="showTab('activity')" class="tab-btn flex-1 py-4 px-6 text-center font-medium text-gray-500 hover:text-blue-500 transition-colors">
                    <i class="fas fa-chart-line mr-2"></i>Atividade
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Projects Tab -->
                <div id="projects-tab" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <h3 class="font-semibold text-gray-800 mb-2">E-commerce Platform</h3>
                            <p class="text-gray-600 text-sm mb-3">Plataforma completa de e-commerce com React e Node.js</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">React</span>
                                <span class="text-xs text-gray-500">2 dias atrás</span>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <h3 class="font-semibold text-gray-800 mb-2">Task Manager App</h3>
                            <p class="text-gray-600 text-sm mb-3">Aplicativo de gerenciamento de tarefas com Vue.js</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Vue.js</span>
                                <span class="text-xs text-gray-500">1 semana atrás</span>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <h3 class="font-semibold text-gray-800 mb-2">API REST</h3>
                            <p class="text-gray-600 text-sm mb-3">API robusta para sistema de autenticação</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Node.js</span>
                                <span class="text-xs text-gray-500">2 semanas atrás</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skills Tab -->
                <div id="skills-tab" class="tab-content hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fab fa-js-square text-4xl text-yellow-500 mb-2"></i>
                            <div class="font-medium">JavaScript</div>
                            <div class="text-sm text-gray-500">Avançado</div>
                        </div>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fab fa-react text-4xl text-blue-500 mb-2"></i>
                            <div class="font-medium">React</div>
                            <div class="text-sm text-gray-500">Avançado</div>
                        </div>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fab fa-node-js text-4xl text-green-500 mb-2"></i>
                            <div class="font-medium">Node.js</div>
                            <div class="text-sm text-gray-500">Intermediário</div>
                        </div>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-database text-4xl text-gray-600 mb-2"></i>
                            <div class="font-medium">MySQL</div>
                            <div class="text-sm text-gray-500">Intermediário</div>
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div id="activity-tab" class="tab-content hidden">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-lg">
                            <i class="fas fa-code text-blue-500"></i>
                            <div>
                                <div class="font-medium">Novo commit no projeto E-commerce</div>
                                <div class="text-sm text-gray-500">Há 2 horas</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 p-4 border-l-4 border-green-500 bg-green-50 rounded-r-lg">
                            <i class="fas fa-users text-green-500"></i>
                            <div>
                                <div class="font-medium">Nova conexão com Maria Santos</div>
                                <div class="text-sm text-gray-500">Há 5 horas</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 p-4 border-l-4 border-purple-500 bg-purple-50 rounded-r-lg">
                            <i class="fas fa-trophy text-purple-500"></i>
                            <div>
                                <div class="font-medium">Conquista desbloqueada: 10 projetos</div>
                                <div class="text-sm text-gray-500">Há 1 dia</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editOverlay" class="edit-overlay fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-6">Editar Perfil</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                    <input id="editName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="editBio" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Localização</label>
                    <input id="editLocation" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            <div class="flex space-x-4 mt-6">
                <button onclick="saveProfile()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors">
                    Salvar
                </button>
                <button onclick="toggleEditMode()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        // User data from PHP database
        let userData = {
            name: "<?php echo htmlspecialchars($user['nome']); ?>",
            bio: "<?php echo htmlspecialchars($user['bio'] ?? 'Nenhuma bio cadastrada.'); ?>",
            email: "<?php echo htmlspecialchars($user['email']); ?>",
            location: "<?php echo htmlspecialchars($user['localizacao'] ?? 'Não informado'); ?>",
            memberSince: "<?php echo date('d/m/Y', strtotime($user['data_criacao'])); ?>",
            photo: "<?php echo htmlspecialchars($user['foto_perfil']); ?>",
            id: <?php echo $user['id']; ?>
        };

        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active state from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('text-blue-500', 'border-b-2', 'border-blue-500', 'bg-blue-50');
                btn.classList.add('text-gray-500');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Add active state to clicked button
            event.target.classList.add('text-blue-500', 'border-b-2', 'border-blue-500', 'bg-blue-50');
            event.target.classList.remove('text-gray-500');
        }

        // Edit profile functionality
        function toggleEditMode() {
            const overlay = document.getElementById('editOverlay');
            overlay.classList.toggle('active');
            
            if (overlay.classList.contains('active')) {
                // Populate form with current data
                document.getElementById('editName').value = userData.name;
                document.getElementById('editBio').value = userData.bio;
                document.getElementById('editLocation').value = userData.location;
            }
        }

        function saveProfile() {
            // Get form values
            userData.name = document.getElementById('editName').value;
            userData.bio = document.getElementById('editBio').value;
            userData.location = document.getElementById('editLocation').value;
            
            // Update UI
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userBio').textContent = userData.bio;
            document.getElementById('userLocation').textContent = userData.location;
            
            // Close modal
            toggleEditMode();
            
            // Show success message
            showNotification('Perfil atualizado com sucesso!', 'success');
        }

        function changePhoto() {
            const photos = [
                "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face",
                "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop&crop=face",
                "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop&crop=face",
                "https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?w=300&h=300&fit=crop&crop=face"
            ];
            
            const currentPhoto = document.getElementById('profilePhoto').src;
            let newPhoto;
            
            do {
                newPhoto = photos[Math.floor(Math.random() * photos.length)];
            } while (newPhoto === currentPhoto);
            
            document.getElementById('profilePhoto').src = newPhoto;
            userData.photo = newPhoto;
            
            showNotification('Foto de perfil atualizada!', 'success');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Animate stats on load
        function animateStats() {
            const stats = [
                { id: 'projectsCount', target: 12 },
                { id: 'connectionsCount', target: 248 },
                { id: 'skillsCount', target: 15 },
                { id: 'achievementsCount', target: 8 }
            ];
            
            stats.forEach(stat => {
                let current = 0;
                const increment = stat.target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= stat.target) {
                        current = stat.target;
                        clearInterval(timer);
                    }
                    document.getElementById(stat.id).textContent = Math.floor(current);
                }, 30);
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            animateStats();
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'980134a10049d97a',t:'MTc1ODAzNDUzNC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>


