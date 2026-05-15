<?php
require_once("config.php");

session_name(SESSION_NAME);
session_start();

/* Already logged in — go to dashboard */
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: main.php");
    exit();
}

$error   = "";
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'main.php';

/* Sanitize redirect — only allow relative paths on same server */
if (strpos($redirect, '://') !== false || strpos($redirect, '//') === 0) {
    $redirect = 'main.php';
}

/* Handle login */
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['logged_in']  = true;
        $_SESSION['username']   = $username;
        session_regenerate_id(true); /* prevent session fixation */
        header("Location: " . $redirect);
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
        /* Small delay to slow brute force */
        sleep(1);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Connexion</title>
    <style>
        :root {
            --olive-dark:  #1a3a1a;
            --olive-mid:   #2e5c1e;
            --olive-leaf:  #6db33f;
            --olive-pale:  #c8dfa8;
            --olive-bg:    #eef4e6;
            --blue-spark:  #36a4d7;
            --border:      #b0cc90;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background:
                linear-gradient(135deg, #b8d898 0%, #b8d898 22%, transparent 22%),
                linear-gradient(315deg, #b8d898 0%, #b8d898 22%, transparent 22%),
                #f0f4ee;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            box-shadow: 0 12px 40px rgba(26,58,26,0.12);
            padding: 44px 48px;
            width: 100%;
            max-width: 420px;
        }

        /* Logo area */
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo .brand {
            font-size: 26px;
            font-weight: 800;
            color: var(--olive-dark);
            letter-spacing: 1px;
        }

        .login-logo .brand-sub {
            font-size: 11px;
            color: var(--olive-leaf);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .logo-dot {
            display: inline-block;
            width: 7px; height: 7px;
            background: var(--blue-spark);
            border-radius: 50%;
            margin: 0 4px;
            vertical-align: middle;
        }

        /* Title */
        .login-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--olive-dark);
            margin-bottom: 6px;
        }

        .login-sub {
            font-size: 13px;
            color: #7a9a65;
            margin-bottom: 28px;
        }

        /* Form */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #5a7a45;
        }

        .form-group input {
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--olive-dark);
            background: var(--olive-bg);
            outline: none;
            transition: border-color 0.15s, background 0.15s;
            font-family: inherit;
            width: 100%;
        }

        .form-group input:focus {
            border-color: var(--olive-leaf);
            background: white;
        }

        .form-group input::placeholder { color: #9ab890; }

        /* Error */
        .alert-error {
            background: #fde8e8;
            color: #a32d2d;
            border: 1.5px solid #f5b8b8;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* Logged out message */
        .alert-info {
            background: var(--olive-bg);
            color: var(--olive-mid);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--olive-mid);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: 0.15s;
            margin-top: 6px;
        }

        .btn-login:hover { background: var(--olive-dark); }
        .btn-login:active { transform: scale(0.98); }

        /* Divider line */
        hr {
            border: none;
            border-top: 1.5px solid var(--olive-bg);
            margin: 28px 0 20px;
        }

        .login-footer {
            text-align: center;
            font-size: 12px;
            color: #9ab890;
        }
    </style>
</head>
<body>

<div class="login-card">

    <div class="login-logo">
        <div class="brand">RAMO CLEAN</div>
        <div class="brand-sub">
            <span class="logo-dot"></span>
            Espace Administration
            <span class="logo-dot"></span>
        </div>
    </div>

    <div class="login-title">Connexion</div>
    <div class="login-sub">Entrez vos identifiants pour accéder au tableau de bord.</div>

    <?php if ($error): ?>
    <div class="alert-error">✗ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out'): ?>
    <div class="alert-info">✓ Vous avez été déconnecté avec succès.</div>
    <?php endif; ?>

    <form method="POST" action="login.php?redirect=<?php echo urlencode($redirect); ?>">

        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" placeholder="admin" required autocomplete="username"
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <button type="submit" name="login" class="btn-login">→ Se connecter</button>

    </form>

    <hr>
    <div class="login-footer">Ramo Clean · Système de gestion interne</div>

</div>

</body>
</html>
