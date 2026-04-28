<?php
/**
 * Admin — Shared guard + premium HTML shell.
 */
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$adminUser = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');

function adminHead(string $title, string $subtitle = ''): void {
    global $adminUser;
    $current = basename($_SERVER['PHP_SELF']);
    $pdo = getDB();
    $unreadCount  = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — Holistic Wellness Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
    /* ── CSS Variables ── */
    :root {
        --primary:   #5a7d7c;
        --primary-d: #436865;
        --accent:    #d2aa7e;
        --accent-d:  #c09265;
        --dark:      #1e2a35;
        --sidebar-w: 260px;
        --topbar-h:  68px;
        --bg:        #f0f2f6;
        --white:     #ffffff;
        --text:      #374151;
        --text-muted:#9ca3af;
        --border:    #e5e7eb;
        --radius:    12px;
        --shadow:    0 1px 3px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.06);
        --shadow-md: 0 4px 24px rgba(0,0,0,0.12);
        --transition: all 0.22s ease;
    }

    /* ── Reset ── */
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    html { font-size: 15px; }
    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        display: flex;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ════════════════════════
       SIDEBAR
    ════════════════════════ */
    .sidebar {
        width: var(--sidebar-w);
        background: var(--dark);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        z-index: 100;
        transition: var(--transition);
        overflow: hidden;
    }
    /* Brand */
    .sidebar-brand {
        padding: 1.5rem 1.4rem 1.3rem;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        display: flex; align-items: center; gap: 0.85rem;
    }
    .brand-logo {
        width: 40px; height: 40px; border-radius: 10px;
        background: linear-gradient(135deg, var(--primary), var(--primary-d));
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(90,125,124,0.4);
    }
    .brand-text { min-width: 0; }
    .brand-text h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1rem; font-weight: 600;
        color: white; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .brand-text span {
        font-size: 0.7rem; color: rgba(255,255,255,0.4);
        text-transform: uppercase; letter-spacing: 1px;
    }

    /* Nav */
    .sidebar-nav { padding: 1.2rem 0.75rem; flex: 1; overflow-y: auto; }
    .nav-section-label {
        font-size: 0.65rem; font-weight: 600;
        color: rgba(255,255,255,0.3);
        text-transform: uppercase; letter-spacing: 1.5px;
        padding: 0.5rem 0.75rem; margin-top: 0.5rem;
    }
    .nav-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.72rem 0.85rem;
        border-radius: 9px;
        color: rgba(255,255,255,0.55);
        text-decoration: none;
        font-size: 0.88rem; font-weight: 500;
        transition: var(--transition);
        margin-bottom: 2px;
        position: relative;
    }
    .nav-item:hover { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.9); }
    .nav-item.active {
        background: linear-gradient(135deg, rgba(90,125,124,0.35), rgba(67,104,101,0.25));
        color: white;
        box-shadow: inset 0 0 0 1px rgba(90,125,124,0.3);
    }
    .nav-item i { width: 18px; text-align: center; font-size: 0.9rem; }
    .nav-badge {
        margin-left: auto;
        background: var(--accent);
        color: white;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 50px;
        min-width: 20px; text-align: center;
    }
    .nav-badge.red { background: #ef4444; }

    /* Sidebar footer */
    .sidebar-footer {
        padding: 1rem 0.75rem;
        border-top: 1px solid rgba(255,255,255,0.07);
    }
    .user-card {
        display: flex; align-items: center; gap: 0.75rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .user-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 700; color: white; flex-shrink: 0;
    }
    .user-info { min-width: 0; }
    .user-info strong { display: block; font-size: 0.85rem; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-info span { font-size: 0.72rem; color: rgba(255,255,255,0.4); }
    .sidebar-actions { display: flex; gap: 0.5rem; }
    .sidebar-action-btn {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        padding: 0.55rem;
        border-radius: 8px;
        color: rgba(255,255,255,0.5);
        text-decoration: none;
        font-size: 0.78rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.07);
        transition: var(--transition);
    }
    .sidebar-action-btn:hover { background: rgba(255,255,255,0.1); color: white; }
    .sidebar-action-btn.logout:hover { background: rgba(239,68,68,0.15); color: #f87171; border-color: rgba(239,68,68,0.3); }

    /* ════════════════════════
       MAIN CONTENT
    ════════════════════════ */
    .main-wrap {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* Topbar */
    .topbar {
        height: var(--topbar-h);
        background: var(--white);
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center;
        padding: 0 2rem;
        position: sticky; top: 0; z-index: 50;
        gap: 1rem;
    }
    .topbar-left { flex: 1; }
    .topbar-title { font-size: 1.1rem; font-weight: 600; color: var(--dark); }
    .topbar-sub { font-size: 0.78rem; color: var(--text-muted); margin-top: 1px; }
    .topbar-right { display: flex; align-items: center; gap: 0.75rem; }
    .topbar-pill {
        display: flex; align-items: center; gap: 0.5rem;
        background: #f9fafb;
        border: 1px solid var(--border);
        border-radius: 50px;
        padding: 0.4rem 0.9rem;
        font-size: 0.82rem; color: var(--text-muted);
    }
    .topbar-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; }
    .topbar-time { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; }

    /* Page content */
    .page-content { padding: 2rem; flex: 1; }

    /* ── Flash Messages ── */
    .flash {
        display: flex; align-items: center; gap: 0.8rem;
        padding: 0.9rem 1.2rem; border-radius: var(--radius);
        margin-bottom: 1.5rem; font-size: 0.88rem; font-weight: 500;
        animation: slideIn 0.35s ease;
        border: 1px solid;
    }
    .flash.success { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
    .flash.error   { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
    @keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

    /* ── Stat Cards ── */
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 1.8rem; }
    .stat-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 1.4rem 1.5rem;
        box-shadow: var(--shadow);
        display: flex; align-items: flex-start; gap: 1rem;
        border: 1px solid var(--border);
        transition: var(--transition);
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius) var(--radius) 0 0;
    }
    .stat-card.teal::before  { background: linear-gradient(90deg, var(--primary), var(--primary-d)); }
    .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.green::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .stat-card.blue::before  { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; flex-shrink: 0;
    }
    .stat-icon.teal  { background: rgba(90,125,124,0.1); color: var(--primary); }
    .stat-icon.amber { background: rgba(245,158,11,0.1); color: #d97706; }
    .stat-icon.green { background: rgba(34,197,94,0.1);  color: #16a34a; }
    .stat-icon.blue  { background: rgba(59,130,246,0.1); color: #1d4ed8; }
    .stat-info { flex: 1; }
    .stat-num { font-size: 1.9rem; font-weight: 700; color: var(--dark); line-height: 1; font-family: 'Playfair Display', serif; }
    .stat-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.3rem; font-weight: 500; }
    .stat-sub { font-size: 0.73rem; color: var(--text-muted); margin-top: 0.5rem; }

    /* ── Panels ── */
    .panel {
        background: var(--white);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .panel-head {
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .panel-head-left { display: flex; align-items: center; gap: 0.6rem; }
    .panel-icon { font-size: 0.95rem; color: var(--primary); }
    .panel-head h3 { font-size: 0.95rem; font-weight: 600; color: var(--dark); }
    .panel-head small { font-size: 0.78rem; color: var(--text-muted); margin-left: 0.5rem; }

    /* ── Data Table ── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        background: #f9fafb;
        color: var(--text-muted);
        font-size: 0.73rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        padding: 0.75rem 1.2rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .data-table td {
        padding: 0.85rem 1.2rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.85rem;
        color: var(--text);
        vertical-align: middle;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr { transition: var(--transition); }
    .data-table tbody tr:hover td { background: #fafbfc; }

    /* Avatars in table */
    .client-cell { display: flex; align-items: center; gap: 0.7rem; }
    .client-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white; font-size: 0.8rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .client-name { font-weight: 600; font-size: 0.85rem; color: var(--dark); }
    .client-email { font-size: 0.75rem; color: var(--text-muted); }

    /* ── Badges ── */
    .badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.28rem 0.75rem; border-radius: 50px;
        font-size: 0.72rem; font-weight: 600;
        white-space: nowrap;
    }
    .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .badge-pending   { background: #fef3c7; color: #92400e; } .badge-pending::before   { background: #f59e0b; }
    .badge-confirmed { background: #d1fae5; color: #065f46; } .badge-confirmed::before { background: #10b981; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; } .badge-cancelled::before { background: #ef4444; }
    .badge-unread    { background: #dbeafe; color: #1e40af; } .badge-unread::before    { background: #3b82f6; }
    .badge-read      { background: #f3f4f6; color: #6b7280; } .badge-read::before      { background: #9ca3af; }

    /* ── Buttons ── */
    .btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.55rem 1.1rem; border-radius: 8px;
        font-size: 0.82rem; font-weight: 600;
        cursor: pointer; border: none;
        text-decoration: none; transition: var(--transition);
        font-family: 'Inter', sans-serif;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-d); transform: translateY(-1px); }
    .btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
    .btn-ghost:hover { background: #f9fafb; color: var(--text); }
    .btn-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .btn-danger:hover { background: #fecaca; }
    .btn-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .btn-success:hover { background: #a7f3d0; }
    .btn-sm { padding: 0.35rem 0.8rem; font-size: 0.76rem; }
    .btn-icon { padding: 0.45rem; width: 32px; height: 32px; justify-content: center; }

    /* ── Inline status select ── */
    .status-select {
        border: 1px solid var(--border); border-radius: 7px;
        padding: 0.3rem 0.6rem; font-size: 0.8rem;
        font-family: 'Inter', sans-serif; cursor: pointer;
        background: white; color: var(--text);
        transition: var(--transition);
    }
    .status-select:focus { outline: none; border-color: var(--primary); }

    /* ── Filter tabs ── */
    .filter-tabs { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
    .filter-tab {
        padding: 0.45rem 1rem; border-radius: 8px;
        font-size: 0.8rem; font-weight: 500;
        cursor: pointer; text-decoration: none;
        border: 1px solid var(--border);
        color: var(--text-muted); background: var(--white);
        transition: var(--transition);
    }
    .filter-tab:hover { background: #f9fafb; color: var(--text); }
    .filter-tab.active { background: var(--primary); color: white; border-color: var(--primary); }

    /* ── Empty State ── */
    .empty-state { padding: 3.5rem; text-align: center; color: var(--text-muted); }
    .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.3; display: block; }
    .empty-state p { font-size: 0.9rem; }

    /* ── Pagination ── */
    .pagination { display: flex; gap: 0.4rem; justify-content: center; margin-top: 1.5rem; }
    .page-btn {
        padding: 0.45rem 0.9rem; border-radius: 8px;
        font-size: 0.8rem; font-weight: 500;
        text-decoration: none; border: 1px solid var(--border);
        color: var(--text-muted); background: var(--white);
        transition: var(--transition);
    }
    .page-btn:hover { background: #f9fafb; color: var(--text); }
    .page-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

    /* ── Activity Feed ── */
    .activity-item {
        display: flex; gap: 0.9rem; padding: 0.9rem 1.2rem;
        border-bottom: 1px solid #f3f4f6; align-items: flex-start;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; flex-shrink: 0; margin-top: 2px;
    }
    .activity-text { flex: 1; }
    .activity-text strong { font-size: 0.85rem; color: var(--dark); }
    .activity-text p { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem; }
    .activity-time { font-size: 0.73rem; color: var(--text-muted); white-space: nowrap; }

    /* ── Responsive ── */
    @media(max-width: 900px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main-wrap { margin-left: 0; }
    }
    </style>
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">🌿</div>
        <div class="brand-text">
            <h2>Holistic Wellness</h2>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="index.php"    class="nav-item <?= $current === 'index.php'    ? 'active' : '' ?>">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>
        <a href="bookings.php" class="nav-item <?= $current === 'bookings.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Bookings
            <?php if ($pendingCount > 0): ?>
            <span class="nav-badge red"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="contacts.php" class="nav-item <?= $current === 'contacts.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Messages
            <?php if ($unreadCount > 0): ?>
            <span class="nav-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="users.php" class="nav-item <?= $current === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clients
        </a>
        <a href="settings.php" class="nav-item <?= $current === 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-gear"></i> Settings
        </a>

        <div class="nav-section-label" style="margin-top:1rem">Site</div>
        <a href="../index.php" target="_blank" class="nav-item">
            <i class="fas fa-globe"></i> View Website <i class="fas fa-arrow-up-right-from-square" style="font-size:0.65rem;margin-left:auto;opacity:0.5"></i>
        </a>
        <a href="../book.php" target="_blank" class="nav-item">
            <i class="fas fa-calendar-plus"></i> Booking Page
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($adminUser, 0, 1)) ?></div>
            <div class="user-info">
                <strong><?= $adminUser ?></strong>
                <span>Administrator</span>
            </div>
        </div>
        <div class="sidebar-actions">
            <a href="../index.php" target="_blank" class="sidebar-action-btn" title="View site">
                <i class="fas fa-eye"></i> Site
            </a>
            <a href="logout.php" class="sidebar-action-btn logout" title="Logout">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
</aside>

<!-- ── Main ── -->
<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-left">
            <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
            <?php if ($subtitle): ?>
            <div class="topbar-sub"><?= htmlspecialchars($subtitle) ?></div>
            <?php endif; ?>
        </div>
        <div class="topbar-right">
            <div class="topbar-pill">
                <span class="dot"></span>
                Live
            </div>
            <div class="topbar-time" id="adminClock"></div>
        </div>
    </header>
    <main class="page-content">
<?php
}

function adminFoot(): void {
    echo '</main></div>';
    ?>
    <script>
    // Live clock
    function updateClock() {
        const now = new Date();
        document.getElementById('adminClock').textContent =
            now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit', hour12:true}) + ' · ' +
            now.toLocaleDateString('en-US', {weekday:'short', month:'short', day:'numeric'});
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Auto-submit status select
    document.querySelectorAll('.status-select').forEach(function(sel) {
        sel.addEventListener('change', function() { this.closest('form').submit(); });
    });

    // Accordion (shared pages)
    document.querySelectorAll('.accordion-header').forEach(function(header) {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const isActive = this.classList.contains('active');
            document.querySelectorAll('.accordion-header').forEach(function(h) {
                h.classList.remove('active');
                h.querySelector('.toggle-icon') && (h.querySelector('.toggle-icon').textContent = '+');
                h.nextElementSibling.style.maxHeight = null;
            });
            if (!isActive) {
                this.classList.add('active');
                this.querySelector('.toggle-icon') && (this.querySelector('.toggle-icon').textContent = '−');
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
    </script>
    </body>
    </html>
<?php
}
