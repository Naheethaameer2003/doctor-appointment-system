<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Dr. Zamzameer</title>
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
          <li class="nav-item"><a class="nav-link" href="how-it-works.php">How It Works</a></li>
          <li class="nav-item"><a class="nav-link active-page" href="about.php">About</a></li>
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
      <div class="fs-1 mb-2">👨‍⚕️</div>
      <h1 class="font-display fw-bold mb-2">About Dr. Zamzameer</h1>
      <p class="text-white-50 mb-0">Trusted General Physician · Sammanthurai, Sri Lanka</p>
    </div>
  </div>

  <!-- PROFILE + BIO -->
  <div class="container my-5">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
          <h4 class="font-display fw-bold mb-4">🩺 Doctor Profile</h4>
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center gap-3">
              <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 fs-5">👨‍⚕️</span>
              <div>
                <div class="text-muted small">Qualification</div>
                <div class="fw-semibold">MBBS — General Physician</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="bg-success bg-opacity-10 text-success rounded-3 p-2 fs-5">🎓</span>
              <div>
                <div class="text-muted small">University</div>
                <div class="fw-semibold">University of Colombo</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 fs-5">💼</span>
              <div>
                <div class="text-muted small">Experience</div>
                <div class="fw-semibold">10+ Years in Medical Practice</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="bg-info bg-opacity-10 text-info rounded-3 p-2 fs-5">📍</span>
              <div>
                <div class="text-muted small">Clinic</div>
                <div class="fw-semibold">284, Hospital Road, Sammanthurai-01</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="bg-danger bg-opacity-10 text-danger rounded-3 p-2 fs-5">⏰</span>
              <div>
                <div class="text-muted small">Working Hours</div>
                <div class="fw-semibold">Mon – Sat · 5:00 PM – 10:00 PM</div>
              </div>
            </div>
          </div>
          <hr class="my-4" />
          <h6 class="fw-semibold mb-3">Specialties</h6>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">Fever &amp;
              Infections</span>
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Diabetes</span>
            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">Blood Pressure</span>
            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-2">Child Care</span>
            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">Chronic Disease</span>
            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">General Health</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 d-flex flex-column gap-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h4 class="font-display fw-bold mb-3">About the Doctor</h4>
          <p class="text-secondary mb-0">Dr. Zamzameer is a dedicated General Physician based in Sammanthurai, Eastern
            Province, Sri Lanka. With over 10 years of clinical experience, he provides accurate diagnosis and
            patient-focused treatment with a modern medical care approach. He is committed to making healthcare
            affordable and accessible to every family in the community.</p>
        </div>
        <div class="row g-3">
          <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
              <div class="font-display fw-bold fs-3 text-primary">1200+</div>
              <div class="small text-muted">Patients</div>
            </div>
          </div>
          <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
              <div class="font-display fw-bold fs-3 text-primary">10+</div>
              <div class="small text-muted">Years Exp.</div>
            </div>
          </div>
          <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
              <div class="font-display fw-bold fs-3 text-primary">4.9★</div>
              <div class="small text-muted">Rating</div>
            </div>
          </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h6 class="fw-bold mb-3">Our Values</h6>
          <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-start gap-2"><span class="text-success fw-bold">✔</span><span
                class="small text-secondary"><strong>Mission:</strong> Provide affordable and trusted healthcare for
                everyone.</span></div>
            <div class="d-flex align-items-start gap-2"><span class="text-primary fw-bold">✔</span><span
                class="small text-secondary"><strong>Vision:</strong> Become a leading digital healthcare clinic in Sri
                Lanka.</span></div>
            <div class="d-flex align-items-start gap-2"><span class="text-warning fw-bold">✔</span><span
                class="small text-secondary"><strong>Care:</strong> Every patient is treated with compassion and
                respect.</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="container mb-5">
    <div class="bg-primary text-white text-center rounded-4 p-5">
      <h2 class="font-display fw-bold mb-2">Ready to Book Your Visit?</h2>
      <p class="text-white-50 mb-3">Schedule an appointment with Dr. Zamzameer in minutes</p>
      <a href="appointment.php" class="btn btn-light text-primary fw-bold rounded-pill px-5 py-2">📅 Book
        Appointment</a>
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