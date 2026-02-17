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
$msg = "";
$show_confetti = false;

// 2. LOGIQUE REWARD AUTOMATIQUE (50 DNT / 5 MIN)
$now = new DateTime();
$last_act = new DateTime($user['last_activity_reward']); 
$diff = $now->getTimestamp() - $last_act->getTimestamp();

if ($diff >= 300) { 
    mysqli_query($conn, "UPDATE users_dahniztoken SET balance = balance + 50, last_activity_reward = NOW() WHERE id = $uid");
    mysqli_query($conn, "INSERT INTO transactions_dahniztoken (sender_pid, receiver_pid, amount, created_at) VALUES ('ACTIVITY_REWARD', '$my_pid', 50, NOW())");
    header("Location: index.php"); 
    exit();
}

// 3. ACTION: CLAIM FAUCET
if (isset($_POST['faucet']) && $user['has_claimed_faucet'] == 0) {
    mysqli_query($conn, "UPDATE users_dahniztoken SET balance = balance + 10000, has_claimed_faucet = 1 WHERE id = $uid");
    mysqli_query($conn, "INSERT INTO transactions_dahniztoken (sender_pid, receiver_pid, amount, created_at) VALUES ('SYSTEM_FAUCET', '$my_pid', 10000, NOW())");
    $show_confetti = true; 
    header("Refresh: 2; url=index.php");
}

