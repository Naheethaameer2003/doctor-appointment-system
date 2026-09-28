<?php
/* ══════════════════════════════════════════════════════
   appointment.php — Dr. Zamzameer Appointment Booking
   Features:
     • DB-connected: saves to appointments table
     • Taken slots loaded from DB for selected date
     • Form validation
     • Appointment success modal
     • PDF Appointment Slip generation (browser-side via html2pdf.js)
   ══════════════════════════════════════════════════════ */

require_once 'db.php';

$booking_success  = false;
$ref_number       = '';
$errors           = [];
$name             = '';
$formatted_date   = '';
$appt_time        = '';
$visit_type       = 'General Consultation';

$all_slots   = ['05:00 PM','05:30 PM','06:00 PM','06:30 PM','07:00 PM','07:30 PM','08:00 PM','08:30 PM','09:00 PM','09:30 PM','10:00 PM'];
$picked_time = $_POST['appt_time'] ?? '';
$visit_types = ['General Consultation','Follow-up Visit','Health Checkup','Emergency'];

/* ── Load taken slots for selected date from DB ── */
$check_date  = $_POST['appt_date'] ?? date('Y-m-d');
$taken_stmt  = $pdo->prepare("SELECT appt_time FROM appointments WHERE appt_date = ? AND status != 'cancelled'");
$taken_stmt->execute([$check_date]);
$taken_slots = $taken_stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ── Collect & sanitise form fields ── */
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $age        = trim($_POST['age']        ?? '');
    $address    = trim($_POST['address']    ?? '');
    $appt_date  = trim($_POST['appt_date']  ?? '');
    $appt_time  = trim($_POST['appt_time']  ?? '');
    $visit_type = trim($_POST['visit_type'] ?? 'General Consultation');
    $symptoms   = trim($_POST['symptoms']   ?? '');

    /* ── Validate required fields ── */
    if (empty($name))      $errors[] = 'Full name is required.';
    if (empty($email))     $errors[] = 'Email is required.';
    if (empty($phone))     $errors[] = 'Phone number is required.';
    if (empty($appt_date)) $errors[] = 'Please pick a date.';
    if (empty($appt_time)) $errors[] = 'Please pick a time slot.';

    /* ── Check if slot is already taken ── */
    if (empty($errors) && in_array($appt_time, $taken_slots)) {
        $errors[] = 'Sorry, that time slot is already booked. Please choose another.';
    }

    if (empty($errors)) {
        /* ── Generate unique reference number ── */
        do {
            $ref_number = 'ZAM-' . rand(1000, 9999);
            $chk = $pdo->prepare("SELECT id FROM appointments WHERE ref_number = ?");
            $chk->execute([$ref_number]);
        } while ($chk->fetch());

        /* ── INSERT into database ── */
        $ins = $pdo->prepare("INSERT INTO appointments
            (ref_number, name, email, phone, age, address, appt_date, appt_time, visit_type, symptoms, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $ins->execute([$ref_number, $name, $email, $phone, $age, $address, $appt_date, $appt_time, $visit_type, $symptoms]);

        $booking_success = true;

        // ── Send confirmation email to patient (HTML) ──
        $formatted_date_email = date('d F Y', strtotime($appt_date));
        $to      = $email;
        $subject = "Appointment Confirmation - Dr. Zamzameer | Ref: #$ref_number";

        // HTML email body — includes appointment slip details + PDF download reminder
        $msg  = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        $msg .= 'body{font-family:Arial,sans-serif;background:#f4f6fb;margin:0;padding:0;}';
        $msg .= '.wrap{max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}';
        $msg .= '.header{background:#0d6efd;padding:30px;text-align:center;color:#fff;}';
        $msg .= '.header h1{margin:0;font-size:22px;}';
        $msg .= '.header p{margin:6px 0 0;opacity:0.85;font-size:13px;}';
        $msg .= '.badge{display:inline-block;background:rgba(255,255,255,0.2);color:#fff;padding:6px 20px;border-radius:30px;font-weight:700;font-size:15px;margin-top:12px;}';
        $msg .= '.body{padding:28px 32px;}';
        $msg .= '.row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:14px;}';
        $msg .= '.label{color:#888;}';
        $msg .= '.value{font-weight:600;color:#1a1a1a;}';
        $msg .= '.note{background:#fff8e1;border-left:4px solid #ffc107;padding:14px 16px;border-radius:6px;margin:20px 0;font-size:13px;color:#5a4000;}';
        $msg .= '.pdf-box{background:#e8f5e9;border:2px dashed #43a047;border-radius:8px;padding:16px;text-align:center;margin:20px 0;}';
        $msg .= '.pdf-box p{margin:0;font-size:13px;color:#2e7d32;font-weight:600;}';
        $msg .= '.footer{background:#f8f9fa;padding:16px;text-align:center;font-size:12px;color:#aaa;}';
        $msg .= '</style></head><body><div class="wrap">';

        // Header
        $msg .= '<div class="header"><h1>🩺 Appointment Confirmed!</h1>';
        $msg .= '<p>Dr. Zamzameer — General Physician</p>';
        $msg .= '<div class="badge">REF: #' . htmlspecialchars($ref_number) . '</div></div>';

        // Body
        $msg .= '<div class="body">';
        $msg .= '<p style="font-size:15px;">Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>';
        $msg .= '<p style="color:#555;font-size:14px;">Your appointment has been successfully booked. Please find your appointment details below.</p>';

        $msg .= '<div class="row"><span class="label">Patient Name</span><span class="value">' . htmlspecialchars($name) . '</span></div>';
        $msg .= '<div class="row"><span class="label">Reference No.</span><span class="value">#' . htmlspecialchars($ref_number) . '</span></div>';
        $msg .= '<div class="row"><span class="label">Doctor</span><span class="value">Dr. Zamzameer (General Physician)</span></div>';
        $msg .= '<div class="row"><span class="label">Appointment Date</span><span class="value">' . htmlspecialchars($formatted_date_email) . '</span></div>';
        $msg .= '<div class="row"><span class="label">Appointment Time</span><span class="value">' . htmlspecialchars($appt_time) . '</span></div>';
        $msg .= '<div class="row"><span class="label">Visit Type</span><span class="value">' . htmlspecialchars($visit_type) . '</span></div>';
        if (!empty($phone)) {
            $msg .= '<div class="row"><span class="label">Your Phone</span><span class="value">' . htmlspecialchars($phone) . '</span></div>';
        }

        // Clinic details
        $msg .= '<div style="margin-top:20px;background:#f0f6ff;border-radius:8px;padding:14px;">';
        $msg .= '<p style="margin:0 0 8px;font-weight:700;font-size:13px;color:#0d6efd;">📍 CLINIC DETAILS</p>';
        $msg .= '<p style="margin:0;font-size:13px;color:#444;">284, Hospital Road, SAMMANTHURAI-01, Eastern Province, Sri Lanka<br>';
        $msg .= '📞 +94 77 345 0900 &nbsp;|&nbsp; ⏰ Mon – Sat: 5:00 PM – 10:00 PM</p>';
        $msg .= '</div>';

        // PDF reminder box
        $msg .= '<div class="pdf-box">';
        $msg .= '<p>📄 Your PDF Appointment Slip is available on the booking confirmation page.<br>';
        $msg .= '<span style="font-weight:400;color:#388e3c;">Click "Download PDF Slip" after booking to save your slip.</span></p>';
        $msg .= '</div>';

        // Important notes
        $msg .= '<div class="note">⚠️ <strong>Important:</strong><br>';
        $msg .= '✔ Please arrive 10–15 minutes early<br>';
        $msg .= '✔ Bring this email or your PDF slip on the day<br>';
        $msg .= '✔ Carry previous medical records if any<br>';
        $msg .= '✔ To reschedule, call <strong>+94 77 345 0900</strong> at least 24 hours in advance</div>';

        $msg .= '<p style="font-size:13px;color:#888;">Thank you for choosing Dr. Zamzameer Clinic. We look forward to seeing you!</p>';
        $msg .= '</div>';

        // Footer
        $msg .= '<div class="footer">© 2026 Dr. Zamzameer Appointment System · 284 Hospital Road, SAMMANTHURAI-01</div>';
        $msg .= '</div></body></html>';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Dr. Zamzameer Clinic <noreply@drzamzameer.lk>\r\n";
        $headers .= "Reply-To: drzamzameer@clinic.lk\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        // mail() works on live server (cPanel/hosting). On XAMPP localhost it may not send.
        @mail($to, $subject, $msg, $headers);

        /* Format the date nicely for display & PDF */
        $formatted_date = date('d F Y', strtotime($appt_date));
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dr. Zamzameer — Book Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- html2pdf.js — client-side PDF generation (no server libs needed) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
      body { font-family: "Outfit", sans-serif; }
      .font-display { font-family: "Playfair Display", serif; }


      /* ══ PROFESSIONAL NAVBAR ══ */
      .nav-link.active-page { color:#0d6efd!important; font-weight:600; background:rgba(13,110,253,0.07); border-radius:8px; }
      .pro-nav { background:rgba(255,255,255,0.92)!important;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid rgba(0,0,0,0.07)!important;padding:0;transition:box-shadow 0.3s; }
      .pro-nav.scrolled { box-shadow:0 4px 24px rgba(0,0,0,0.10)!important; }
      .pro-nav .container { height:68px; }
      .nav-brand-icon { width:42px;height:42px;background:linear-gradient(135deg,#0d6efd,#0a58ca);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 4px 12px rgba(13,110,253,0.3);flex-shrink:0; }
      .pro-nav .nav-link { font-size:0.875rem;font-weight:500;color:#555!important;padding:6px 14px!important;border-radius:8px;position:relative;transition:color 0.2s,background 0.2s; }
      .pro-nav .nav-link::after { content:'';position:absolute;bottom:2px;left:50%;right:50%;height:2px;background:#0d6efd;border-radius:2px;transition:left 0.25s,right 0.25s; }
      .pro-nav .nav-link:hover { color:#0d6efd!important;background:rgba(13,110,253,0.06); }
      .pro-nav .nav-link:hover::after,.pro-nav .nav-link.active-page::after { left:14px;right:14px; }
      .pro-nav .nav-link.active-page { color:#0d6efd!important;font-weight:600;background:rgba(13,110,253,0.07); }
      .nav-book-btn { background:linear-gradient(135deg,#0d6efd,#0a58ca);border:none;border-radius:50px;padding:9px 22px;font-size:0.875rem;font-weight:600;color:#fff!important;box-shadow:0 4px 14px rgba(13,110,253,0.35);transition:transform 0.18s,box-shadow 0.18s;text-decoration:none;display:inline-flex;align-items:center;gap:6px; }
      .nav-book-btn:hover { transform:translateY(-2px);box-shadow:0 7px 20px rgba(13,110,253,0.45);color:#fff!important; }
      .pro-toggler { border:1.5px solid rgba(0,0,0,0.12)!important;border-radius:10px!important;padding:7px 10px!important;transition:background 0.2s; }
      .pro-toggler:hover { background:rgba(13,110,253,0.06); }
      @media (max-width:991.98px) {
        .pro-nav .navbar-collapse { background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);padding:16px;margin-top:10px;border:1px solid rgba(0,0,0,0.06); }
        .pro-nav .nav-link::after { display:none; }
        .pro-nav .nav-link { padding:10px 14px!important;border-radius:10px; }
        .nav-book-btn { width:100%;justify-content:center;margin-top:8px; }
      }

      /* Time slot buttons */
      .slot { cursor:pointer; transition:all 0.15s; }
      .slot:not(.taken):hover { border-color:#0d6efd!important; color:#0d6efd!important; }
      .slot.picked { border-color:#0d6efd!important; background:rgba(13,110,253,0.08)!important; color:#0d6efd!important; font-weight:600; }
      .slot.taken  { opacity:0.4; cursor:not-allowed; text-decoration:line-through; }

      /* Success modal pop animation */
      @keyframes pop { from{opacity:0;transform:scale(0.8);}to{opacity:1;transform:scale(1);} }
      .pop-anim { animation:pop 0.35s cubic-bezier(0.34,1.56,0.64,1); }

      /* ──────────── PDF Slip Styles ────────────
         This div is hidden on screen; html2pdf renders it to PDF.
         Keep it simple — avoid flex/grid for better PDF rendering. */
      #pdfSlip {
        display: none;               /* Hidden on page */
        width: 700px;
        padding: 40px;
        font-family: "Outfit", sans-serif;
        color: #1a1a1a;
        background: #fff;
      }
      #pdfSlip .slip-header { text-align: center; border-bottom: 3px solid #0d6efd; padding-bottom: 20px; margin-bottom: 24px; }
      #pdfSlip .slip-logo   { font-size: 28px; font-weight: 800; color: #0d6efd; margin-bottom: 4px; }
      #pdfSlip .slip-sub    { font-size: 13px; color: #666; }
      #pdfSlip .slip-badge  { display: inline-block; background: #e8f0fe; color: #0d6efd; padding: 6px 20px; border-radius: 30px; font-weight: 700; font-size: 15px; margin-bottom: 20px; }
      #pdfSlip .slip-row    { display: table; width: 100%; border-bottom: 1px solid #eee; padding: 10px 0; }
      #pdfSlip .slip-label  { display: table-cell; width: 40%; color: #888; font-size: 13px; }
      #pdfSlip .slip-value  { display: table-cell; font-weight: 600; font-size: 14px; }
      #pdfSlip .slip-note   { margin-top: 24px; background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 16px; font-size: 13px; border-radius: 4px; }
      #pdfSlip .slip-footer { margin-top: 32px; text-align: center; font-size: 12px; color: #aaa; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
  </head>
  <body class="bg-light">

    <!-- ═══════════ NAVBAR ═══════════ -->
    <nav class="navbar navbar-expand-lg sticky-top pro-nav" id="mainNav">
      <div class="container d-flex align-items-center">
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none me-4" href="index.php">
          <div class="nav-brand-icon">🩺</div>
          <div>
            <span class="fw-bold font-display" style="font-size:1.1rem">Dr. <span class="text-primary">Zamzameer</span></span>
            <div class="text-muted" style="font-size:0.68rem;line-height:1;margin-top:1px">General Physician</div>
          </div>
        </a>
        <button class="navbar-toggler pro-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav mx-auto gap-lg-1 align-items-lg-center">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
             <!-- <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li> -->
            <li class="nav-item"><a class="nav-link" href="index.php#location">Location</a></li>
            <li class="nav-item"><a class="nav-link" href="how-it-works.php">How It Works</a></li>
            <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
          </ul>
          <a class="nav-book-btn active-page" href="appointment.php">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Book Appointment
          </a>
        </div>
      </div>
    </nav>

    <!-- PROGRESS BAR  -->
    <div class="bg-white border-bottom py-3">
      <div class="container">
        <div class="d-flex align-items-center justify-content-center gap-2" style="max-width:500px;margin:0 auto">
          <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold small" style="width:32px;height:32px">✓</div>
          <span class="small text-muted">Your Info</span>
          <div class="flex-grow-1 border-top border-success" style="height:2px;max-width:60px"></div>
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small" style="width:32px;height:32px">2</div>
          <span class="small fw-semibold">Schedule</span>
          <div class="flex-grow-1 border-top" style="height:2px;max-width:60px"></div>
          <div class="rounded-circle bg-secondary bg-opacity-25 text-secondary d-flex align-items-center justify-content-center fw-bold small" style="width:32px;height:32px">3</div>
          <span class="small text-muted">Confirm</span>
        </div>
      </div>
    </div>

    <div class="container my-4" style="max-width:860px">
      <h1 class="font-display fw-bold fs-3 mb-1">📅 Book an Appointment</h1>
      <p class="text-secondary small mb-4">Fill in your details to schedule a visit with Dr. Zamzameer</p>

      <!-- Validation errors -->
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger small">
        <strong>⚠️ Please fix:</strong>
        <ul class="mb-0 mt-1"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>

      <!--  BOOKING FORM  -->
      <form method="POST" action="appointment.php">

        <!-- Personal Info -->
        <div class="card border rounded-4 shadow-sm p-4 mb-3">
          <h6 class="fw-bold mb-3">👤 Personal Information</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Full Name *</label>
              <input type="text" class="form-control rounded-3" name="name" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"/>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Email Address *</label>
              <input type="email" class="form-control rounded-3" name="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Phone Number *</label>
              <input type="tel" class="form-control rounded-3" name="phone" placeholder="+94 77 345 0900" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Age</label>
              <input type="number" class="form-control rounded-3" name="age" placeholder="e.g. 28" min="1" max="120" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"/>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small">Address</label>
              <input type="text" class="form-control rounded-3" name="address" placeholder="Your home address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"/>
            </div>
          </div>
        </div>

        <!-- Schedule -->
        <div class="card border rounded-4 shadow-sm p-4 mb-3">
          <h6 class="fw-bold mb-3">📆 Appointment Schedule</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Preferred Date *</label>
              <input type="date" class="form-control rounded-3" name="appt_date" id="apptDate" required
                     min="<?= date('Y-m-d') ?>"
                     value="<?= htmlspecialchars($_POST['appt_date'] ?? '') ?>" onchange="fetchSlots(this.value)"/>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Visit Type</label>
              <select class="form-select rounded-3" name="visit_type" id="visitType" onchange="updateSummaryType()">
    <?php foreach($visit_types as $vt): ?>
    <option value="<?= htmlspecialchars($vt) ?>" <?= (($_POST['visit_type'] ?? 'General Consultation') === $vt) ? 'selected' : '' ?>><?= $vt ?></option>
    <?php endforeach; ?>
</select>
            </div>
          </div>
          <!-- Hidden field carries the picked time slot to PHP -->
          <input type="hidden" name="appt_time" id="apptTimeInput" value="<?= htmlspecialchars($picked_time) ?>"/>
          <label class="form-label fw-semibold small mb-2">Choose Time Slot *</label>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach($all_slots as $slot):
              $taken  = in_array($slot, $taken_slots);
              $picked = ($picked_time === $slot) && !$taken;
              $cls    = 'btn btn-outline-secondary rounded-pill small slot' . ($taken ? ' taken' : '') . ($picked ? ' picked' : '');
              $onclick = $taken ? '' : "onclick=\"pickSlot(this,'".addslashes($slot)."')\"";
            ?>
            <button type="button" class="<?= $cls ?>" <?= $onclick ?>><?= htmlspecialchars($slot) ?></button>
            <?php endforeach; ?>
          </div>
          <p class="text-muted mt-2 mb-0" style="font-size:0.75rem">⛔ Strikethrough = Already booked</p>
        </div>

        <!-- Symptoms -->
        <div class="card border rounded-4 shadow-sm p-4 mb-3">
          <h6 class="fw-bold mb-3">🩺 Reason for Visit</h6>
          <textarea class="form-control rounded-3" name="symptoms" rows="4" placeholder="Briefly describe your symptoms or reason for visit..."><?= htmlspecialchars($_POST['symptoms'] ?? '') ?></textarea>
        </div>

        <!-- Summary panel -->
        <div class="bg-dark text-white rounded-4 p-4 mb-3">
          <p class="text-white-50 small fw-bold text-uppercase mb-3">📋 Appointment Summary</p>
          <div class="d-flex justify-content-between border-bottom border-secondary py-2"><span class="text-white-50 small">Doctor</span><span class="fw-semibold small">Dr. Zamzameer</span></div>
          <div class="d-flex justify-content-between border-bottom border-secondary py-2"><span class="text-white-50 small">Date</span>
            <span class="fw-semibold small" id="s-date"><?= !empty($_POST['appt_date']) ? date('d M Y', strtotime($_POST['appt_date'])) : '—' ?></span></div>
          <div class="d-flex justify-content-between border-bottom border-secondary py-2"><span class="text-white-50 small">Time</span><span class="fw-semibold small" id="s-time"><?= $picked_time ? htmlspecialchars($picked_time) : '—' ?></span></div>
          <div class="d-flex justify-content-between py-2"><span class="text-white-50 small">Visit Type</span><span class="fw-semibold small" id="s-type"><?= htmlspecialchars($_POST['visit_type'] ?? '') ?></span></div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow">✅ Confirm Appointment</button>
      </form>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SUCCESS MODAL — shown by PHP after a valid form submission
         Contains: reference number, summary, and PDF download button
         ═══════════════════════════════════════════════════════════ -->
    <?php if ($booking_success): ?>
    <div class="modal fade show d-block" id="successModal" tabindex="-1" style="background:rgba(0,0,0,0.65)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-4 text-center pop-anim">
          <div class="fs-1 mb-2">🎉</div>
          <h4 class="font-display fw-bold mb-2">Appointment Booked!</h4>
          <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-3 fw-bold mb-3">
            REF: #<?= htmlspecialchars($ref_number) ?>
          </span>
          <p class="text-secondary small mb-4">
            Your appointment with <strong>Dr. Zamzameer</strong> has been successfully booked.<br/>
            Please arrive <strong>10 minutes early</strong>.
          </p>

          <!-- Appointment details inside modal -->
          <div class="bg-light rounded-3 p-3 text-start mb-4 small">
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Patient</span><span class="fw-semibold"><?= htmlspecialchars($name) ?></span></div>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Date</span><span class="fw-semibold"><?= htmlspecialchars($formatted_date) ?></span></div>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Time</span><span class="fw-semibold"><?= htmlspecialchars($appt_time) ?></span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Visit Type</span><span class="fw-semibold"><?= htmlspecialchars($visit_type) ?></span></div>
          </div>

          <div class="d-flex gap-2 justify-content-center">
            <!-- Download PDF button — triggers generatePDF() JavaScript function below -->
            <button class="btn btn-success rounded-pill px-4 fw-bold" onclick="generatePDF()">
              📄 Download PDF Slip
            </button>
             <button class="btn btn-success rounded-pill px-4 fw-bold" style="background:#25D366;border-color:#25D366;"
              onclick="shareWhatsApp()">
              📲 Share on WhatsApp
            </button>
            <button class="btn btn-primary rounded-pill px-4 fw-bold" onclick="location.href='index.php'">
              🏠 Back to Home
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         PDF APPOINTMENT SLIP TEMPLATE (hidden on screen)
         html2pdf.js renders this div into a downloadable PDF.
         Edit the content here to customise what appears in the PDF.
         ═══════════════════════════════════════════════════════════ -->
    <div id="pdfSlip">
      <!-- Header -->
      <div class="slip-header">
        <div class="slip-logo">🩺 Dr. Zamzameer</div>
        <div class="slip-sub">General Physician · 284, Hospital Road, SAMMANTHURAI-01</div>
        <div class="slip-sub">Phone: +94 77 345 0900 · drzamzameer@clinic.lk</div>
      </div>

      <!-- Title -->
      <div style="text-align:center;margin-bottom:20px">
        <div style="font-size:20px;font-weight:800;color:#1a1a1a;margin-bottom:8px">APPOINTMENT CONFIRMATION SLIP</div>
        <div class="slip-badge">REF: #<?= htmlspecialchars($ref_number) ?></div>
      </div>

      <!-- Patient & Appointment details -->
      <div class="slip-row"><span class="slip-label">Patient Name</span><span class="slip-value"><?= htmlspecialchars($name) ?></span></div>
      <div class="slip-row"><span class="slip-label">Phone</span><span class="slip-value"><?= htmlspecialchars($phone) ?></span></div>
      <div class="slip-row"><span class="slip-label">Email</span><span class="slip-value"><?= htmlspecialchars($email) ?></span></div>
      <?php if (!empty($age)): ?>
      <div class="slip-row"><span class="slip-label">Age</span><span class="slip-value"><?= htmlspecialchars($age) ?> years</span></div>
      <?php endif; ?>
      <?php if (!empty($address)): ?>
      <div class="slip-row"><span class="slip-label">Address</span><span class="slip-value"><?= htmlspecialchars($address) ?></span></div>
      <?php endif; ?>
      <div class="slip-row"><span class="slip-label">Appointment Date</span><span class="slip-value"><?= htmlspecialchars($formatted_date) ?></span></div>
      <div class="slip-row"><span class="slip-label">Appointment Time</span><span class="slip-value"><?= htmlspecialchars($appt_time) ?></span></div>
      <div class="slip-row"><span class="slip-label">Visit Type</span><span class="slip-value"><?= htmlspecialchars($visit_type) ?></span></div>
      <div class="slip-row"><span class="slip-label">Doctor</span><span class="slip-value">Dr. Zamzameer — General Physician</span></div>
      <?php if (!empty($symptoms)): ?>
      <div class="slip-row"><span class="slip-label">Reason / Symptoms</span><span class="slip-value"><?= htmlspecialchars($symptoms) ?></span></div>
      <?php endif; ?>

      <!-- Important note -->
      <div class="slip-note">
        ⚠️ <strong>Please bring this slip on the day of your appointment.</strong><br/>
        Arrive at least 10 minutes before your scheduled time. For rescheduling, contact us 24 hours in advance.
      </div>

      <!-- Footer -->
      <div class="slip-footer">
        Generated on <?= date('d F Y, h:i A') ?> · Dr. Zamzameer Clinic · 284 Hospital Road, SAMMANTHURAI-01
      </div>
    </div>
    <?php endif; ?>

    <!-- ═════════════════════════════ FOOTER ═════════════════════════════ -->
    <footer class="bg-dark border-top border-secondary text-center text-secondary small py-4 mt-4 mb-6">
      <p class="mb-0">© 2026 <span class="text-primary fw-semibold">Dr. Zamzameer</span> — Appointment Booking System. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      /* ── Time slot picker ── */
      let pickedTime = '<?= addslashes($picked_time) ?>';

      function pickSlot(el, time) {
        if (el.classList.contains('taken')) return;
        /* ── Date select பண்ணாம slot pick பண்ண allow இல்லை ── */
        if (!document.getElementById('apptDate').value) {
          alert('Please select a date first!');
          return;
        }
        document.querySelectorAll('.slot').forEach(s => s.classList.remove('picked'));
        el.classList.add('picked');
        pickedTime = time;
        document.getElementById('apptTimeInput').value = time;
        document.getElementById('s-time').textContent  = time;
      }

      /* ── AJAX: fetch taken slots for selected date and update buttons ── */
      function fetchSlots(dateVal) {
        /* Update summary date label */
        document.getElementById('s-date').textContent = dateVal
          ? new Date(dateVal).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'})
          : '—';

        if (!dateVal) return;

        fetch('get_slots.php?date=' + encodeURIComponent(dateVal))
          .then(function(res) { return res.json(); })
          .then(function(takenSlots) {
            /* Reset picked slot if it becomes taken on new date */
            if (takenSlots.includes(pickedTime)) {
              pickedTime = '';
              document.getElementById('apptTimeInput').value = '';
              document.getElementById('s-time').textContent = '—';
            }

            /* Update every slot button */
            document.querySelectorAll('.slot').forEach(function(btn) {
              const slotTime = btn.textContent.trim();
              const isTaken  = takenSlots.includes(slotTime);

              if (isTaken) {
                /* Disable and style as taken */
                btn.classList.add('taken');
                btn.classList.remove('picked');
                btn.setAttribute('disabled', true);
                btn.onclick = null;
              } else {
                /* Re-enable available slot */
                btn.classList.remove('taken');
                btn.removeAttribute('disabled');
                btn.onclick = function() { pickSlot(btn, slotTime); };

                /* Re-apply picked style if this was the chosen slot */
                if (slotTime === pickedTime) {
                  btn.classList.add('picked');
                }
              }
            });
          })
          .catch(function(err) {
            console.error('Could not load slots:', err);
          });
      }

      /* ── Run on page load if a date is already set (e.g. after form error) ── */
      (function() {
        const d = document.getElementById('apptDate').value;
        if (d) fetchSlots(d);
        /* ── Visit Type summary sync on page load ── */
        document.getElementById('s-type').textContent =
          document.getElementById('visitType').value;
      })();

      function updateSummaryType() {
        document.getElementById('s-type').textContent = document.getElementById('visitType').value;
      }

      /* ── PDF Generation ──
         Uses html2pdf.js to convert the hidden #pdfSlip div into a PDF.
         Settings: A4 page, quality 2 for sharp text, margin 10mm on all sides.
         The filename includes the reference number for easy filing. */
      function generatePDF() {
        const element  = document.getElementById('pdfSlip');
        const refNum   = '<?= addslashes($ref_number) ?>';
        const filename = 'Appointment_' + refNum + '.pdf';

        /* Make the slip visible temporarily for html2pdf to capture it */
        element.style.display = 'block';

        const opt = {
          margin:       10,                   /* mm — margin on all sides */
          filename:     filename,
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },  /* scale:2 = crisp text */
          jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        /* Generate and auto-download the PDF, then hide the slip again */
        html2pdf().set(opt).from(element).save().then(() => {
          element.style.display = 'none';
        });
      }


        function shareWhatsApp() {

       // generatePDF();

      const ref = '<?= addslashes($ref_number ?? '') ?>';
      const name = '<?= addslashes($name ?? '') ?>';
      const date = '<?= addslashes($formatted_date ?? '') ?>';
      const time = '<?= addslashes($appt_time ?? '') ?>';
      const type = '<?= addslashes($visit_type ?? '') ?>';

      const msg =
        `🩺 *Appointment Confirmation*
*Dr. Zamzameer Clinic*

📋 *Ref:* #${ref}
👤 *Patient:* ${name}
📅 *Date:* ${date}
⏰ *Time:* ${time}
🏥 *Visit Type:* ${type}

📍 284, Hospital Road, SAMMANTHURAI-01
📞 +94 77 345 0900

_Please arrive 10 minutes early._`;

      const url = 'https://wa.me/?text=' + encodeURIComponent(msg);
      window.open(url, '_blank');
    }

    </script>
  </body>
</html>