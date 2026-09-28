<?php
require_once 'db.php';

$all_slots = ['05:00 PM', '05:30 PM', '06:00 PM', '06:30 PM', '07:00 PM', '07:30 PM', '08:00 PM', '08:30 PM', '09:00 PM', '09:30 PM', '10:00 PM'];
$today = date('Y-m-d');
$now = time();

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
if (date('N') == 7) {
  $is_available = false;
}

/* ── Next available slot today ── */
$next_slot = null;
$next_slot_date = 'Today';

if ($is_available) {
  $taken_stmt = $pdo->prepare("SELECT appt_time FROM appointments WHERE appt_date = ? AND status != 'cancelled'");
  $taken_stmt->execute([$today]);
  $taken_today = $taken_stmt->fetchAll(PDO::FETCH_COLUMN);

  foreach ($all_slots as $slot) {
    if (strtotime(date('Y-m-d') . ' ' . $slot) <= $now)
      continue;
    if (!in_array($slot, $taken_today)) {
      $next_slot = $slot;
      break;
    }
  }

  if (!$next_slot) {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $taken_stmt->execute([$tomorrow]);
    $taken_tomorrow = $taken_stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($all_slots as $slot) {
      if (!in_array($slot, $taken_tomorrow)) {
        $next_slot = $slot;
        $next_slot_date = 'Tomorrow';
        break;
      }
    }
  }
}

if (!$next_slot) {
  $next_slot = $is_available ? 'No slots' : 'Not Available';
  $next_slot_date = $is_available ? 'Available' : 'Doctor';
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dr. Zamzameer — Appointment Booking</title>
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

    /* Animations */
    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.2;
      }
    }

    .animate-up {
      animation: fadeUp 0.6s ease;
    }

    .animate-up-d {
      animation: fadeUp 0.8s ease;
    }

    .live-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #20c997;
      display: inline-block;
      animation: blink 1.4s infinite;
    }

    /* ══ PROFESSIONAL NAVBAR ══ */
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

    @media (max-width: 991.98px) {
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

    /* Services */
    .service-card {
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: default;
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
    }

    /* Gallery */
    .gallery-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 12px;
      transition: transform 0.3s;
    }

    .gallery-img:hover {
      transform: scale(1.03);
    }

    .gallery-placeholder {
      width: 100%;
      height: 200px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
    }

    /* Contact */
    .contact-info-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 16px;
      border-radius: 12px;
      background: #f8f9fa;
      margin-bottom: 12px;
    }

    /* Map */
    #map {
      border-radius: 16px;
      overflow: hidden;
    }

    /* Section scroll offset for sticky nav */
    section[id] {
      scroll-margin-top: 70px;
    }
  </style>
</head>

