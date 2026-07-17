<?php
/**
 * admin/contacts.php — View and delete contact form submissions.
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';
requireAdmin();

$adminTitle  = 'Contact Messages';
$adminActive = 'contacts';

// ── Handle DELETE ─────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    db()->prepare('DELETE FROM contacts WHERE id = ?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'Message deleted.');
    redirect(SITE_URL . '/admin/contacts.php');
}

// ── Handle VIEW (single message) ─────────────────────────────
$viewMsg = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $stmt = db()->prepare('SELECT * FROM contacts WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['view']]);
    $viewMsg = $stmt->fetch();
}

$messages = db()->query(
    'SELECT * FROM contacts ORDER BY created_at DESC'
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<?php if ($viewMsg): ?>
  <!-- Single message view -->
  <div class="admin-card" style="max-width:660px;margin-bottom:24px;">
    <div class="card-head">
      <h3>Message from <?= h($viewMsg['name']) ?></h3>
      <a href="contacts.php" class="btn btn-sm btn-dark">← Back</a>
    </div>
    <div class="card-body">
      <table style="width:100%;border-collapse:collapse;">
        <?php
        $rows = [
          'From'    => h($viewMsg['name']),
          'Email'   => '<a href="mailto:'.h($viewMsg['email']).'">'.h($viewMsg['email']).'</a>',
          'Subject' => h($viewMsg['subject']),
          'Order'   => h($viewMsg['order_ref'] ?: '—'),
          'Sent'    => date('d M Y, H:i', strtotime($viewMsg['created_at'])),
        ];
        foreach ($rows as $label => $val): ?>
          <tr>
            <td style="padding:10px 0;font-size:0.82rem;font-weight:700;color:var(--text-light);width:90px;vertical-align:top;"><?= $label ?></td>
            <td style="padding:10px 0;font-size:0.9rem;color:var(--text-dark);"><?= $val ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <hr style="border:none;border-top:1px solid #e0e6ef;margin:16px 0;" />
      <p style="font-size:0.9rem;color:var(--text-dark);line-height:1.8;white-space:pre-wrap;"><?= h($viewMsg['message']) ?></p>
      <div style="margin-top:20px;display:flex;gap:10px;">
        <a href="mailto:<?= h($viewMsg['email']) ?>" class="btn btn-success">Reply by Email</a>
        <a href="contacts.php?delete=<?= (int)$viewMsg['id'] ?>" class="btn btn-danger"
           onclick="return confirm('Delete this message?')">Delete</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Messages table -->
<div class="admin-card">
  <div class="card-head">
    <h3>All Messages</h3>
    <span style="font-size:0.82rem;color:var(--text-light);"><?= count($messages) ?> total</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Subject</th>
          <th>Order Ref</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-light);">No messages yet.</td></tr>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <tr>
              <td style="font-weight:600;"><?= h($m['name']) ?></td>
              <td><a href="mailto:<?= h($m['email']) ?>" style="color:var(--primary);"><?= h($m['email']) ?></a></td>
              <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= h($m['subject']) ?></td>
              <td><?= h($m['order_ref'] ?: '—') ?></td>
              <td style="white-space:nowrap;color:var(--text-light);font-size:0.78rem;">
                <?= date('d M Y', strtotime($m['created_at'])) ?>
              </td>
              <td>
                <a href="contacts.php?view=<?= (int)$m['id'] ?>" class="btn btn-sm btn-success">View</a>
                <a href="contacts.php?delete=<?= (int)$m['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this message?')">Del</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
