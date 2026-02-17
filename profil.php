
<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// 1. SÉCURITÉ
if (!isset($_SESSION['dahniz_user'])) {
    header("Location: auth.php");
    exit();
}
$uid = $_SESSION['dahniz_user'];
$res = mysqli_query($conn, "SELECT * FROM users_dahniztoken WHERE id = $uid");
$user = mysqli_fetch_assoc($res);
$my_pid = $user['principal_id'];
// 2. CALCUL DES STATS
$in_res = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions_dahniztoken WHERE receiver_pid = '$my_pid'");
$total_received = mysqli_fetch_assoc($in_res)['total'] ?? 0;
$out_res = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions_dahniztoken WHERE sender_pid = '$my_pid'");
$total_sent = mysqli_fetch_assoc($out_res)['total'] ?? 0;
$count_res = mysqli_query($conn, "SELECT COUNT(*) AS nb FROM transactions_dahniztoken WHERE sender_pid = '$my_pid' OR receiver_pid = '$my_pid'");
$nb_tx = mysqli_fetch_assoc($count_res)['nb'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - DahnizToken</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/.../6.0.0/css/all.min.css">
</head>
<body>
<nav class="nav-header">
    <a href="index.php" class="nav-logo">💎 Mon Profil</a>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="explorer.php"><i class="fas fa-search"></i> Explorer</a>
        <?php if($user['username'] === 'hanifi') echo '<a href="admin_dahniz.php" style="color:#ffd700;">Admin</a>'; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>
<div class="container" style="width: 850px; max-width: 95%;">
    <div class="header-box" style="background: #34495e;">
        <i class="fas fa-user-circle fa-4x" style="color: #f1c40f; margin-bottom: 10px;"></i>
        <h1 style="margin:0;"><?php echo strtoupper($user['username']); ?></h1>
        <p style="font-size: 0.8rem; opacity: 0.8;">Membre actif du réseau DahnizToken</p>
    </div>
    <div class="section">
        <h3><i class="fas fa-chart-line"></i> Mes Statistiques</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 15px;">
            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #27ae60; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <small style="color: #7f8c8d;">TOTAL REÇU</small><br>
                <strong style="font-size: 1.1rem; color: #2ecc71;"><?php echo number_format($total_received); ?> DNT</strong>
            </div>
            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #e74c3c; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <small style="color: #7f8c8d;">TOTAL ENVOYÉ</small><br>
                <strong style="font-size: 1.1rem; color: #e74c3c;"><?php echo number_format($total_sent); ?> DNT</strong>
            </div>
            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #3498db; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <small style="color: #7f8c8d;">OPÉRATIONS</small><br>
                <strong style="font-size: 1.1rem; color: #3498db;"><?php echo $nb_tx; ?></strong>
            </div>
            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #f1c40f; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <small style="color: #7f8c8d;">SOLDE RÉEL</small><br>
                <strong style="font-size: 1.1rem; color: #f39c12;"><?php echo number_format($user['balance']); ?> DNT</strong>
            </div>
        </div>
    </div>
    <div class="section">
        <h3><i class="fas fa-list-ul"></i> Détail de mes gains et activités</h3>
        <div style="margin-top: 15px;">
            <?php
            // On cherche TOUTES les transactions où tu es impliqué
            $history_query = "SELECT * FROM transactions_dahniztoken 
                              WHERE sender_pid = '$my_pid' OR receiver_pid = '$my_pid' 
                              ORDER BY created_at DESC";
            $history_res = mysqli_query($conn, $history_query);
            if (mysqli_num_rows($history_res) > 0) {
                while($item = mysqli_fetch_assoc($history_res)) {
                    $is_received = ($item['receiver_pid'] == $my_pid);
                    // On définit le texte et la couleur selon l'origine
                    if ($item['sender_pid'] == 'SYSTEM_FAUCET') {
                        $label = "🎁 Cadeau de Bienvenue (Faucet)";
                        $color = "#27ae60";
                    } elseif ($item['sender_pid'] == 'RECOMPENSE_ACTIVITE' || $item['sender_pid'] == 'ADMIN_MINT') {
                        $label = "⚡ Bonus d'Activité / Système";
                        $color = "#2980b9";
                    } else {
                        $label = $is_received ? "🤝 Reçu d'un utilisateur" : "📤 Transfert envoyé";
                        $color = $is_received ? "#27ae60" : "#e74c3c";
                    }
                    echo "<div style='background: white; padding: 12px; border-radius: 6px; margin-bottom: 10px; border-left: 5px solid $color; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
                            <div>
                                <span style='font-weight: bold; font-size: 0.9rem;'>$label</span><br>
                                <small style='color: #95a5a6;'>".$item['created_at']."</small>
                            </div>
                            <div style='font-weight: bold; color: $color; font-size: 1rem;'>
                                ".($is_received ? "+" : "-")." ".number_format($item['amount'])." DNT
                            </div>
                          </div>";
                }
            } else {
                // Si c'est vide ici, c'est que les transactions ne sont pas dans la table transactions_dahniztoken !
                echo "<p style='text-align:center; color:#e74c3c; font-weight:bold;'>⚠️ Aucune donnée trouvée dans l'historique.<br>
                      <span style='font-weight:normal; font-size:0.8rem; color:#7f8c8d;'>Vérifie que ta table 'transactions_dahniztoken' contient bien des lignes.</span></p>";
            }
            ?>
        </div>
    </div>
</div>
<footer class="simple-footer">
        <p>Copyright © 2000-2026 ® <strong>AhnizTech</strong><br>Mr. <a href="https://khelaf-hanifi.com/" target="_blank" style="color:var(--info); text-decoration:none;">Hanifi Khelaf</a></p>
</footer>
</body>
</html>
