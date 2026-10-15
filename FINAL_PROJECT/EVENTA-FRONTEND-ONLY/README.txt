EVENTA — FRONTEND ONLY (NO BACKEND, NO SQL)

Contains ONLY HTML/CSS/JavaScript. It does not contain or modify api/, config/, vendor/, database or phpMyAdmin files.

INSTALL:
1. BACK UP your existing project first.
2. Copy the folder eventa-FRONTEND-ONLY into your EXISTING project folder, e.g.
   C:\xampp\htdocs\event_ticketing_db\eventa-FRONTEND-ONLY\
3. Keep your original api/, config/, vendor/ exactly as they are.
4. Start Apache in XAMPP and open the site through localhost, not by double-clicking
   an HTML file. For this project layout, open:
   http://localhost/FINAL_PROJECT/EVENTA-FRONTEND-ONLY/viewer.html
   (adjust FINAL_PROJECT if your project folder has a different name).

EXPECTED LAYOUT:
  event_ticketing_db/
    api/login.php, events.php, bookings.php, schedules.php, venues.php, users.php
    config/ ... ORIGINAL FILES
    vendor/ ... ORIGINAL FILES
    eventa-FRONTEND-ONLY/
      viewer.html, customer.html, organiser.html, admin.html, index.html
      assets/app.js, assets/style.css

IMPORTANT: Do not open the HTML pages from a file:/// URL. The browser cannot use
that origin to call PHP. app.js expects API endpoints in the project's api/ folder.
If your existing backend lives elsewhere, change const API near the beginning of
assets/app.js to its actual URL.

VIEWER uses static event preview because supplied events.php requires JWT.
Customers can create an account from customer.html; registration always creates
a customer account and signs them in. Organiser and admin pages remain sign-in
only. The other pages connect to your existing API using JWT.

Existing MariaDB ERROR 1130 happens even in XAMPP Shell and cannot be fixed by replacing frontend files. Database connectivity must be repaired separately. This ZIP never changes your backend or database.
