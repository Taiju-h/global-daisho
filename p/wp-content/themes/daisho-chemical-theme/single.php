<?php
/**
 * Single Post Template
 * Since this is a one-page theme, redirect to home
 */

// Redirect to home page since this is a one-page theme
wp_redirect(home_url());
exit;
?>