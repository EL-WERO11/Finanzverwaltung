<?php
session_start();

define('ADMIN_USERNAME', 'admin'); // Benutzername für login*
define('ADMIN_PASSWORD', 'admin'); // Benutzername für passwort*

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Bitte alle Felder ausfüllen";
    } elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = "Ungültige Anmeldedaten";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Finanz Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --danger: #ef4444;
            --light: #f9fafb;
            --dark: #111827;
            --background: #f3f4f6;
            --text: #111827;
            --card-bg: rgba(255, 255, 255, 0.95);
            --border-color: #d1d5db;
            --gray-light: #e5e7eb;
            --alert-bg: #fee2e2;
            --alert-color: #b91c1c;
            --footer-text: #6b7280;
            --input-padding: 0.6rem 1rem;
            --input-font-size: 0.95rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: var(--background);
            color: var(--text);
            transition: all 0.3s ease;
        }

        .login-container {
            width: 90%;
            max-width: 380px;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            margin: 1rem;
        }

        .login-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.8rem;
        }

        .logo h1 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: var(--text);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: var(--input-padding);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: var(--input-font-size);
            transition: all 0.2s ease;
            background: var(--card-bg);
            color: var(--text);
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        .btn {
            width: 100%;
            padding: 0.7rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(79, 70, 229, 0.3);
        }

        .alert {
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
            background-color: var(--alert-bg);
            color: var(--alert-color);
            border-left: 4px solid var(--danger);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.2rem;
            color: var(--footer-text);
            font-size: 0.8rem;
        }

        .dark-toggle-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .dark-toggle-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .dark-mode {
            --background: #1e1e2f;
            --text: #f1f1f1;
            --card-bg: #2c2f48;
            --border-color: #3b3f5a;
            --gray-light: #3b3f5a;
            --primary: #7aa2f7;
            --primary-dark: #6a90f0;
            --primary-light: #a3c0ff;
            --alert-bg: #3a1e1e;
            --footer-text: #a1a1aa;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                margin: 0.8rem;
                width: 85%;
            }
            
            .form-control {
                padding: 0.55rem 0.9rem;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 0.65rem;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 768px) {
            .login-container {
                max-width: 420px;
                padding: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <button id="darkToggle" class="dark-toggle-btn">🌞 Hell</button>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-wallet"></i>
            <h1>Finanz Dashboard</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Anmelden
            </button>
        </form>
        
        <p class="footer-text">© <?= date('Y') ?> Finanzverwaltung</p>
    </div>
</body>
<script>

    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const btn = document.getElementById('darkToggle');

        body.classList.add('dark-mode');
        btn.textContent = "🌞 Hell";

        btn.addEventListener('click', () => {
            const isDark = body.classList.toggle('dark-mode');
            btn.textContent = isDark ? "🌞 Hell" : "🌙 Dunkel";
            
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        });

        if (localStorage.getItem('darkMode') === 'disabled') {
            body.classList.remove('dark-mode');
            btn.textContent = "🌙 Dunkel";
        }
    });
	document.addEventListener("keydown", function (e) {
    if (e.key === "F12" || (e.ctrlKey && e.shiftKey && e.key === "I")) {
        e.preventDefault();
        alert("Entwicklertools sind deaktiviert.");
    }
	});
	document.addEventListener("contextmenu", function (e) {
    e.preventDefault();
	});
</script>
</html>