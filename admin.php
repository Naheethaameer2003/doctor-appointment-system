<?php
require_once 'db.php';
requireLogin();

/* ── Toggle doctor availability ── */
if (isset($_GET["toggle_avail"])) {
  $curr = $pdo->query("SELECT setting_value FROM clinic_settings WHERE setting_key='doctor_available'")->fetchColumn();
  if ($curr === '1') {
    // Turning OFF — save today's date as leave_date
    $pdo->prepare("UPDATE clinic_settings SET setting_value='0', leave_date=? WHERE setting_key='doctor_available'")->execute([date('Y-m-d')]);
  } else {
    // Turning ON — clear leave_date
    $pdo->prepare("UPDATE clinic_settings SET setting_value='1', leave_date=NULL WHERE setting_key='doctor_available'")->execute([]);
  }
  header("Location: admin.php");
  exit;
}

/* ── Logout ── */
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

/* ── Actions: confirm / cancel appointment ── */
if (isset($_GET['action'], $_GET['id'])) {
  $aid = (int) $_GET['id'];
  if ($_GET['action'] === 'confirm') {
    $pdo->prepare("UPDATE appointments SET status='confirmed' WHERE id=?")->execute([$aid]);
  } elseif ($_GET['action'] === 'cancel') {
    $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?")->execute([$aid]);
  } elseif ($_GET['action'] === 'delete') {
    $pdo->prepare("DELETE FROM appointments WHERE id=?")->execute([$aid]);
  }
  header('Location: admin.php');
  exit;
}

/* ── Mark contact message as read ── */
if (isset($_GET['read_msg'])) {
  $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int) $_GET['read_msg']]);
  header('Location: admin.php#messages');
  exit;
}

/* ── Doctor availability with auto reset ── */
$avail_row = $pdo->query("SELECT setting_value, leave_date FROM clinic_settings WHERE setting_key='doctor_available'")->fetch();
$is_available = true;
if ($avail_row) {
  if ($avail_row['setting_value'] === '0') {
    // Auto reset if leave_date was yesterday or earlier
    if ($avail_row['leave_date'] && $avail_row['leave_date'] < date('Y-m-d')) {
      $pdo->prepare("UPDATE clinic_settings SET setting_value='1', leave_date=NULL WHERE setting_key='doctor_available'")->execute([]);
      $is_available = true;
    } else {
      $is_available = false;
    }
  }
}

/* ── Sunday auto closed ── */
$is_sunday = (date('N') == 7);
$effective_available = $is_available && !$is_sunday;


/* ── KPI Stats ── */
$today = date('Y-m-d');
$total_today = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appt_date='$today'")->fetchColumn();
$total_confirmed = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn();
$total_pending = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$total_patients = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$unread_msgs = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();

/* ── Filter ── */
$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'pending')
  $where = "WHERE status='pending'";
if ($filter === 'confirmed')
  $where = "WHERE status='confirmed'";
if ($filter === 'today')
  $where = "WHERE appt_date='$today'";

$appointments = $pdo->query("SELECT * FROM appointments $where ORDER BY appt_date ASC, appt_time ASC")->fetchAll();

/* ── Contact messages ── */
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10")->fetchAll();

