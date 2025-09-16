<?php
// Conexão
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

$user_id = $_GET['id'] ?? 1; // No real use SESSION
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $bio = $_POST['bio'];

    // Upload de foto
    $foto = $user['foto_perfil']; // pega a foto antiga

    if (!empty($_FILES['foto_perfil']['tmp_name'])) {
        $imageData = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        $foto = 'data:' . $_FILES['foto_perfil']['type'] . ';base64,' . base64_encode($imageData);
    }

    $sql = "UPDATE users SET nome = :nome, bio = :bio, foto_perfil = :foto WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'bio' => $bio,
        'foto' => $foto,
        'id' => $user_id
    ]);

    // Redireciona de volta ao perfil
    header("Location: perfil.php?id=$user_id");
    exit();
}
?><!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - <?php echo htmlspecialchars($user['nome']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, rgba(16, 63, 150, 0.86) 0%, #3f42e2ba 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .form-card {
            transition: all 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-2px);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: scale(1.02);
        }

        .preview-image {
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }

        .drag-area {
            transition: all 0.3s ease;
        }

        .drag-area.dragover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }

        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen">
    <!-- Header -->
    <nav class="glass-effect border-b border-white/20 p-4">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-code text-white text-2xl"></i>
                <h1 class="text-white text-xl font-bold">ConectaTech</h1>
            </div>
            <a href="perfil.php?id=<?php echo $user_id; ?>"
                class="text-white hover:text-blue-200 transition-colors flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar ao Perfil</span>
            </a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto p-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h2 class="text-4xl font-bold text-white mb-2">Editar Perfil</h2>
            <p class="text-white/80">Atualize suas informações pessoais</p>
        </div>

        <!-- Edit Form -->
        <div class="form-card bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Form Header -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-800 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Informações do Perfil</h3>
                        <p class="text-white/80">Mantenha seus dados sempre atualizados</p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <form method="POST" enctype="multipart/form-data" class="p-8" id="editForm" action="">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column - Photo -->
                    <div class="space-y-6">
                        <div class="text-center">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Foto de Perfil</h4>

                            <!-- Current Photo Preview -->
                            <div class="mb-6">
                                <img id="photoPreview"
                                    src="<?php echo htmlspecialchars($user['foto_perfil'] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face'); ?>"
                                    alt="Foto atual"
                                    class="preview-image w-40 h-40 object-cover rounded-full border-4 border-blue-500 shadow-lg mx-auto"
                                    onerror="this.src='https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face';">
                            </div>

                            <!-- File Upload Area -->
                            <div class="drag-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
                                onclick="document.getElementById('foto_perfil').click()">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600 mb-2">Clique para selecionar uma nova foto</p>
                                <p class="text-sm text-gray-400">ou arraste e solte aqui</p>
                                <input type="file" name="foto_perfil" id="foto_perfil" class="hidden" accept="image/*"
                                    onchange="previewImage(this)">
                            </div>

                            <!-- Photo Actions -->
                            <div class="flex justify-center space-x-4 mt-4">
                                <button type="button" onclick="removePhoto()"
                                    class="text-red-500 hover:text-red-700 transition-colors flex items-center space-x-2">
                                    <i class="fas fa-trash"></i>
                                    <span>Remover Foto</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Form Fields -->
                    <div class="space-y-6">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4">Informações Pessoais</h4>

                        <!-- Nome -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-user text-blue-500 mr-2"></i>Nome Completo
                            </label>
                            <input type="text" name="nome" id="nome"
                                class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value="<?php echo htmlspecialchars($user['nome']); ?>" required
                                placeholder="Digite seu nome completo">
                            <div class="text-xs text-gray-500 flex items-center space-x-1">
                                <i class="fas fa-info-circle"></i>
                                <span>Este nome será exibido em seu perfil público</span>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-quote-left text-purple-500 mr-2"></i>Biografia
                            </label>
                            <textarea name="bio" id="bio" rows="5"
                                class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                placeholder="Conte um pouco sobre você, suas habilidades e interesses..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <div class="flex justify-between items-center text-xs text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Descreva suas habilidades e experiências</span>
                                </div>
                                <span id="charCount">0/500</span>
                            </div>
                        </div>

                        <!-- Email (readonly) -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-envelope text-green-500 mr-2"></i>Email
                            </label>
                            <input type="email"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                                value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <div class="text-xs text-gray-500 flex items-center space-x-1">
                                <i class="fas fa-lock"></i>
                                <span>O email não pode ser alterado</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6 mt-8 pt-6 border-t border-gray-200">
                    <button type="submit"
                        class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-save"></i>
                        <span>Salvar Alterações</span>
                    </button>
                    <a href="perfil.php?id=<?php echo $user_id; ?>"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2 no-underline">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tips Card -->
        <div class="mt-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h4 class="text-white font-semibold mb-4 flex items-center">
                <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>
                Dicas para um perfil atrativo
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-white/80 text-sm">
                <div class="flex items-start space-x-2">
                    <i class="fas fa-camera text-blue-400 mt-1"></i>
                    <span>Use uma foto profissional e atual</span>
                </div>
                <div class="flex items-start space-x-2">
                    <i class="fas fa-pen text-purple-400 mt-1"></i>
                    <span>Escreva uma bio clara e objetiva</span>
                </div>
                <div class="flex items-start space-x-2">
                    <i class="fas fa-star text-yellow-400 mt-1"></i>
                    <span>Destaque suas principais habilidades</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter for bio
        const bioTextarea = document.getElementById('bio');
        const charCount = document.getElementById('charCount');

        function updateCharCount() {
            const count = bioTextarea.value.length;
            charCount.textContent = `${count}/500`;

            if (count > 450) {
                charCount.classList.add('text-red-500');
                charCount.classList.remove('text-gray-500');
            } else {
                charCount.classList.add('text-gray-500');
                charCount.classList.remove('text-red-500');
            }
        }

        bioTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count

        // Image preview functionality
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('photoPreview').src = e.target.result;
                    showNotification('Foto selecionada com sucesso!', 'success');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remove photo functionality
        function removePhoto() {
            document.getElementById('photoPreview').src = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face';
            document.getElementById('foto_perfil').value = '';
            showNotification('Foto removida', 'info');
        }

        // Drag and drop functionality
        const dragArea = document.querySelector('.drag-area');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dragArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dragArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dragArea.classList.add('dragover');
        }

        function unhighlight(e) {
            dragArea.classList.remove('dragover');
        }

        dragArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                document.getElementById('foto_perfil').files = files;
                previewImage(document.getElementById('foto_perfil'));
            }
        }

        // Form validation and submission
        document.getElementById('editForm').addEventListener('submit', function (e) {
            const nome = document.getElementById('nome').value.trim();

            if (nome.length < 2) {
                e.preventDefault();
                showNotification('O nome deve ter pelo menos 2 caracteres', 'error');
                return;
            }

            if (bioTextarea.value.length > 500) {
                e.preventDefault();
                showNotification('A biografia não pode exceder 500 caracteres', 'error');
                return;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
            submitBtn.disabled = true;

            // Add success animation to form
            setTimeout(() => {
                document.querySelector('.form-card').classList.add('success-animation');
            }, 100);
        });

        // Notification system
        function showNotification(message, type) {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Real-time form validation
        document.getElementById('nome').addEventListener('input', function () {
            const value = this.value.trim();
            if (value.length < 2 && value.length > 0) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Add subtle animations to form elements
            const formElements = document.querySelectorAll('input, textarea');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
    <script>(function () { function c() { var b = a.contentDocument || a.contentWindow.document; if (b) { var d = b.createElement('script'); d.innerHTML = "window.__CF$cv$params={r:'980149a0d302f19d',t:'MTc1ODAzNTM5NC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);"; b.getElementsByTagName('head')[0].appendChild(d) } } if (document.body) { var a = document.createElement('iframe'); a.height = 1; a.width = 1; a.style.position = 'absolute'; a.style.top = 0; a.style.left = 0; a.style.border = 'none'; a.style.visibility = 'hidden'; document.body.appendChild(a); if ('loading' !== document.readyState) c(); else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c); else { var e = document.onreadystatechange || function () { }; document.onreadystatechange = function (b) { e(b); 'loading' !== document.readyState && (document.onreadystatechange = e, c()) } } } })();</script>
</body>

</html>