// 4. ACTION: TRANSFERT
if (isset($_POST['transfer'])) {
    $to_pid = mysqli_real_escape_string($conn, $_POST['recipient']);
    $amount = intval($_POST['amount']);
    if ($user['balance'] >= $amount && $amount > 0) {
        mysqli_query($conn, "UPDATE users_dahniztoken SET balance = balance - $amount WHERE id = $uid");
        mysqli_query($conn, "UPDATE users_dahniztoken SET balance = balance + $amount WHERE principal_id = '$to_pid'");
        mysqli_query($conn, "INSERT INTO transactions_dahniztoken (sender_pid, receiver_pid, amount, created_at) VALUES ('$my_pid', '$to_pid', $amount, NOW())");
        $msg = "Transfert réussi !";
        header("Refresh:1"); 
    } else {
        $msg = "Erreur : Solde insuffisant !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DahnizToken - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #f1c40f;
            --success: #27ae60;
            --info: #3498db;
            --dark: #1a1a1a;
        }

        * { box-sizing: border-box; }
        
        body { 
            margin: 0; 
            padding: 0;
            background: #f4f7f6; 
            font-family: 'Segoe UI', sans-serif; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh;
        }

        .nav-header {
            background: var(--dark);
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .nav-logo { color: white; text-decoration: none; font-weight: bold; font-size: 1.1rem; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ccc; text-decoration: none; font-size: 1.2rem; }
        .nav-links a.active { color: var(--accent); }

        .container {
            width: 95%;
            max-width: 480px;
            margin: 15px auto;
            flex: 1;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        .balance-card { background: var(--primary); color: white; }
        .pid-display { 
            font-size: 0.7rem; 
            background: rgba(255,255,255,0.1); 
            padding: 8px; 
            border-radius: 5px; 
            word-break: break-all;
            display: block;
            margin: 10px 0;
            font-family: monospace;
        }
        .balance-text { font-size: 1.8rem; font-weight: bold; color: var(--accent); margin: 5px 0; }

        h3 { margin-top: 0; font-size: 1.1rem; color: var(--primary); display: flex; align-items: center; gap: 8px; justify-content: center; }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            text-transform: uppercase;
        }
        .btn-claim { background: var(--success); color: white; }
        .btn-send { background: var(--info); color: white; }
        .btn-disabled { background: #bdc3c7; color: white; cursor: not-allowed; }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        footer {
            text-align: center;
            padding: 15px;
            font-size: 0.8rem;
            color: #7f8c8d;
            background: white;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

<nav class="nav-header">
    <a href="index.php" class="nav-logo">💎 Dahniz</a>
    <div class="nav-links">
        <a href="index.php" class="active"><i class="fas fa-home"></i></a>
        <a href="profil.php"><i class="fas fa-user"></i></a>
        <a href="explorer.php"><i class="fas fa-search"></i></a>
        <?php if ($user['username'] === 'hanifi'): ?>
            <a href="admin_dahniz.php" style="color:var(--accent);"><i class="fas fa-crown"></i></a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>

<div class="container">
    <div class="card balance-card">
        <span style="opacity: 0.8; font-size: 0.8rem;">My Principal ID :</span>
        <div class="pid-display"><?php echo $my_pid; ?></div>
        <div class="balance-text"><?php echo number_format($user['balance']); ?> DNT</div>
        <div style="font-size: 0.85rem; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 5px;">
            🎁 Reward in: <span id="timer" style="color:var(--accent); font-weight:bold;">--:--</span>
        </div>
    </div>

    <?php if($msg): ?>
        <div style="background:#e74c3c; color:white; padding:10px; border-radius:8px; text-align:center; margin-bottom:15px;"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3><i class="fas fa-faucet" style="color:var(--success);"></i> Dahniz Faucet</h3>
        <p style="font-size:0.85rem; color:#666; margin-bottom:15px;">Claim 10,000 DNT instantly!</p>
        <form method="POST">
            <button type="submit" name="faucet" class="btn <?php echo $user['has_claimed_faucet'] ? 'btn-disabled' : 'btn-claim'; ?>" <?php echo $user['has_claimed_faucet'] ? 'disabled' : ''; ?>>
                <?php echo $user['has_claimed_faucet'] ? 'ALREADY CLAIMED ✅' : 'CLAIM 10,000 DNT'; ?>
            </button>
        </form>
    </div>

    <div class="card">
        <h3><i class="fas fa-paper-plane" style="color:var(--info);"></i> Transfer</h3>
        <form method="POST">
            <input type="text" name="recipient" placeholder="Recipient Principal ID" required>
            <input type="number" name="amount" placeholder="Amount (DNT)" required>
            <button type="submit" name="transfer" class="btn btn-send">SEND NOW</button>
        </form>
    </div>

    <div class="card">
        <h3 style="justify-content: flex-start;"><i class="fas fa-history"></i> Activity</h3>
        <?php
        $history = mysqli_query($conn, "SELECT * FROM transactions_dahniztoken WHERE sender_pid = '$my_pid' OR receiver_pid = '$my_pid' ORDER BY id DESC LIMIT 5");
        if(mysqli_num_rows($history) > 0) {
            while($row = mysqli_fetch_assoc($history)) {
                $is_received = ($row['receiver_pid'] == $my_pid);
                $color = $is_received ? "#27ae60" : "#e74c3c";
                // AJOUT DE L'ANNÉE ICI (d/m/Y)
                $date = date("d/m/Y H:i", strtotime($row['created_at']));
                echo "<div class='activity-item'>
                        <span style='font-weight:500;'>".($is_received ? "Recv" : "Sent")."</span>
                        <strong style='color:$color;'>".($is_received ? "+" : "-").number_format($row['amount'])."</strong>
                        <span style='color:#999; font-size:0.7rem;'>$date</span>
                      </div>";
            }
        } else {
            echo "<p style='color:#999; padding:10px;'>No activity yet.</p>";
        }
        ?>
    </div>
</div>

<footer>
    <p>Copyright © 2000-2026 ® <strong>AhnizTech</strong><br>Mr. <a href="https://khelaf-hanifi.com/" target="_blank" style="color:var(--info); text-decoration:none;">Hanifi Khelaf</a></p>
</footer>

<script>
function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    var interval = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        display.textContent = (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
        if (--timer < 0) { 
            display.textContent = "READY!"; 
            clearInterval(interval);
            setTimeout(() => { location.reload(); }, 1000); 
        }
    }, 1000);
}
window.onload = function () {
    var remaining = <?php echo max(0, 300 - $diff); ?>;
    startTimer(remaining, document.querySelector('#timer'));
    <?php if($show_confetti): ?>
        confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } });
    <?php endif; ?>
};
</script>
</body>
</html>