<body class="bg-light">

  <!-- ═══════════════════════════════ NAVBAR ═══════════════════════════════ -->
  <nav class="navbar navbar-expand-lg sticky-top pro-nav" id="mainNav">
    <div class="container d-flex align-items-center">

      <!-- Brand -->
      <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none me-4" href="index.php">
        <div class="nav-brand-icon">🩺</div>
        <div>
          <span class="fw-bold font-display" style="font-size:1.1rem">Dr. <span
              class="text-primary">Zamzameer</span></span>
          <div class="text-muted" style="font-size:0.68rem;line-height:1;margin-top:1px">General Physician</div>
        </div>
      </a>

      <!-- Hamburger -->
      <button class="navbar-toggler pro-toggler ms-auto" type="button" data-bs-toggle="collapse"
        data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Links -->
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto gap-lg-1 align-items-lg-center">
          <li class="nav-item"><a class="nav-link active-page" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
          <!-- <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li> -->
          <li class="nav-item"><a class="nav-link" href="index.php#location">Location</a></li>
          <li class="nav-item"><a class="nav-link" href="how-it-works.php">How It Works</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>

        </ul>
        <!-- CTA Book button -->
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

  <!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
  <section class="py-5">
    <div class="container py-4">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 animate-up">
          <span
            class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-flex align-items-center gap-2 fw-semibold">
            <?php if ($is_available): ?><span class="live-dot"></span> Accepting Appointments Today<?php else: ?><span
                style="width:8px;height:8px;border-radius:50%;background:#dc3545;display:inline-block;"></span> Not
              Accepting Appointments<?php endif; ?>
          </span>
          <h1 class="display-5 fw-bold font-display lh-sm mb-3">
            Book Your Visit with<br /><span class="text-primary">Dr. Zamzameer</span><br />Easily &amp; Fast
          </h1>
          <p class="text-secondary mb-4">
            Schedule your appointment online in just a few steps. No waiting, no calls needed — simple, fast, and
            confirmed instantly.
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <a href="appointment.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-semibold shadow">📅 Book
              Now</a>
            <!-- <a href="#services" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-semibold">Our Services</a> -->
          </div>
        </div>
        <div class="col-lg-6 animate-up-d">
          <div class="card border rounded-4 shadow p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center fs-1"
                style="width:70px;height:70px">👨‍⚕️</div>
              <div>
                <h5 class="fw-bold font-display mb-0">Dr. Zamzameer</h5>
                <small class="text-primary fw-semibold">General Physician</small><br />
                <?php if ($is_available): ?><span
                    class="badge bg-success bg-opacity-10 text-success rounded-pill mt-1 small">● Available
                    Today</span><?php else: ?><span
                    class="badge bg-danger bg-opacity-10 text-danger rounded-pill mt-1 small">● Not
                    Available</span><?php endif; ?>
              </div>
            </div>
            <hr class="my-2" />
            <div class="d-flex flex-column gap-3 mb-3">
              <div class="d-flex align-items-center gap-2">
                <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 fs-6">📍</span>
                <div>
                  <div class="text-muted" style="font-size:0.75rem">Clinic Location</div>
                  <div class="fw-semibold small">284, Hospital Road, SAMMANTHURAI-01</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 fs-6">⏰</span>
                <div>
                  <div class="text-muted" style="font-size:0.75rem">Working Hours</div>
                  <div class="fw-semibold small">Mon – Sat · 5:00 PM – 10:00 PM</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 fs-6">⭐</span>
                <div>
                  <div class="text-muted" style="font-size:0.75rem">Patient Rating</div>
                  <div class="fw-semibold small">4.9 / 5.0 · 500+ Reviews</div>
                </div>
              </div>
            </div>
            <div class="bg-dark rounded-3 p-3 d-flex align-items-center gap-3">
              <span class="fs-5">⏩</span>
              <div>
                <div class="text-white-50 small">Next Available Slot</div>
                <div class="text-white fw-semibold small">Today · 6:00 PM</div>
              </div>
              <button class="btn btn-primary btn-sm rounded-pill ms-auto fw-semibold"
                onclick="location.href='appointment.php'">Book</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════ STATS ═══════════════════════════════ -->
  <div class="bg-dark py-4">
    <div class="container">
      <div class="row g-0 text-center">
        <div class="col-6 col-md-3 border-end border-secondary py-2">
          <div class="font-display fs-2 fw-bold text-primary">500+</div>
          <div class="text-white-50 small">Happy Patients</div>
        </div>
        <div class="col-6 col-md-3 border-end border-secondary py-2">
          <div class="font-display fs-2 fw-bold text-primary">8+</div>
          <div class="text-white-50 small">Years Experience</div>
        </div>
        <div class="col-6 col-md-3 border-end border-secondary py-2">
          <div class="font-display fs-2 fw-bold text-primary">4.9★</div>
          <div class="text-white-50 small">Patient Rating</div>
        </div>
        <div class="col-6 col-md-3 py-2">
          <div class="font-display fs-2 fw-bold text-primary">24/7</div>
          <div class="text-white-50 small">Online Booking</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════ SERVICES SECTION ════════════════════════ -->
  <section class="py-5 bg-white" id="services">
    <div class="container py-3">
      <div class="text-center mb-5">
        <span
          class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold text-uppercase mb-3 d-inline-block">What
          We Offer</span>
        <h2 class="font-display fw-bold mb-2">Our Medical Services</h2>
        <p class="text-secondary small">Comprehensive healthcare for you and your family</p>
      </div>
      <div class="row g-4">

        <!-- Service 1: General Consultation -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">🩺</div>
            <h5 class="fw-bold mb-2">General Consultation</h5>
            <p class="text-secondary small mb-0">Routine check-ups, fever, cold, infections, and general health issues
              handled with care and expertise.</p>
          </div>
        </div>

        <!-- Service 2: Health Checkup -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">🏥</div>
            <h5 class="fw-bold mb-2">Full Health Checkup</h5>
            <p class="text-secondary small mb-0">Comprehensive body checkup including blood pressure, sugar, and
              complete medical evaluation.</p>
          </div>
        </div>

        <!-- Service 3: Chronic Disease Management -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">💊</div>
            <h5 class="fw-bold mb-2">Chronic Disease Care</h5>
            <p class="text-secondary small mb-0">Ongoing management of diabetes, hypertension, and other long-term
              conditions with regular monitoring.</p>
          </div>
        </div>

        <!-- Service 4: Child Health -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">👶</div>
            <h5 class="fw-bold mb-2">Child Healthcare</h5>
            <p class="text-secondary small mb-0">Pediatric consultations, vaccinations, and child development monitoring
              from infancy to adolescence.</p>
          </div>
        </div>

        <!-- Service 5: Prescription -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">📋</div>
            <h5 class="fw-bold mb-2">Prescription & Lab Referrals</h5>
            <p class="text-secondary small mb-0">Medical prescriptions and referrals to specialist labs and hospitals
              when needed.</p>
          </div>
        </div>

        <!-- Service 6: Follow-up -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 service-card">
            <div
              class="bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center fs-2 mb-3"
              style="width:60px;height:60px">🔄</div>
            <h5 class="fw-bold mb-2">Follow-up Visits</h5>
            <p class="text-secondary small mb-0">Scheduled follow-up appointments to track recovery progress and adjust
              treatment plans.</p>
          </div>
        </div>

      </div>
      <div class="text-center mt-4">
        <a href="appointment.php" class="btn btn-primary rounded-pill px-5 fw-semibold">Book a Service →</a>
      </div>
    </div>
  </section>


  <!-- ═══════════════════════════ LOCATION SECTION ════════════════════════ -->
  <!-- Google Maps embed + திசைமாற்றம் / வழிகாட்டி -->
  <section class="py-5" id="location">
    <div class="container py-3">
      <div class="text-center mb-5">
        <span
          class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold text-uppercase mb-3 d-inline-block">Find
          Us</span>
        <h2 class="font-display fw-bold mb-2">Clinic Location</h2>
        <p class="text-secondary small">Easy to reach — right on Hospital Road</p>
      </div>
      <div class="row g-4 align-items-center">
        <div class="col-lg-12">
          <div class="card border-0 shadow-sm rounded-4 p-4">
            <!-- Address info -->
            <div class="contact-info-item">
              <span class="fs-4">📍</span>
              <div>
                <div class="fw-bold">Clinic Address</div>
                <div class="text-secondary small">284, Hospital Road,<br />SAMMANTHURAI-01,<br />Eastern Province, Sri
                  Lanka</div>
              </div>
            </div>
            <div class="contact-info-item">
              <span class="fs-4">⏰</span>
              <div>
                <div class="fw-bold">Working Hours</div>
                <div class="text-secondary small">
                  Monday – Saturday: 5:00 PM – 10:00 PM<br />
                  Sunday: Closed
                </div>
              </div>
            </div>
            <div class="contact-info-item">
              <span class="fs-4">🚗</span>
              <div>
                <div class="fw-bold">Getting Here</div>
                <div class="text-secondary small">Parking available. 5 min from Sammanthurai town centre.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </section>

  <!-- ═══════════════════════════════ FOOTER ══════════════════════════════ -->
  <footer class="bg-dark border-top border-secondary text-center text-white small py-4 mt-4 mb-6">
    <p class="mb-1">© 2026 <span class="text-primary fw-semibold">Dr. Zamzameer</span> — Appointment Booking System. All
      Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Smooth scroll + mobile navbar auto-close on anchor click -->
  <script>
    // ── Navbar shadow on scroll ──
    const mainNav = document.getElementById('mainNav');
    window.addEventListener('scroll', () => {
      mainNav.classList.toggle('scrolled', window.scrollY > 20);
    });

    // ── Active nav link highlight based on scroll position ──
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.pro-nav .nav-link[href^="#"]');
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(sec => {
        if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
      });
      navLinks.forEach(link => {
        link.classList.remove('active-page');
        if (link.getAttribute('href') === '#' + current) link.classList.add('active-page');
      });
    });

    // ── Smooth scroll + close mobile menu on anchor click ──
    document.querySelectorAll('a.nav-anchor, a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href || href === '#') return;
        if (href.startsWith('#')) {
          const target = document.querySelector(href);
          if (target) {
            e.preventDefault();
            const navCollapse = document.getElementById('navMenu');
            if (navCollapse && navCollapse.classList.contains('show')) {
              const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
              if (bsCollapse) bsCollapse.hide();
            }
            setTimeout(() => {
              target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
          }
        }
      });
    });
  </script>
</body>

</html>