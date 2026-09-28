<?php
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if (empty($email) || empty($pass)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && $pass === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name']      = $user['username'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dr. Zamzameer — Doctor Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>body{font-family:"Outfit",sans-serif;}.font-display{font-family:"Playfair Display",serif;}</style>
  </head>
  <body>
    <div class="container-fluid p-0">
      <div class="row g-0 min-vh-100">
        <div class="col-md-6 bg-dark d-flex flex-column justify-content-center align-items-center p-5 text-white">
          <div class="text-center" style="max-width:340px">
            <div class="bg-primary rounded-4 d-flex align-items-center justify-content-center mx-auto mb-4 fs-1 shadow" style="width:90px;height:90px">👨‍⚕️</div>
            <h2 class="font-display fw-bold mb-2">Dr. Zamzameer's<br/>Management Portal</h2>
            <p class="text-white-50 small mb-4">Login to view and manage all patient appointments, confirm bookings, and keep track of your daily schedule.</p>
            <div class="d-flex flex-column gap-2 text-start">
              <div class="d-flex align-items-center gap-3"><span class="bg-primary bg-opacity-25 rounded-3 p-2">📅</span><span class="text-white-50 small">View all appointments</span></div>
              <div class="d-flex align-items-center gap-3"><span class="bg-primary bg-opacity-25 rounded-3 p-2">✅</span><span class="text-white-50 small">Confirm or cancel bookings</span></div>
              <div class="d-flex align-items-center gap-3"><span class="bg-primary bg-opacity-25 rounded-3 p-2">👥</span><span class="text-white-50 small">Manage patient records</span></div>
              <div class="d-flex align-items-center gap-3"><span class="bg-primary bg-opacity-25 rounded-3 p-2">📊</span><span class="text-white-50 small">Dashboard overview</span></div>
            </div>
          
          </div>
        </div>
        <div class="col-md-6 bg-white d-flex align-items-center justify-content-center p-5">
          <div style="width:100%;max-width:400px">
            <a href="index.php" class="text-secondary small text-decoration-none d-block mb-4">← Back to Home</a>
            <h2 class="font-display fw-bold mb-1">Doctor Login 🔐</h2>
            <p class="text-secondary small mb-4">Access your appointment management dashboard</p>
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
              <div class="mb-3">
                <label class="form-label fw-semibold small">Email Address</label>
                <input class="form-control rounded-3" type="email" name="email" placeholder="admin@zamzameer.lk" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small">Password</label>
                <input class="form-control rounded-3" type="text" name="password" placeholder="Enter your password" required/>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="rememberMe"/>
                  <label class="form-check-label small text-secondary" for="rememberMe">Remember me</label>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow">Login to Dashboard →</button>
            </form>
            <p class="text-center text-secondary small mt-4">Not the doctor? <a href="appointment.php" class="text-primary fw-semibold text-decoration-none">Book an Appointment</a></p>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
