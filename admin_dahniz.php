<?php
// 1. CONNEXION ET SÉCURITÉ DE SESSION
include 'db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Vérification : L'utilisateur doit être connecté
if (!isset($_SESSION['dahniz_user'])) {
    header("Location: auth.php");
    exit();
}

// Vérification : Seul l'utilisateur 'hanifi' a accès
$uid = $_SESSION['dahniz_user'];
$admin_check = mysqli_query($conn, "SELECT username FROM users_dahniztoken WHERE id = $uid");
$admin_data = mysqli_fetch_assoc($admin_check);

if ($admin_data['username'] !== 'hanifi') {
    die("<h1 style='color:red; text-align:center;'>Accès refusé. Seul l'admin Hanifi peut voir cette page.</h1>");
}

// 2. LOGIQUE DES ACTIONS (BANNIR ET INJECTER)

// ACTION : BANNIR
if (isset($_GET['delete'])) {
    $id_to_del = intval($_GET['delete']);
    $check_u = mysqli_query($conn, "SELECT username FROM users_dahniztoken WHERE id = $id_to_del");
    $u_to_del = mysqli_fetch_assoc($check_u);
    
    if ($u_to_del['username'] !== 'hanifi') {
        mysqli_query($conn, "DELETE FROM users_dahniztoken WHERE id = $id_to_del");
        header("Location: admin_dahniz.php?msg=Utilisateur supprimé");
        exit();
    }
}

// ACTION : INJECTER (Mint)
if (isset($_POST['mint'])) {
    $target_pid = mysqli_real_escape_string($conn, $_POST['target_pid']);
    $amount = intval($_POST['amount']);
    if ($amount > 0) {
        mysqli_query($conn, "UPDATE users_dahniztoken SET balance = balance + $amount WHERE principal_id = '$target_pid'");
        // Insertion avec NOW() pour avoir l'année 2026
        mysqli_query($conn, "INSERT INTO transactions_dahniztoken (sender_pid, receiver_pid, amount, created_at) VALUES ('ADMIN_MINT', '$target_pid', $amount, NOW())");
        $msg_ok = "Injection de $amount DAHNIZ réussie !";
    }
}

// 3. RÉCUPÉRATION DES STATS
$total_res = mysqli_query($conn, "SELECT SUM(balance) AS total FROM users_dahniztoken");
$total_supply = mysqli_fetch_assoc($total_res)['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Console - DahnizToken</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="nav-header">
    <a href="index.php" class="nav-logo">💎 Admin Console</a>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>

<div class="container" style="width: 850px; max-width: 95%;">
    <div class="header-box" style="background: #2c3e50;">
        <h1>Gestion de l'Écosystème</h1>
        <h2 style="color:#f1c40f;">Masse monétaire : <?php echo number_format($total_supply); ?> DAHNIZ</h2>
        <?php if(isset($msg_ok)) echo "<p style='color:#2ecc71; font-weight:bold;'>$msg_ok</p>"; ?>
    </div>

    <div class="section">
        <h3><i class="fas fa-tools"></i> Injecter des Tokens (Mint)</h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: 12px; margin-top: 15px;">
            <input type="text" name="target_pid" placeholder="Principal ID du destinataire" required 
                style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; background: #ffffff; color: #333; box-sizing: border-box; font-size: 0.95rem;">
            
            <input type="number" name="amount" placeholder="Montant à créer" required 
                style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; background: #ffffff; color: #333; box-sizing: border-box; font-size: 0.95rem;">
            
            <button type="submit" name="mint" 
                style="width: 100%; padding: 14px; background: #2980b9; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                CRÉER ET ENVOYER
            </button>
        </form>
    </div>

    <div class="section">
        <h3><i class="fas fa-users"></i> Utilisateurs Enregistrés</h3>
        <table style="width:100%; border-collapse: collapse; font-size: 0.85rem; margin-top: 10px;">
            <thead>
                <tr style="background:#34495e; color:white; text-align: left;">
                    <th style="padding:12px;">Username</th>
                    <th>Principal ID</th>
                    <th>Solde</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $users = mysqli_query($conn, "SELECT * FROM users_dahniztoken ORDER BY balance DESC");
                while($u = mysqli_fetch_assoc($users)) {
                    $is_admin = ($u['username'] === 'hanifi');
                    echo "<tr style='border-bottom: 1px solid #eee;'>
                            <td style='padding:12px; font-weight:bold;'>".strtoupper($u['username'])."</td>
                            <td><small style='color:#7f8c8d; font-family: monospace;'>".$u['principal_id']."</small></td>
                            <td style='color:#27ae60; font-weight:bold;'>".number_format($u['balance'])." DNT</td>
                            <td style='text-align:center;'>";
                    
                    if ($is_admin) {
                        echo "<span style='color:#f1c40f;' title='Administrateur'><i class='fas fa-crown'></i></span>";
                    } else {
                        echo "<a href='admin_dahniz.php?delete=".$u['id']."' 
                                 style='color:#e74c3c; text-decoration:none; font-size:0.8rem; border:1px solid #e74c3c; padding:5px 10px; border-radius:4px;' 
                                 onclick='return confirm(\"Bannir définitivement cet utilisateur ?\")'>
                                 <i class='fas fa-trash-alt'></i> Bannir
                              </a>";
                    }
                    
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="simple-footer" style="text-align:center; margin-top:30px; padding:20px; color:#7f8c8d;">
    <p>Copyright © 2000-2026 ® <strong>AhniZTech</strong>, Mr. <a href="https://khelaf-hanifi.com/" target="_blank" style="color:#3498db; text-decoration:none;">Hanifi Khelaf</a>.</p>
    <div class="social-icons" style="margin-top:10px;">
        <a href="https://www.facebook.com/khelaf.hanifi/" style="margin:0 10px; color:#7f8c8d;"><i class="fab fa-facebook-f"></i></a>
        <a href="https://x.com/KhelafHanifi" style="margin:0 10px; color:#7f8c8d;"><i class="fab fa-twitter"></i></a>
        <a href="https://www.instagram.com/khelaf_nath_wahniz" style="margin:0 10px; color:#7f8c8d;"><i class="fab fa-instagram"></i></a>
        <a href="https://www.linkedin.com/in/khelaf-hanifi-51666549/" style="margin:0 10px; color:#7f8c8d;"><i class="fab fa-linkedin-in"></i></a>
    </div>
</footer>

</body>
</html>
