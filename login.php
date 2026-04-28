<?php
$pageTitle = 'Sign In';
$metaDesc  = 'Sign in to your Holistic Wellness account to view your session history and progress notes.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/user_auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$redirect = $_GET['redirect'] ?? '';
require_once __DIR__ . '/includes/header.php';
?>
<style>
.auth-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background: linear-gradient(135deg, #f7f4f1 0%, #e8eeee 100%); }
.auth-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(90,125,124,0.12); padding: 3rem; width: 100%; max-width: 420px; }
.auth-header { text-align: center; margin-bottom: 2.2rem; }
.auth-icon { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, var(--primary), var(--primary-d)); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem; box-shadow: 0 8px 24px rgba(90,125,124,0.3); }
.auth-header h1 { font-size: 1.7rem; color: var(--dark); margin-bottom: 0.4rem; }
.auth-header p { font-size: 0.9rem; color: #888; }
.form-group { margin-bottom: 1.3rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.45rem; }
.input-wrap { position: relative; }
.input-wrap i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.9rem; pointer-events: none; }
.form-control { width: 100%; padding: 0.8rem 1rem 0.8rem 2.8rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; font-family: inherit; color: #1f2937; transition: all 0.3s; background: #fafafa; }
.form-control:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(90,125,124,0.12); }
.remember-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.3rem; }
.remember-row label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.83rem; color: #6b7280; cursor: pointer; }
.remember-row input[type="checkbox"] { accent-color: var(--primary); }
.remember-row a { font-size: 0.83rem; color: var(--primary); text-decoration: none; }
.btn-auth { width: 100%; padding: 0.9rem; background: linear-gradient(135deg, var(--primary), var(--primary-d)); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(90,125,124,0.3); }
.btn-auth:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(90,125,124,0.4); }
.auth-footer { text-align: center; margin-top: 1.8rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; font-size: 0.88rem; color: #888; }
.auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
.guest-banner { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.9rem 1.1rem; text-align: center; margin-top: 1rem; font-size: 0.83rem; color: #6b7280; }
.guest-banner a { color: var(--primary); font-weight: 600; text-decoration: none; }
</style>

<div class="auth-page">
    <div class="auth-card">
        <?php renderFlash(); ?>
        <div class="auth-header">
            <div class="auth-icon">🔑</div>
            <h1>Welcome Back</h1>
            <p>Sign in to view your session history</p>
        </div>

        <form method="POST" action="actions/login.php" novalidate>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" required
                           placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
            </div>
            <div class="remember-row">
                <label><input type="checkbox" name="remember"> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit" class="btn-auth">
                <i class="fas fa-right-to-bracket" style="margin-right:0.5rem"></i> Sign In
            </button>
        </form>

        <div class="guest-banner">
            🛡️ Or <a href="book.php">book a session as a guest</a> — no account needed.
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one free</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
