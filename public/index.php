<?php
// public/index.php
$page = 'home';

require_once '../includes/header.php';

// Optional: Log page view
f_log_function_call('render_public_home', [], 'Public home page loaded');
?>

<main>
    <h1>Welcome to Ambrose Archery</h1>
    <p>Indoor archery range with 15 lanes in Melbourne, Australia.</p>
    <p>Book your session online or call us on 0415 XXX XXX</p>
    
    <div style="margin: 30px 0;">
        <img src="assets/Archery.Webp" alt="Ambrose Archery Indoor Range" style="max-width: 100%; height: auto; border-radius: 8px;">
    </div>

    <a href="booking.php" style="display: inline-block; padding: 15px 30px; background: #0066cc; color: white; text-decoration: none; border-radius: 6px; font-size: 1.1em;">
        Book Your Session Now →
    </a>
</main>

<?php
require_once '../includes/footer.php';
?>