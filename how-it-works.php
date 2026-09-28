<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>How It Works — Dr. Zamzameer</title>
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
    .step-card {
      border-left: 4px solid #0d6efd;
      border-radius: 0 14px 14px 0;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .step-card:hover {
      transform: translateX(4px);
      box-shadow: 0 6px 20px rgba(13, 110, 253, 0.12) !important;
    }

    .step-num {
      width: 60px;
      height: 60px;
      border-radius: 14px;
      flex-shrink: 0;
    }

    .faq-item {
      cursor: pointer;
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
           <!-- <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li> -->
          <li class="nav-item"><a class="nav-link" href="index.php#location">Location</a></li>
          <li class="nav-item"><a class="nav-link active-page" href="how-it-works.php">How It Works</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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
      <div class="fs-1 mb-2">⚙️</div>
      <h1 class="font-display fw-bold mb-2">How It Works</h1>
      <p class="text-white-50 mb-0">Book your appointment in 3 simple steps — takes less than 2 minutes</p>
    </div>
  </div>

  <div class="container my-5">

    <!-- STEPS -->
    <div class="text-center mb-5">
      <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold text-uppercase">Simple
        3-Step Process</span>
    </div>

    <div class="d-flex flex-column gap-4 mb-5" style="max-width:720px;margin:0 auto">

      <div class="card border-0 shadow-sm step-card p-4 d-flex flex-row align-items-start gap-4">
        <div
          class="step-num bg-primary text-white d-flex align-items-center justify-content-center fw-bold font-display fs-3">
          1</div>
        <div>
          <h5 class="fw-bold mb-2">Fill Your Personal Details</h5>
          <p class="text-secondary small mb-3">Enter your basic information in the appointment booking form. This helps
            Dr. Zamzameer prepare for your visit.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Your Name</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Email Address</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Phone Number</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Reason for Visit</span>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm step-card p-4 d-flex flex-row align-items-start gap-4">
        <div
          class="step-num bg-primary text-white d-flex align-items-center justify-content-center fw-bold font-display fs-3">
          2</div>
        <div>
          <h5 class="fw-bold mb-2">Choose Your Date &amp; Time Slot</h5>
          <p class="text-secondary small mb-3">Pick a date and select an available time slot. Booked slots are shown in
            strikethrough so you only see open slots.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Available Time Slots</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Mon – Sat, 5PM–10PM</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ No Double Booking</span>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm step-card p-4 d-flex flex-row align-items-start gap-4">
        <div
          class="step-num bg-success text-white d-flex align-items-center justify-content-center fw-bold font-display fs-3">
          3</div>
        <div>
          <h5 class="fw-bold mb-2">Get Instant Confirmation</h5>
          <p class="text-secondary small mb-3">After submitting, you'll receive an instant booking confirmation with
            your reference number. A confirmation email is also sent to you.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Reference Number</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ Email Confirmation</span>
            <span class="badge bg-light text-dark border rounded-pill px-3">✔ PDF Slip Download</span>
          </div>
        </div>
      </div>

    </div>

    <!-- WHAT TO BRING -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
      <h5 class="font-display fw-bold mb-4">📋 What to Bring on the Day</h5>
      <div class="row g-3">
        <div class="col-md-3 col-6">
          <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
            <div class="fs-2 mb-1">📄</div>
            <div class="small fw-semibold">Appointment Slip<br />(PDF or Email)</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="bg-success bg-opacity-10 rounded-3 p-3 text-center">
            <div class="fs-2 mb-1">🪪</div>
            <div class="small fw-semibold">National ID Card</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="bg-warning bg-opacity-10 rounded-3 p-3 text-center">
            <div class="fs-2 mb-1">📁</div>
            <div class="small fw-semibold">Previous Medical Records</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="bg-info bg-opacity-10 rounded-3 p-3 text-center">
            <div class="fs-2 mb-1">⏰</div>
            <div class="small fw-semibold">Arrive 10 Min Early</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <div class="mb-5">
      <h5 class="font-display fw-bold mb-4">❓ Frequently Asked Questions</h5>
      <div class="accordion" id="faqAccordion">
        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
          <h2 class="accordion-header"><button class="accordion-button rounded-3 fw-semibold" type="button"
              data-bs-toggle="collapse" data-bs-target="#faq1">Can I reschedule my appointment?</button></h2>
          <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-secondary small">Yes. Please call us at <strong>+94 77 345 0900</strong> at
              least 24 hours before your appointment to reschedule.</div>
          </div>
        </div>
        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
          <h2 class="accordion-header"><button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
              data-bs-toggle="collapse" data-bs-target="#faq2">Will I receive a confirmation?</button></h2>
          <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-secondary small">Yes! After booking you will see a confirmation page with
              your reference number, and a confirmation email with all your appointment details is sent to your email
              address.</div>
          </div>
        </div>
        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
          <h2 class="accordion-header"><button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
              data-bs-toggle="collapse" data-bs-target="#faq3">What are the clinic hours?</button></h2>
          <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-secondary small">The clinic is open <strong>Monday to Saturday, 5:00 PM –
                10:00 PM</strong>. We are closed on Sundays.</div>
          </div>
        </div>
        <div class="accordion-item border-0 shadow-sm rounded-3">
          <h2 class="accordion-header"><button class="accordion-button collapsed rounded-3 fw-semibold" type="button"
              data-bs-toggle="collapse" data-bs-target="#faq4">Is walk-in available?</button></h2>
          <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-secondary small">Walk-ins are welcome but online booking is strongly
              recommended to avoid waiting. Online appointments get priority slots.</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- CTA -->
  <div class="container mb-5">
    <div class="bg-primary text-white text-center rounded-4 p-5">
      <h2 class="font-display fw-bold mb-2">Ready to Book?</h2>
      <p class="text-white-50 mb-3">It takes less than 2 minutes</p>
      <a href="appointment.php" class="btn btn-light text-primary fw-bold rounded-pill px-5 py-2">📅 Book Appointment
        Now</a>
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