<?php
session_start();
require_once 'db.php';
$contact_sent = false;
$contact_error = '';

// PRG Pattern — POST redirect GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_name'])) {
  $c_name    = trim($_POST['contact_name']    ?? '');
  $c_email   = trim($_POST['contact_email']   ?? '');
  $c_phone   = trim($_POST['contact_phone']   ?? '');
  $c_subject = trim($_POST['contact_subject'] ?? '');
  $c_message = trim($_POST['contact_message'] ?? '');

  if ($c_name && $c_email && $c_message) {
    $ins = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)");
    $ins->execute([$c_name, $c_email, $c_phone, $c_subject, $c_message]);
    // PRG: redirect after success — prevents duplicate on refresh
    $_SESSION['contact_sent'] = true;  // ADD THIS
    header('Location: contact.php');   // CHANGE — remove ?sent=1
    exit;
  } else {
    $contact_error = 'Please fill in all required fields.';
  }
}

// Show success if redirected here after POST
if (!empty($_SESSION['contact_sent'])) {
  $contact_sent = true;
  unset($_SESSION['contact_sent']);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact — Dr. Zamzameer</title>
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

    .pro-nav {
      background: rgba(255, 255, 255, 0.92) !important;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.07) !important;
      padding: 0;
      transition: box-shadow 0.3s;
    }

    .pro-nav.scrolled {
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.10) !important;
    }

    .pro-nav .container {
      height: 68px;
    }

    .nav-brand-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, #0d6efd, #0a58ca);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
      flex-shrink: 0;
    }

    .pro-nav .nav-link {
      font-size: 0.875rem;
      font-weight: 500;
      color: #555 !important;
      padding: 6px 14px !important;
      border-radius: 8px;
      position: relative;
      transition: color 0.2s, background 0.2s;
    }

    .pro-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 2px;
      left: 50%;
      right: 50%;
      height: 2px;
      background: #0d6efd;
      border-radius: 2px;
      transition: left 0.25s, right 0.25s;
    }

    .pro-nav .nav-link:hover {
      color: #0d6efd !important;
      background: rgba(13, 110, 253, 0.06);
    }

    .pro-nav .nav-link:hover::after,
    .pro-nav .nav-link.active-page::after {
      left: 14px;
      right: 14px;
    }

    .pro-nav .nav-link.active-page {
      color: #0d6efd !important;
      font-weight: 600;
      background: rgba(13, 110, 253, 0.07);
    }

    .nav-book-btn {
      background: linear-gradient(135deg, #0d6efd, #0a58ca);
      border: none;
      border-radius: 50px;
      padding: 9px 22px;
      font-size: 0.875rem;
      font-weight: 600;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(13, 110, 253, 0.35);
      transition: transform 0.18s, box-shadow 0.18s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .nav-book-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 20px rgba(13, 110, 253, 0.45);
      color: #fff !important;
    }

    .pro-toggler {
      border: 1.5px solid rgba(0, 0, 0, 0.12) !important;
      border-radius: 10px !important;
      padding: 7px 10px !important;
      transition: background 0.2s;
    }

    .pro-toggler:hover {
      background: rgba(13, 110, 253, 0.06);
    }

    @media (max-width:991.98px) {
      .pro-nav .navbar-collapse {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(0, 0, 0, 0.06);
      }

      .pro-nav .nav-link::after {
        display: none;
      }

      .pro-nav .nav-link {
        padding: 10px 14px !important;
        border-radius: 10px;
      }

      .nav-book-btn {
        width: 100%;
        justify-content: center;
        margin-top: 8px;
      }
    }
  </style>
  <style>
    .info-card {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 18px;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      margin-bottom: 14px;
      transition: transform 0.2s;
    }

    .info-card:hover {
      transform: translateY(-2px);
    }
  </style>
</head>

<body class="bg-light">
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg sticky-top pro-nav" id="mainNav">
    <div class="container d-flex align-items-center">
      <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none me-4" href="index.php">
        <div class="nav-brand-icon">🩺</div>
        <div>
          <span class="fw-bold font-display" style="font-size:1.1rem">Dr. <span
              class="text-primary">Zamzameer</span></span>
          <div class="text-muted" style="font-size:0.68rem;line-height:1;margin-top:1px">General Physician</div>
        </div>
      </a>
      <button class="navbar-toggler pro-toggler ms-auto" type="button" data-bs-toggle="collapse"
        data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto gap-lg-1 align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#location">Location</a></li>

          <li class="nav-item"><a class="nav-link" href="how-it-works.php">How It Works</a></li>
          <!-- <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li> -->
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link active-page" href="contact.php">Contact</a></li>
        </ul>
        <a class="nav-book-btn" href="appointment.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          Book Appointment
        </a>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <div class="bg-dark text-white text-center py-5">
    <div class="container">
      <div class="fs-1 mb-2">📞</div>
      <h1 class="font-display fw-bold mb-2">Contact Us</h1>
      <p class="text-white-50 mb-0">We're here to help — reach out anytime</p>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container my-5">
    <div class="row g-4">

      <!-- LEFT — Info Cards -->
      <div class="col-lg-5">
        <h4 class="font-display fw-bold mb-4">Get In Touch</h4>

        <div class="info-card">
          <div
            class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center fs-4 flex-shrink-0"
            style="width:52px;height:52px">📞</div>
          <div>
            <div class="fw-bold">Phone</div>
            <div class="text-secondary small">+94 77 345 0900</div>
            <a href="tel:+94773450900" class="btn btn-sm btn-primary rounded-pill px-3 mt-2">Call Now</a>
          </div>
        </div>

        <!-- <div class="info-card">
          <div
            class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center fs-4 flex-shrink-0"
            style="width:52px;height:52px">📧</div>
          <div>
            <div class="fw-bold">Email</div>
            <div class="text-secondary small">drzamzameer@clinic.lk</div>
            <a href="mailto:drzamzameer@clinic.lk" class="btn btn-sm btn-success rounded-pill px-3 mt-2">Send Email</a>
          </div>
        </div> -->

        <div class="info-card">
          <div
            class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center fs-4 flex-shrink-0"
            style="width:52px;height:52px"><i class="bi bi-whatsapp"></i></div>
          <div>
            <div class="fw-bold">WhatsApp</div>
            <div class="text-secondary small">+94 77 345 0900</div>
            <a href="https://wa.me/94773450900" target="_blank"
              class="btn btn-sm btn-info text-white rounded-pill px-3 mt-2">Chat on WhatsApp</a>
          </div>
        </div>

        <div class="info-card">
          <div
            class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center fs-4 flex-shrink-0"
            style="width:52px;height:52px">📍</div>
          <div>
            <div class="fw-bold">Address</div>
            <div class="text-secondary small">284, Hospital Road,<br />SAMMANTHURAI-01,<br />Eastern Province, Sri Lanka
            </div>
          </div>
        </div>

        <div class="info-card">
          <div
            class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center fs-4 flex-shrink-0"
            style="width:52px;height:52px">⏰</div>
          <div>
            <div class="fw-bold">Working Hours</div>
            <div class="text-secondary small">Monday – Saturday: 5:00 PM – 10:00 PM<br /><span
                class="text-danger">Sunday: Closed</span></div>
          </div>
        </div>
      </div>

      <!-- RIGHT — Contact Form -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h4 class="font-display fw-bold mb-4">📨 Send us a Message</h4>
          <?php if ($contact_sent): ?>
            <div class="alert alert-success rounded-3 text-center py-4">
              <div class="fs-1 mb-2">✅</div>
              <h5 class="fw-bold">Message Sent!</h5>
              <p class="mb-0 text-secondary small">We'll get back to you within 24 hours.</p>
            </div>
          <?php else: ?>
            <?php if ($contact_error): ?>
              <div class="alert alert-danger rounded-3">⚠️ <?= htmlspecialchars($contact_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="contact.php">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-semibold small">Full Name *</label>
                  <input type="text" name="contact_name" class="form-control rounded-3" placeholder="Your name" required
                    value="<?= htmlspecialchars($_POST['contact_name'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold small">Email Address *</label>
                  <input type="email" name="contact_email" class="form-control rounded-3" placeholder="your@email.com"
                    required value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold small">Phone Number</label>
                  <input type="tel" name="contact_phone" class="form-control rounded-3" placeholder="+94 77 345 0900"
                    value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>" />
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold small">Subject</label>
                  <input type="text" name="contact_subject" class="form-control rounded-3"
                    placeholder="What is this about?" value="<?= htmlspecialchars($_POST['contact_subject'] ?? '') ?>" />
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold small">Message *</label>
                  <textarea name="contact_message" class="form-control rounded-3" rows="5"
                    placeholder="Type your message here..."
                    required><?= htmlspecialchars($_POST['contact_message'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold">📨 Send Message</button>
                </div>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- BOTTOM CTA -->
  <div class="container mb-35">
    <div class="bg-primary text-white text-center rounded-4 p-4">
      <h5 class="font-display fw-bold mb-2">Want to book directly?</h5>
      <p class="text-white-50 small mb-3">Skip the form — book your appointment online in minutes</p>
      <a href="appointment.php" class="btn btn-light text-primary fw-bold rounded-pill px-4">📅 Book Appointment</a>
    </div>
  </div>

  <footer class="bg-dark border-top border-secondary text-center text-white small py-4 mt-4 mb-6">
    <p class="mb-1">© 2026 <span class="text-primary fw-semibold">Dr. Zamzameer</span> — Appointment Booking System. All
      Rights Reserved.</p>
   
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const mainNav = document.getElementById('mainNav');
    window.addEventListener('scroll', () => { if (mainNav) mainNav.classList.toggle('scrolled', window.scrollY > 20); });
  </script>
</body>

</html>