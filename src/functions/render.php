<?php
// src/functions/render.php
// All rendering / HTML generation functions (Pure where possible)

/**
 * Function: Render Header
 */
function f_render_header(string $title = 'Ambrose Archery'): string {
    return f_log_call('f_render_header', function() use ($title) {
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='en'>\n";
        $html .= "<head>\n";
        $html .= "    <meta charset='UTF-8'>\n";
        $html .= "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        $html .= "    <title>" . htmlspecialchars($title) . "</title>\n";
        $html .= "    <!-- Meta Pixel / Analytics will go here later -->\n";
        $html .= "</head>\n";
        $html .= "<body>\n";
        $html .= "    <header>\n";
        $html .= "        <h1>" . htmlspecialchars($title) . "</h1>\n";
        $html .= "    </header>\n";

        return $html;
    }, ['title' => $title]);
}

/**
 * Function: Render Navigation (Desktop + Mobile)
 */
function f_render_navigation(): string {
    return f_log_call('f_render_navigation', function() {
        $html = "<nav class='main-nav'>\n";
        $html .= "    <a href='index.php'>Home</a>\n";
        $html .= "    <a href='booking.php'>Book Now</a>\n";
        $html .= "    <a href='about.php'>About</a>\n";
        $html .= "    <a href='pricing.php'>Pricing</a>\n";
        $html .= "    <a href='contact.php'>Contact</a>\n";
        $html .= "</nav>\n";

        // Mobile menu placeholder
        $html .= "<div class='mobile-menu'>\n";
        $html .= "    <!-- Hamburger menu will be enhanced with JS later -->\n";
        $html .= "</div>\n";

        return $html;
    });
}

/**
 * Function: Render Footer
 */
function f_render_footer(): string {
    return f_log_call('f_render_footer', function() {
        $html = "<footer>\n";
        $html .= "    <p>&copy; " . date("Y") . " Ambrose Archery. All Rights Reserved.</p>\n";
        $html .= "    <p><a href='manage-booking.php'>Manage Booking</a></p>\n";
        $html .= "</footer>\n";
        $html .= "</body>\n";
        $html .= "</html>";

        return $html;
    });
}

/**
 * Helper: Full Page Wrapper (Optional but useful)
 */
function f_render_page(string $title, string $content): string {
    $html  = f_render_header($title);
    $html .= f_render_navigation();
    $html .= "<main>\n";
    $html .= $content;
    $html .= "</main>\n";
    $html .= f_render_footer();

    return $html;
}
?>