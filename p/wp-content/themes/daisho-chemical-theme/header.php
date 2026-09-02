<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo get_theme_mod('site_description', 'Daisho Chemical R&D - Pioneers in ground improvement technology, specializing in chemical additives and engineering solutions for construction projects.'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Header -->
<header class="header">
    <div class="container">
        <nav class="navbar">
            <a href="<?php echo home_url(); ?>#home" class="logo"><?php echo get_theme_mod('site_logo', 'DAISHO CHEMICAL'); ?></a>
            <ul class="nav-menu">
                <li><a href="<?php echo home_url(); ?>#services"><?php echo get_theme_mod('nav_services', 'SERVICES'); ?></a></li>
                <li><a href="<?php echo home_url(); ?>#products"><?php echo get_theme_mod('nav_products', 'PRODUCTS'); ?></a></li>
                <li><a href="<?php echo home_url(); ?>#timeline"><?php echo get_theme_mod('nav_timeline', 'TIMELINE'); ?></a></li>
                <li><a href="<?php echo home_url(); ?>#contact"><?php echo get_theme_mod('nav_contact', 'CONTACT'); ?></a></li>
            </ul>
            <a href="#" class="language-btn"><?php echo get_theme_mod('language_text', 'LANGUAGE'); ?></a>
        </nav>
    </div>
</header>