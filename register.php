<?php
$pageTitle = 'Create Account';
$metaDesc  = 'Create your Holistic Wellness account to track your session history and progress.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/user_auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
require_once __DIR__ . '/includes/header.php';
?>
<style>
.auth-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background: linear-gradient(135deg, #f7f4f1 0%, #e8eeee 100%); }
.auth-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(90,125,124,0.12); padding: 3rem; width: 100%; max-width: 480px; }
.auth-header { text-align: center; margin-bottom: 2.2rem; }
.auth-icon { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, var(--primary), var(--primary-d)); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem; box-shadow: 0 8px 24px rgba(90,125,124,0.3); }
.auth-header h1 { font-size: 1.7rem; color: var(--dark); margin-bottom: 0.4rem; }
.auth-header p { font-size: 0.9rem; color: #888; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.45rem; }
.form-control { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; font-family: inherit; color: #1f2937; transition: all 0.3s; background: #fafafa; }
.form-control:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(90,125,124,0.12); }
.anon-box { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1.5px solid #bae6fd; border-radius: 12px; padding: 1.2rem; margin: 1.2rem 0; }
.anon-box label { display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; }
.anon-box input[type="checkbox"] { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--primary); flex-shrink: 0; }
.anon-label-text strong { display: block; font-size: 0.88rem; color: #1e40af; margin-bottom: 0.2rem; }
.anon-label-text span { font-size: 0.8rem; color: #64748b; line-height: 1.5; }
.btn-auth { width: 100%; padding: 0.9rem; background: linear-gradient(135deg, var(--primary), var(--primary-d)); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all 0.3s; margin-top: 0.5rem; box-shadow: 0 6px 20px rgba(90,125,124,0.3); }
.btn-auth:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(90,125,124,0.4); }
.auth-footer { text-align: center; margin-top: 1.8rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; font-size: 0.88rem; color: #888; }
.auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
.divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
.divider::before, .divider::after { content:''; flex:1; height:1px; background:#e5e7eb; }
.divider span { font-size: 0.8rem; color: #aaa; }
.strength-bar { height: 4px; border-radius: 2px; margin-top: 0.4rem; background: #e5e7eb; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 2px; transition: all 0.3s; width: 0; }
</style>

<div class="auth-page">
    <div class="auth-card">
        <?php renderFlash(); ?>
        <div class="auth-header">
            <div class="auth-icon">🌿</div>
            <h1>Create Your Account</h1>
            <p>Join Holistic Wellness to track your journey</p>
        </div>

        <form method="POST" action="actions/register.php" id="registerForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name <span style="color:#ef4444">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="Peter Kimani Wafula"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email <span style="color:#ef4444">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="you@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password <span style="color:#ef4444">*</span></label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Min. 8 characters">
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div style="font-size:0.75rem;color:#aaa;margin-top:0.3rem" id="strengthLabel"></div>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password <span style="color:#ef4444">*</span></label>
                <input type="password" id="confirm" name="confirm" class="form-control" required placeholder="Repeat password">
            </div>

            <!-- Anonymity Option -->
            <div class="anon-box">
                <label>
                    <input type="checkbox" name="is_anonymous" id="is_anonymous" <?= !empty($_POST['is_anonymous']) ? 'checked' : '' ?>>
                    <div class="anon-label-text">
                        <strong>🔒 I prefer anonymity</strong>
                        <span>Your real name will be hidden from our team. You'll appear as "Anonymous User" in communications, while still being able to track your sessions privately.</span>
                    </div>
                </label>
            </div>

            <button type="submit" class="btn-auth">
                <i class="fas fa-user-plus" style="margin-right:0.5rem"></i> Create My Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in here</a>
            <br><br>
            <a href="book.php" style="color:#aaa;font-weight:400">Or continue as a guest →</a>
        </div>
    </div>
</div>

<script>
// Password strength indicator
const pwd = document.getElementById('password');
const fill = document.getElementById('strengthFill');
const label = document.getElementById('strengthLabel');
const levels = [
    { color:'#ef4444', text:'Too short', width:'20%' },
    { color:'#f97316', text:'Weak',      width:'40%' },
    { color:'#eab308', text:'Fair',      width:'60%' },
    { color:'#22c55e', text:'Strong',    width:'80%' },
    { color:'#16a34a', text:'Very strong', width:'100%' },
];
pwd.addEventListener('input', function() {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const l = levels[Math.min(score, 4)];
    fill.style.width = v.length ? l.width : '0';
    fill.style.background = l.color;
    label.textContent = v.length ? l.text : '';
    label.style.color = l.color;
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
