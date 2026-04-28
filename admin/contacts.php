<?php
require_once __DIR__ . '/includes/admin_auth.php';

$pdo = getDB();

// Mark single as read
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    if ($id) $pdo->prepare("UPDATE contacts SET is_read=1 WHERE id=:id")->execute([':id'=>$id]);
    header('Location: contacts.php');
    exit;
}
// Mark all read
if (isset($_GET['readall'])) {
    $pdo->query("UPDATE contacts SET is_read=1");
    header('Location: contacts.php?msg=readall');
    exit;
}
// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) $pdo->prepare("DELETE FROM contacts WHERE id=:id")->execute([':id'=>$id]);
    header('Location: contacts.php?msg=deleted');
    exit;
}

// Counts
$unreadCount = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
$totalCount  = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$readCount   = $totalCount - $unreadCount;

// Filter
$filterRead = $_GET['filter'] ?? '';
$where = '';
if ($filterRead === 'unread') $where = 'WHERE is_read=0';
if ($filterRead === 'read')   $where = 'WHERE is_read=1';

// Pagination
$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = $pdo->query("SELECT COUNT(*) FROM contacts $where")->fetchColumn();
$pages   = max(1, ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT * FROM contacts $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$contacts = $stmt->fetchAll();

adminHead('Messages', 'Manage client contact messages');
?>

<!-- Flash -->
<?php if (isset($_GET['msg'])): ?>
<div class="flash <?= $_GET['msg']==='deleted' ? 'error' : 'success' ?>">
    <i class="fas <?= $_GET['msg']==='deleted' ? 'fa-trash' : 'fa-check-circle' ?>"></i>
    <?php
        $msgs = ['deleted'=>'Message deleted.','readall'=>'All messages marked as read.'];
        echo $msgs[$_GET['msg']] ?? 'Done.';
    ?>
</div>
<?php endif; ?>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <a href="contacts.php" class="filter-tab <?= !$filterRead ? 'active' : '' ?>">
        All (<?= $totalCount ?>)
    </a>
    <a href="contacts.php?filter=unread" class="filter-tab <?= $filterRead==='unread' ? 'active' : '' ?>"
       style="<?= ($filterRead !== 'unread' && $unreadCount > 0) ? 'border-color:#93c5fd;color:#1e40af;background:#dbeafe' : '' ?>">
        <i class="fas fa-circle" style="font-size:0.5rem;color:#3b82f6"></i> Unread (<?= $unreadCount ?>)
    </a>
    <a href="contacts.php?filter=read" class="filter-tab <?= $filterRead==='read' ? 'active' : '' ?>">
        Read (<?= $readCount ?>)
    </a>
    <?php if ($unreadCount > 0): ?>
    <a href="contacts.php?readall=1" class="btn btn-success btn-sm" style="margin-left:auto">
        <i class="fas fa-check-double"></i> Mark All Read
    </a>
    <?php endif; ?>
</div>

<!-- Table Panel -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-head-left">
            <i class="fas fa-envelope panel-icon"></i>
            <h3>Contact Messages</h3>
            <?php if ($unreadCount > 0): ?>
            <span class="badge badge-unread"><?= $unreadCount ?> new</span>
            <?php endif; ?>
        </div>
        <span style="font-size:0.78rem;color:var(--text-muted)">Showing <?= count($contacts) ?> of <?= $total ?></span>
    </div>

    <?php if (empty($contacts)): ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>No messages found<?= $filterRead ? ' in "' . $filterRead . '" filter' : '' ?>.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Sender</th>
                <th>Subject</th>
                <th>Message Preview</th>
                <th>Status</th>
                <th>Received</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($contacts as $c):
            $initials = strtoupper(substr($c['name'], 0, 1)) . (strpos($c['name'], ' ') !== false ? strtoupper(substr(strrchr($c['name'], ' '), 1, 1)) : '');
            $isUnread = !$c['is_read'];
        ?>
        <tr style="<?= $isUnread ? 'background:#fafeff;' : '' ?>">
            <td style="font-size:0.75rem;color:var(--text-muted);font-weight:600">
                #<?= $c['id'] ?>
                <?php if ($isUnread): ?>
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#3b82f6;margin-left:3px;vertical-align:middle"></span>
                <?php endif; ?>
            </td>
            <td>
                <div class="client-cell">
                    <div class="client-avatar" style="<?= !$isUnread ? 'opacity:0.55' : '' ?>"><?= $initials ?></div>
                    <div>
                        <div class="client-name" style="<?= $isUnread ? 'font-weight:700' : '' ?>"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="client-email"><?= htmlspecialchars($c['email']) ?></div>
                    </div>
                </div>
            </td>
            <td>
                <span style="font-size:0.82rem;font-weight:<?= $isUnread ? '600' : '400' ?>;color:var(--dark)">
                    <?= htmlspecialchars($c['subject']) ?>
                </span>
            </td>
            <td style="max-width:240px">
                <span style="font-size:0.78rem;color:var(--text-muted)">
                    <?= htmlspecialchars(mb_substr($c['message'], 0, 90)) ?><?= mb_strlen($c['message']) > 90 ? '…' : '' ?>
                </span>
            </td>
            <td>
                <span class="badge <?= $isUnread ? 'badge-unread' : 'badge-read' ?>">
                    <?= $isUnread ? 'Unread' : 'Read' ?>
                </span>
            </td>
            <td style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap">
                <?= date('d M Y', strtotime($c['created_at'])) ?><br>
                <span style="font-size:0.7rem"><?= date('H:i', strtotime($c['created_at'])) ?></span>
            </td>
            <td>
                <div style="display:flex;gap:0.3rem">
                    <a href="mailto:<?= htmlspecialchars($c['email']) ?>?subject=Re: <?= urlencode($c['subject']) ?>"
                       class="btn btn-ghost btn-icon btn-sm" title="Reply by email">
                        <i class="fas fa-reply"></i>
                    </a>
                    <?php if ($isUnread): ?>
                    <a href="contacts.php?read=<?= $c['id'] ?>"
                       class="btn btn-success btn-icon btn-sm" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </a>
                    <?php endif; ?>
                    <a href="contacts.php?delete=<?= $c['id'] ?>"
                       onclick="return confirm('Delete message from <?= htmlspecialchars(addslashes($c['name'])) ?>?')"
                       class="btn btn-danger btn-icon btn-sm" title="Delete">
                        <i class="fas fa-trash-can"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?><?= $filterRead ? '&filter='.$filterRead : '' ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
    <?php endif; ?>
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="?page=<?= $p ?><?= $filterRead ? '&filter='.$filterRead : '' ?>" class="page-btn <?= $p===$page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?>
    <a href="?page=<?= $page+1 ?><?= $filterRead ? '&filter='.$filterRead : '' ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php adminFoot(); ?>
