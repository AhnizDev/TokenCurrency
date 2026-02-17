<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$error = "";

// Redirection si déjà connecté
if (isset($_SESSION['dahniz_user'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['action'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    if ($_POST['action'] == 'signup') {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        // Ton format de PID personnalisé
        $pid = "12sx5-" . bin2hex(random_bytes(4)) . "-v3"; 
        
        $sql = "INSERT INTO users_dahniztoken (username, password, principal_id, balance, last_activity_reward) VALUES ('$user', '$hashed_pass', '$pid', 0, NOW())";
        if (mysqli_query($conn, $sql)) { 
            $error = "<span style='color:#27ae60;'>Account created! Please login.</span>"; 
        } else { 
            $error = "Username already exists."; 
        }
    } 
    
    if ($_POST['action'] == 'login') {
        $res = mysqli_query($conn, "SELECT * FROM users_dahniztoken WHERE username = '$user'");
        $data = mysqli_fetch_assoc($res);
        if ($data && password_verify($pass, $data['password'])) {
            $_SESSION['dahniz_user'] = $data['id'];
            header("Location: index.php");
            exit();
        } else { 
            $error = "Invalid credentials."; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DahnizToken - Gateway</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #f1c40f;
            --success: #27ae60;
            --dark: #1a1a1a;
            --bg: #f4f7f6;
        }

        body { 
            margin: 0; 
            padding: 0;
            background: var(--bg); 
            font-family: 'Segoe UI', sans-serif; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh;
        }

        /* HEADER */
        .nav-header {
            background: var(--dark);
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .nav-logo { color: white; text-decoration: none; font-weight: bold; font-size: 1.1rem; }
        .nav-links a { color: #ccc; text-decoration: none; font-size: 0.8rem; }

        /* CONTAINER CENTRAL */
        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }

        .auth-header {
            background: var(--primary);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .auth-header i.main-icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .auth-header h2 { margin: 0; font-size: 1.4rem; letter-spacing: 1px; }

        /* FORMULAIRE */
        .auth-body { padding: 30px 25px; }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }
        .input-group i.left-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            cursor: pointer;
        }

        .auth-input {
            width: 100%;
            padding: 14px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background: #fafafa;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .auth-input:focus { border-color: var(--success); background: white; }

        /* BOUTONS */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .btn-login { background: var(--success); color: white; }
        .btn-signup { background: transparent; color: var(--primary); border: 2px solid var(--primary); }

        /* FOOTER ARRANGE */
        footer {
            background: white;
            border-top: 1px solid #eee;
            padding: 25px 15px;
            text-align: center;
        }
        footer p {
            margin: 0;
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.5;
        }
        footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<nav class="nav-header">
    <a href="#" class="nav-logo">💎 DahnizToken</a>
    <div class="nav-links">
        <a href="https://khelaf-hanifi.com" target="_blank">Official Website</a>
    </div>
</nav>

<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-shield-halved main-icon"></i>
            <h2>SECURE ACCESS</h2>
            <p style="font-size: 0.75rem; opacity: 0.8; margin-top: 5px; text-transform: uppercase;">Enter the DahnizToken Ecosystem</p>
             <font style="font-size: 0.75rem; opacity: 0.8; margin-top: 2px;">Developed by Mr. <a style="color: white;" href="https://khelaf-hanifi.com/" target="_blank">Khelaf Hanifi</a></font>
        </div>
        
        <div class="auth-body">
            <?php if($error) echo "<p style='color:#e74c3c; text-align:center; font-weight:bold; margin-bottom:15px; font-size:0.9rem;'>$error</p>"; ?>
            
            <form method="POST">
                <div class="input-group">
                    <i class="fas fa-user left-icon"></i>
                    <input type="text" name="username" class="auth-input" placeholder="Username" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" name="password" id="passwordField" class="auth-input" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" id="toggleIcon" onclick="togglePassword()"></i>
                </div>
                
                <button type="submit" name="action" value="login" class="btn btn-login">Login</button>
                <button type="submit" name="action" value="signup" class="btn btn-signup">Create Account</button>
            </form>
        </div>
    </div>
</div>

<footer>
    <p>
        Copyright © 2000-2026 ® <strong>AhnizTech</strong><br>
        Developed by Mr. <a href="https://khelaf-hanifi.com/" target="_blank">Hanifi Khelaf</a>
    </p>
</footer>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('toggleIcon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>
