<?php
/**
 * Shared Header — now user-auth aware
 */
require_once __DIR__ . '/user_auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
function isActive(string $page): string {
    global $currentPage;
    return $currentPage === $page ? ' active' : '';
}
$depth = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false ? 1 : 0;
$root  = str_repeat('../', $depth);
$user  = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Holistic Wellness') ?> — Professional Online Counseling</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'Holistic Wellness offers professional online therapy for individuals, couples, and families.') ?>">
    <link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
    /* ── User menu in nav ── */
    .user-menu { position: relative; }
    .user-menu-btn {
        display: flex; align-items: center; gap: 0.5rem;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        color: white; padding: 0.4rem 0.9rem;
        border-radius: 50px; cursor: pointer;
        font-size: 0.88rem; font-weight: 500;
        transition: all 0.3s; text-decoration: none;
    }
    .user-menu-btn:hover { background: rgba(255,255,255,0.25); }
    .user-avatar-sm {
        width: 26px; height: 26px; border-radius: 50%;
        background: var(--accent);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; color: white;
    }
    .user-dropdown {
        display: none; position: absolute; top: calc(100% + 8px); right: 0;
        background: white; border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        border: 1px solid #e5e7eb;
        min-width: 200px; z-index: 999;
        overflow: hidden;
    }
    .user-dropdown.open { display: block; animation: fadeDown 0.2s ease; }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    .dropdown-header {
        padding: 0.9rem 1rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-d));
        color: white;
    }
    .dropdown-header strong { display: block; font-size: 0.9rem; }
    .dropdown-header span { font-size: 0.75rem; opacity: 0.8; }
    .dropdown-item {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.7rem 1rem; color: #374151;
        text-decoration: none; font-size: 0.85rem;
        transition: background 0.2s;
    }
    .dropdown-item:hover { background: #f9fafb; }
    .dropdown-item i { width: 16px; color: var(--primary); font-size: 0.82rem; }
    .dropdown-divider { height: 1px; background: #f3f4f6; margin: 0.3rem 0; }
    .dropdown-item.logout { color: #dc2626; }
    .dropdown-item.logout i { color: #dc2626; }

    /* Auth nav buttons */
    .nav-auth { display: flex; gap: 0.5rem; align-items: center; }
    .btn-nav-login {
        color: white; text-decoration: none;
        font-size: 0.88rem; font-weight: 500;
        padding: 0.4rem 0.9rem;
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 50px; transition: all 0.3s;
    }
    .btn-nav-login:hover { background: rgba(255,255,255,0.15); }
    .btn-nav-register {
        background: var(--accent); color: white;
        text-decoration: none; font-size: 0.88rem; font-weight: 600;
        padding: 0.4rem 0.9rem; border-radius: 50px;
        transition: all 0.3s;
    }
    .btn-nav-register:hover { background: var(--accent-d); }
    </style>
</head>
<body>
<header>
    <div class="container">
        <nav>
            <a href="<?= $root ?>index.php" class="logo">Holistic Wellness<span>.</span></a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="<?= $root ?>index.php"    class="<?= isActive('index.php') ?>">Home</a></li>
                <li><a href="<?= $root ?>about.php"    class="<?= isActive('about.php') ?>">About</a></li>
                <li><a href="<?= $root ?>services.php" class="<?= isActive('services.php') ?>">Services</a></li>
                <li><a href="<?= $root ?>book.php"     class="<?= isActive('book.php') ?>">Book</a></li>
                <li><a href="<?= $root ?>faq.php"      class="<?= isActive('faq.php') ?>">FAQ</a></li>
                <li><a href="<?= $root ?>contact.php"  class="<?= isActive('contact.php') ?>">Contact</a></li>
            </ul>

            <!-- Auth Area -->
            <?php if ($user): ?>
            <div class="user-menu" id="userMenu">
                <a href="#" class="user-menu-btn" id="userMenuBtn">
                    <div class="user-avatar-sm"><?= strtoupper(substr($user['is_anonymous'] ? 'A' : $user['name'], 0, 1)) ?></div>
                    <?= $user['is_anonymous'] ? 'Anonymous' : htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                    <i class="fas fa-chevron-down" style="font-size:0.65rem"></i>
                </a>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <strong><?= $user['is_anonymous'] ? 'Anonymous User' : htmlspecialchars($user['name']) ?></strong>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <a href="<?= $root ?>dashboard.php" class="dropdown-item"><i class="fas fa-gauge"></i> My Dashboard</a>
                    <a href="<?= $root ?>dashboard.php?tab=bookings" class="dropdown-item"><i class="fas fa-calendar-check"></i> My Bookings</a>
                    <a href="<?= $root ?>dashboard.php?tab=notes" class="dropdown-item"><i class="fas fa-notes-medical"></i> Progress Notes</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $root ?>profile.php" class="dropdown-item"><i class="fas fa-user-gear"></i> Edit Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $root ?>logout.php" class="dropdown-item logout"><i class="fas fa-right-from-bracket"></i> Log Out</a>
                </div>
            </div>
            <?php else: ?>
            <div class="nav-auth">
                <a href="<?= $root ?>login.php"    class="btn-nav-login">Log In</a>
                <a href="<?= $root ?>register.php" class="btn-nav-register">Sign Up</a>
            </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script>
document.getElementById('navToggle').addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('open');
});
<?php if ($user): ?>
document.getElementById('userMenuBtn').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('userDropdown').classList.toggle('open');
});
document.addEventListener('click', function(e) {
    if (!document.getElementById('userMenu').contains(e.target)) {
        document.getElementById('userDropdown').classList.remove('open');
    }
});
<?php endif; ?>
</script>