$admin_name = $_SESSION['admin_name'] ?? 'Dr. Zamzameer';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Dr. Zamzameer</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: "Outfit", sans-serif;
    }

    .font-display {
      font-family: "Playfair Display", serif;
    }

    .sidebar {
      width: 230px;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      z-index: 100;
      overflow-y: auto;
      transition: transform 0.3s;
    }

    .main-area {
      margin-left: 230px;
    }

    .sb-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 10px;
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.86rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.18s;
      margin-bottom: 2px;
    }

    .sb-link:hover {
      background: rgba(255, 255, 255, 0.07);
      color: rgba(255, 255, 255, 0.85);
    }

    .sb-link.active {
      background: #0d6efd;
      color: white;
      font-weight: 600;
    }

    .kpi-card {
      border-radius: 12px;
      padding: 20px;
      background: #fff;
      border: 1px solid #eee;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .badge-status-confirmed {
      background: rgba(25, 135, 84, 0.1);
      color: #198754;
    }

    .badge-status-pending {
      background: rgba(255, 193, 7, 0.2);
      color: #856404;
    }

    .badge-status-cancelled {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    @media(max-width:991.98px) {
      .sidebar {
        transform: translateX(-100%)
      }

      .sidebar.open {
        transform: translateX(0)
      }

      .main-area {
        margin-left: 0
      }
    }
  </style>
</head>

<body class="bg-light">

  <!-- SIDEBAR -->
  <div class="sidebar bg-dark d-flex flex-column" id="sidebar">
    <div class="p-3 border-bottom border-secondary">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center text-white"
          style="width:36px;height:36px">🩺</div>
        <span class="font-display fw-bold text-white">Dr. <span class="text-primary">Zamzameer</span></span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="bg-primary bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
          style="width:38px;height:38px">👨‍⚕️</div>
        <div>
          <div class="text-white fw-semibold small"><?= htmlspecialchars($admin_name) ?></div>
          <div class="text-white-50" style="font-size:0.72rem">General Physician</div>
        </div>
        <span class="ms-auto rounded-circle bg-success" style="width:8px;height:8px"></span>
      </div>
    </div>
    <nav class="p-2 flex-grow-1">
      <div class="text-white-50 px-2 mb-2 mt-3"
        style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Menu</div>
      <a class="sb-link active" href="admin.php">📊 Dashboard</a>
      <a class="sb-link" href="admin.php?filter=all">📅 Appointments
        <span class="ms-auto badge bg-danger rounded-2"><?= $total_patients ?></span></a>
      <a class="sb-link" href="admin.php?filter=today">📆 Today
        <span class="ms-auto badge bg-primary rounded-2"><?= $total_today ?></span></a>
      <a class="sb-link" href="admin.php?filter=pending">⏳ Pending
        <span class="ms-auto badge bg-warning text-dark rounded-2"><?= $total_pending ?></span></a>
      <a class="sb-link" href="#messages" onclick="showMessages()">📨 Messages
        <?php if ($unread_msgs > 0): ?>
          <span class="ms-auto badge bg-danger rounded-2"><?= $unread_msgs ?></span>
        <?php endif; ?>
      </a>
      <div class="text-white-50 px-2 mb-2 mt-3"
        style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Settings</div>
      <a class="sb-link" href="index.php" target="_blank">🌐 View Site</a>
      <a class="sb-link" href="appointment.php" target="_blank">➕ New Booking</a>
    </nav>
    <div class="p-2 border-top border-secondary">
      <a class="sb-link" href="admin.php?logout=1" style="color:rgba(255,90,95,0.7)!important">🚪 Logout</a>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main-area d-flex flex-column min-vh-100">
    <!-- TOPBAR -->
    <div class="bg-white border-bottom px-4 d-flex align-items-center justify-content-between sticky-top"
      style="height:64px">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border-0 d-lg-none fs-5"
          onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div>
          <div class="font-display fw-bold">Dashboard</div>
          <div class="text-muted small">Welcome back, <?= htmlspecialchars($admin_name) ?> 👋 &nbsp;<span
              id="today-date"></span></div>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if ($unread_msgs > 0): ?>
          <a href="#messages" class="btn btn-light border position-relative rounded-3 p-2 text-decoration-none">
            🔔<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:0.65rem"><?= $unread_msgs ?></span>
          </a>
        <?php endif; ?>
        <?php if ($is_sunday): ?>
          <span class="btn btn-secondary rounded-pill fw-semibold small px-3 disabled" title="Sunday — Auto Closed">
            🔴 Closed (Sunday)
          </span>
        <?php else: ?>
          <a href="admin.php?toggle_avail=1"
            class="btn rounded-pill fw-semibold small px-3 <?php echo $is_available ? 'btn-success' : 'btn-danger'; ?>"
            onclick="return confirm('Toggle doctor availability?')">
            <?php echo $is_available ? '🟢 Available' : '🔴 Not Available'; ?>
          </a>
        <?php endif; ?>
        <button class="btn btn-primary rounded-pill fw-semibold small px-3" onclick="location.href='appointment.php'">+
          New Booking</button>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="p-4">

      <!-- KPI ROW -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
          <div class="kpi-card d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-3 p-3 fs-4">📅</div>
            <div>
              <div class="font-display fw-bold fs-4"><?= $total_today ?></div>
              <div class="text-muted small">Today's Appointments</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="kpi-card d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-3 p-3 fs-4">✅</div>
            <div>
              <div class="font-display fw-bold fs-4"><?= $total_confirmed ?></div>
              <div class="text-muted small">Confirmed</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="kpi-card d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-3 p-3 fs-4">⏳</div>
            <div>
              <div class="font-display fw-bold fs-4"><?= $total_pending ?></div>
              <div class="text-muted small">Pending</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="kpi-card d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-3 p-3 fs-4">👥</div>
            <div>
              <div class="font-display fw-bold fs-4"><?= $total_patients ?></div>
              <div class="text-muted small">Total Patients</div>
            </div>
          </div>
        </div>
      </div>

      <!-- FILTER TABS + TABLE -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <h6 class="font-display fw-bold mb-0">📋 Appointment List</h6>
          <div class="btn-group btn-group-sm">
            <a href="admin.php?filter=all"
              class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
            <a href="admin.php?filter=today"
              class="btn <?= $filter === 'today' ? 'btn-primary' : 'btn-outline-secondary' ?>">Today</a>
            <a href="admin.php?filter=pending"
              class="btn <?= $filter === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
            <a href="admin.php?filter=confirmed"
              class="btn <?= $filter === 'confirmed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Confirmed</a>
          </div>
        </div>

        <?php if (empty($appointments)): ?>
          <div class="text-center py-5 text-muted">
            <div class="fs-1 mb-2">📭</div>
            <p class="mb-0">No appointments found.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle small">
              <thead class="table-light">
                <tr>
                  <th>Ref</th>
                  <th>Patient</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Visit Type</th>
                  <th>Phone</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $a): ?>
                  <tr>
                    <td><span class="badge bg-light text-dark border">#<?= htmlspecialchars($a['ref_number']) ?></span></td>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($a['name']) ?></div>
                      <div class="text-muted" style="font-size:0.71rem"><?= htmlspecialchars($a['email']) ?></div>
                    </td>
                    <td><?= date('d M Y', strtotime($a['appt_date'])) ?></td>
                    <td><?= htmlspecialchars($a['appt_time']) ?></td>
                    <td><?= htmlspecialchars($a['visit_type']) ?></td>
                    <td><?= htmlspecialchars($a['phone']) ?></td>
                    <td>
                      <?php
                      $sc = ['confirmed' => 'badge-status-confirmed', 'pending' => 'badge-status-pending', 'cancelled' => 'badge-status-cancelled'];
                      $sc_cls = $sc[$a['status']] ?? 'bg-secondary';
                      ?>
                      <span class="badge <?= $sc_cls ?> rounded-pill px-3"><?= ucfirst($a['status']) ?></span>
                    </td>
                    <td>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light rounded-2" title="View" onclick="viewAppt(
                          '<?= addslashes(htmlspecialchars($a['name'])) ?>',
                          '<?= date('d M Y', strtotime($a['appt_date'])) ?>',
                          '<?= addslashes($a['appt_time']) ?>',
                          '<?= addslashes(htmlspecialchars($a['visit_type'])) ?>',
                          '<?= addslashes($a['status']) ?>',
                          '<?= addslashes(htmlspecialchars($a['phone'])) ?>',
                          '<?= addslashes(htmlspecialchars($a['email'])) ?>',
                          '<?= addslashes(htmlspecialchars($a['symptoms'] ?? '')) ?>'
                        )">👁</button>
                        <?php if ($a['status'] !== 'confirmed'): ?>
                          <a href="admin.php?action=confirm&id=<?= $a['id'] ?>&filter=<?= $filter ?>"
                            class="btn btn-sm btn-success rounded-2" title="Confirm"
                            onclick="return confirm('Confirm this appointment?')">✅</a>
                        <?php endif; ?>
                        <?php if ($a['status'] !== 'cancelled'): ?>
                          <a href="admin.php?action=cancel&id=<?= $a['id'] ?>&filter=<?= $filter ?>"
                            class="btn btn-sm btn-warning rounded-2" title="Cancel" onclick="return cancelAndWhatsApp(
     '<?= addslashes($a['phone']) ?>',
     '<?= addslashes($a['ref_number']) ?>',
     '<?= date('d M Y', strtotime($a['appt_date'])) ?>',
     '<?= addslashes($a['appt_time']) ?>',
     '<?= addslashes($a['name']) ?>'
   )">❌</a>
                        <?php endif; ?>
                        <a href="admin.php?action=delete&id=<?= $a['id'] ?>&filter=<?= $filter ?>"
                          class="btn btn-sm btn-danger rounded-2" title="Delete"
                          onclick="return confirm('Delete this record permanently?')">🗑</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CONTACT MESSAGES -->
      <div class="card border-0 shadow-sm rounded-4 p-4 d-none" id="messages">
        <h6 class="font-display fw-bold mb-3">📨 Contact Messages
          <?php if ($unread_msgs > 0): ?>
            <span class="badge bg-danger ms-2"><?= $unread_msgs ?> new</span>
          <?php endif; ?>
        </h6>
        <?php if (empty($messages)): ?>
          <div class="text-center py-4 text-muted">
            <div class="fs-1 mb-2">📭</div>
            <p class="mb-0">No messages yet.</p>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($messages as $m): ?>
              <div
                class="border rounded-3 p-3 <?= $m['is_read'] ? 'bg-white' : 'bg-warning bg-opacity-10 border-warning' ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="fw-semibold"><?= htmlspecialchars($m['name']) ?></span>
                    <span class="text-muted small ms-2"><?= htmlspecialchars($m['email']) ?></span>
                    <?php if (!$m['is_read']): ?>
                      <span class="badge bg-danger ms-2 small">New</span>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small"><?= date('d M Y', strtotime($m['created_at'])) ?></span>
                    <?php if (!$m['is_read']): ?>
                      <a href="admin.php?read_msg=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-2"
                        title="Mark read">✓</a>
                    <?php endif; ?>
                    <a href="admin.php?delete_msg=<?= $m['id'] ?>" class="btn btn-sm btn-danger rounded-2"
                      onclick="return confirm('Delete this message?')">🗑</a>
                  </div>
                </div>
                <?php if (!empty($m['subject'])): ?>
                  <div class="small fw-semibold mt-1">📌 <?= htmlspecialchars($m['subject']) ?></div>
                <?php endif; ?>
                <div class="text-secondary small mt-1"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
                <?php
                $wa_num = preg_replace('/[\s\-\(\)]/', '', $m['phone']);
                if (str_starts_with($wa_num, '0')) {
                  $wa_num = '94' . substr($wa_num, 1);
                }
                $wa_num = ltrim($wa_num, '+');
                ?>
                <a href="https://wa.me/<?= $wa_num ?>" target="_blank"
                  class="btn btn-sm btn-success rounded-pill px-3 mt-2">
                  <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /p-4 -->
  </div><!-- /main-area -->

  <!-- VIEW MODAL -->
  <div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="font-display fw-bold mb-0">👁 Appointment Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div id="modalBody" class="d-flex flex-column gap-2 small"></div>
        <button class="btn btn-secondary rounded-3 mt-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('today-date').textContent = new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

    function viewAppt(name, date, time, type, status, phone, email, symptoms) {
      const sc = { 'confirmed': 'text-success', 'pending': 'text-warning', 'cancelled': 'text-danger' };
      document.getElementById('modalBody').innerHTML = `
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Patient</span><strong>${name}</strong></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Date</span><span>${date}</span></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Time</span><span>${time}</span></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Visit Type</span><span>${type}</span></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Phone</span><span>${phone}</span></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Email</span><span>${email}</span></div>
          <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Status</span><strong class="${sc[status] || ''}">${status.charAt(0).toUpperCase() + status.slice(1)}</strong></div>
          ${symptoms ? `<div class="d-flex justify-content-between"><span class="text-muted">Symptoms</span><span class="text-end" style="max-width:55%">${symptoms}</span></div>` : ''}
        `;
      new bootstrap.Modal(document.getElementById('viewModal')).show();
    }


    function cancelAndWhatsApp(phone, ref, date, time, name) {
      if (!confirm('Cancel this appointment?')) return false;

      // Sri Lanka number format: 07xxxxxxxx → 947xxxxxxxx
      let num = phone.replace(/\s+/g, '').replace(/^\+/, '');
      if (num.startsWith('0')) {
        num = '94' + num.substring(1);
      }

      const msg =
        `🩺 *Dr. Zamzameer Clinic*

Dear ${name},

Your appointment has been *cancelled* by the doctor.

📋 *Ref:* #${ref}
📅 *Date:* ${date}
⏰ *Time:* ${time}

To rebook, please call us:
📞 +94 77 345 0900

We apologize for the inconvenience.`;

      // Open WhatsApp in new tab, then proceed with cancel
      window.open('https://wa.me/' + num + '?text=' + encodeURIComponent(msg), '_blank');

      return true; // proceeds to admin.php?action=cancel
    }

    function showMessages() {
      const el = document.getElementById("messages");
      el.classList.remove("d-none");
      setTimeout(() => el.scrollIntoView({ behavior: "smooth" }), 50);
    }
    if (window.location.hash === "#messages") showMessages();


  </script>
</body>

</html>