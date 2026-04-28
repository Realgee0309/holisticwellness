<?php
$pageTitle = 'My Profile';
$metaDesc  = 'Update your Holistic Wellness account details.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/user_auth.php';
requireLogin();
$user = getCurrentUser();
$pdo  = getDB();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $newPwd  = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    $errors = [];
    if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if ($newPwd && strlen($newPwd) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($newPwd && $newPwd !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check email uniqueness (excluding self)
        $chk = $pdo->prepare("SELECT id FROM users WHERE email=:e AND id!=:id");
        $chk->execute([':e'=>$email, ':id'=>$user['id']]);
        if ($chk->fetch()) { $errors[] = 'That email is already used by another account.'; }
    }

    if (empty($errors)) {
        $is_anon = !empty($_POST['is_anonymous']) ? 1 : 0;
        if ($newPwd) {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET name=:n, email=:e, password_hash=:h, is_anonymous=:a WHERE id=:id");
            $stmt->execute([':n'=>htmlspecialchars($name, ENT_QUOTES), ':e'=>$email, ':h'=>$hash, ':a'=>$is_anon, ':id'=>$user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=:n, email=:e, is_anonymous=:a WHERE id=:id");
            $stmt->execute([':n'=>htmlspecialchars($name, ENT_QUOTES), ':e'=>$email, ':a'=>$is_anon, ':id'=>$user['id']]);
        }
        // Refresh session
        $_SESSION['user_name']      = $name;
        $_SESSION['user_email']     = $email;
        $_SESSION['user_anonymous'] = $is_anon;
        setFlash('success', 'Profile updated successfully.');
        header('Location: profile.php');
        exit;
    } else {
        setFlash('error', implode(' | ', $errors));
    }
}

// Fresh user data from DB
$dbUser = $pdo->prepare("SELECT * FROM users WHERE id=:id");
$dbUser->execute([':id' => $user['id']]);
$dbUser = $dbUser->fetch();

require_once __DIR__ . '/includes/header.php';
?>
<style>
.profile-page { padding: 4rem 0; min-height: 80vh; background: linear-gradient(135deg, #f7f4f1, #e8eeee); }
.profile-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(90,125,124,0.12); max-width: 600px; margin: 0 auto; overflow: hidden; }
.profile-banner { background: linear-gradient(135deg, var(--primary), var(--primary-d)); padding: 2.5rem; color: white; display: flex; align-items: center; gap: 1.5rem; }
.profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 700; flex-shrink: 0; }
.profile-banner h1 { font-size: 1.3rem; font-weight: 600; margin-bottom: 0.2rem; }
.profile-banner p { opacity: 0.8; font-size: 0.85rem; }
.profile-body { padding: 2rem 2.5rem; }
.form-group { margin-bottom: 1.3rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.45rem; }
.form-control { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; font-family: inherit; color: #1f2937; transition: all 0.3s; background: #fafafa; }
.form-control:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(90,125,124,0.12); }
.section-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary); margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--secondary); }
.anon-toggle { display: flex; align-items: flex-start; gap: 0.75rem; background: #eff6ff; border: 1.5px solid #bfdbfe; padding: 1.1rem 1.2rem; border-radius: 12px; cursor: pointer; }
.anon-toggle input[type="checkbox"] { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--primary); flex-shrink: 0; }
.anon-toggle strong { font-size: 0.88rem; color: #1e40af; display: block; margin-bottom: 0.2rem; }
.anon-toggle span { font-size: 0.8rem; color: #64748b; }
.btn-save { width: 100%; padding: 0.9rem; background: linear-gradient(135deg, var(--primary), var(--primary-d)); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all 0.3s; margin-top: 1.5rem; box-shadow: 0 6px 20px rgba(90,125,124,0.3); }
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(90,125,124,0.4); }
.back-link { text-align: center; margin-top: 1.2rem; }
.back-link a { color: var(--primary); text-decoration: none; font-size: 0.88rem; }
</style>

<div class="profile-page">
    <div class="container">
        <?php renderFlash(); ?>
        <div class="profile-card">
            <div class="profile-banner">
                <div class="profile-avatar"><?= strtoupper(substr($dbUser['name'], 0, 1)) ?></div>
                <div>
                    <h1><?= $dbUser['is_anonymous'] ? 'Anonymous User' : htmlspecialchars($dbUser['name']) ?></h1>
                    <p>Member since <?= date('F Y', strtotime($dbUser['created_at'])) ?></p>
                    <p><?= htmlspecialchars($dbUser['email']) ?></p>
                </div>
            </div>
            <div class="profile-body">
                <form method="POST">
                    <div class="section-label">Personal Information</div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($dbUser['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($dbUser['email']) ?>">
                    </div>

                    <div class="section-label">Privacy</div>
                    <label class="anon-toggle">
                        <input type="checkbox" name="is_anonymous" <?= $dbUser['is_anonymous'] ? 'checked' : '' ?>>
                        <div>
                            <strong>🔒 Prefer anonymity</strong>
                            <span>Your real name stays hidden from our team. You still have full access to your dashboard and booking history.</span>
                        </div>
                    </label>

                    <div class="section-label">Change Password <span style="font-size:0.75rem;font-weight:400;color:#aaa;">(leave blank to keep current)</span></div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Min. 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="btn-save"><i class="fas fa-check" style="margin-right:0.5rem"></i> Save Changes</button>
                </form>
                <div class="back-link"><a href="dashboard.php">← Back to Dashboard</a></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
