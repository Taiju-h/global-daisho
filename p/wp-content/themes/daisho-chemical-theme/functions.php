<?php
/**
 * Daisho Chemical Theme Functions
 * Registers theme support, customizer options, and enqueues styles
 */

// Enqueue styles and scripts
function daisho_theme_scripts() {
    wp_enqueue_style('daisho-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Add Google Fonts - make dynamic based on customizer
    $font_choice = get_theme_mod('font_family', 'Inter');
    $font_url = '';
    
    switch($font_choice) {
        case 'Inter':
            $font_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap';
            break;
        case 'Roboto':
            $font_url = 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap';
            break;
        case 'Open Sans':
            $font_url = 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap';
            break;
        case 'Montserrat':
            $font_url = 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap';
            break;
        case 'Poppins':
            $font_url = 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap';
            break;
        case 'Lato':
            $font_url = 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap';
            break;
    }
    
    if ($font_url) {
        wp_enqueue_style('google-fonts', $font_url, array(), null);
    }
    
    // Add custom CSS from customizer
    $custom_css = get_theme_mod('custom_css', '');
    if ($custom_css) {
        wp_add_inline_style('daisho-style', $custom_css);
    }
    
    // Add dynamic color styles
    $primary_color = get_theme_mod('primary_color', '#ffc107');
    $text_color = get_theme_mod('text_color', '#333333');
    $font_family = get_theme_mod('font_family', 'Inter');
    $button_text_color = get_theme_mod('button_text_color', '#ffffff');
    
    $dynamic_css = "
        :root {
            --primary-color: {$primary_color};
            --text-color: {$text_color};
            --font-family: '{$font_family}', sans-serif;
            --button-text-color: {$button_text_color};
        }
        body {
            font-family: var(--font-family);
            color: var(--text-color);
        }
        .logo, .hero-title .highlight, .cta-button, .service-icon, 
        .product-title, .timeline-line, .timeline-year, .company-name, 
        .contact-detail a, .join-btn {
            color: var(--primary-color) !important;
        }
        .cta-button, .service-icon, .timeline-year, .join-btn {
            background: var(--primary-color) !important;
        }
        .nav-menu a:hover {
            color: var(--primary-color) !important;
        }
        .cta-button, .join-btn {
            color: var(--button-text-color) !important;
        }
    ";
    
    wp_add_inline_style('daisho-style', $dynamic_css);
    
    // Enqueue language translator
    wp_enqueue_script('daisho-translator', get_template_directory_uri() . '/js/language-translator.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'daisho_theme_scripts');

// Theme setup
function daisho_theme_setup() {
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add theme support for custom logo
    add_theme_support('custom-logo');
    
    // Add theme support for HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
}
add_action('after_setup_theme', 'daisho_theme_setup');

// Customizer options
function daisho_customize_register($wp_customize) {
    
    // Colors & Styling Section
    $wp_customize->add_section('colors_styling_section', array(
        'title' => 'Colors & Styling',
        'priority' => 25,
    ));
    
    // Primary/Accent Color
    $wp_customize->add_setting('primary_color', array(
        'default' => '#ffc107',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label' => 'Primary/Accent Color',
        'section' => 'colors_styling_section',
        'description' => 'Used for buttons, highlights, and accents throughout the site',
    )));
    
    // Text Color
    $wp_customize->add_setting('text_color', array(
        'default' => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'text_color', array(
        'label' => 'Main Text Color',
        'section' => 'colors_styling_section',
    )));
    
    // Font Family
    $wp_customize->add_setting('font_family', array(
        'default' => 'Inter',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('font_family', array(
        'label' => 'Font Family',
        'section' => 'colors_styling_section',
        'type' => 'select',
        'choices' => array(
            'Inter' => 'Inter (Default)',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Montserrat' => 'Montserrat',
            'Poppins' => 'Poppins',
            'Lato' => 'Lato',
            'Arial' => 'Arial (System Font)',
        ),
    ));
    
    // Button Text Color
    $wp_customize->add_setting('button_text_color', array(
        'default' => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'button_text_color', array(
        'label' => 'Button Text Color',
        'section' => 'colors_styling_section',
        'description' => 'Text color for all buttons (CTA and Timeline buttons)',
    )));
    
    // Custom CSS
    $wp_customize->add_setting('custom_css', array(
        'default' => '',
        'sanitize_callback' => 'wp_strip_all_tags',
    ));
    $wp_customize->add_control('custom_css', array(
        'label' => 'Custom CSS',
        'section' => 'colors_styling_section',
        'type' => 'textarea',
        'description' => 'Add custom CSS to override any styles',
    ));
    
    // Hero Section
    $wp_customize->add_section('hero_section', array(
        'title' => 'Hero Section',
        'priority' => 30,
    ));
    
    // Hero background image
    $wp_customize->add_setting('hero_bg_image', array(
        'default' => get_template_directory_uri() . '/assets/hero-bg.jpg',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_bg_image', array(
        'label' => 'Hero Background Image',
        'section' => 'hero_section',
    )));
    
    // Hero subtitle
    $wp_customize->add_setting('hero_subtitle', array(
        'default' => 'Welcome to Daisho Chemical R&D',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_subtitle', array(
        'label' => 'Hero Subtitle',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    // Hero title parts
    $wp_customize->add_setting('hero_title_highlight', array(
        'default' => 'PIONEERS',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title_highlight', array(
        'label' => 'Hero Title (Yellow Text)',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('hero_title_main', array(
        'default' => 'IN GROUND IMPROVEMENT',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title_main', array(
        'label' => 'Hero Title (Main Text)',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('hero_title_bottom', array(
        'default' => 'TECHNOLOGY',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title_bottom', array(
        'label' => 'Hero Title (Bottom Yellow)',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('hero_cta_text', array(
        'default' => 'LEARN MORE',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_cta_text', array(
        'label' => 'Hero Button Text',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    // Services Section
    $wp_customize->add_section('services_section', array(
        'title' => 'Services Section',
        'priority' => 40,
    ));
    
    $wp_customize->add_setting('services_title', array(
        'default' => 'OUR SERVICES',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('services_title', array(
        'label' => 'Services Title',
        'section' => 'services_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('services_subtitle', array(
        'default' => 'Engineering solutions and chemical additives for ground improvement',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('services_subtitle', array(
        'label' => 'Services Subtitle',
        'section' => 'services_section',
        'type' => 'textarea',
    ));
    
    // Service items
    for ($i = 1; $i <= 3; $i++) {
        // Service icon
        $default_icon = $i == 1 ? '🔧' : ($i == 2 ? '📊' : '💻');
        $wp_customize->add_setting("service{$i}_icon", array(
            'default' => $default_icon,
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("service{$i}_icon", array(
            'label' => "Service {$i} Icon",
            'section' => 'services_section',
            'type' => 'text',
            'description' => 'Enter an emoji or icon character',
        ));
        
        $wp_customize->add_setting("service{$i}_title", array(
            'default' => $i == 1 ? 'Research & Development' : ($i == 2 ? 'Manufacturing & Supply' : 'System Development'),
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("service{$i}_title", array(
            'label' => "Service {$i} Title",
            'section' => 'services_section',
            'type' => 'text',
        ));
        
        $default_desc = $i == 1 ? 'We develop specialized chemical admixtures and ground improvement technologies tailored to diverse soil conditions and construction requirements.' : 
                       ($i == 2 ? 'We supply high-quality chemical solutions, including QP-Flow series admixtures and various grouting materials, to major infrastructure projects.' : 
                        'Utilizing our industry knowledge, we offer software development services tailored to civil engineering and construction operations.');
        
        $wp_customize->add_setting("service{$i}_description", array(
            'default' => $default_desc,
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        $wp_customize->add_control("service{$i}_description", array(
            'label' => "Service {$i} Description",
            'section' => 'services_section',
            'type' => 'textarea',
        ));
    }
    
    // Products Section
    $wp_customize->add_section('products_section', array(
        'title' => 'Products Section',
        'priority' => 50,
    ));
    
    $wp_customize->add_setting('products_title', array(
        'default' => 'OUR PRODUCTS',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('products_title', array(
        'label' => 'Products Title',
        'section' => 'products_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('products_subtitle', array(
        'default' => 'Technical additives and solutions developed by Daisho Chemical R&D',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('products_subtitle', array(
        'label' => 'Products Subtitle',
        'section' => 'products_section',
        'type' => 'textarea',
    ));
    
    // Product items
    $product_defaults = array(
        1 => array(
            'name' => 'S-Chem',
            'description' => 'A dispersant designed for the most challenging clayey soils, including Singapore Marine Clay (SMC). S-Chem not only reduces viscosity but also improves overall soil strength, making construction operations significantly easier and more reliable even in highly viscous conditions.',
            'applications' => 'Soil improvement, shield tunnel invert, backfilling',
            'packaging' => '1m³ container, various drums'
        ),
        2 => array(
            'name' => 'D Retardant',
            'description' => 'Japan\'s most widely recognized soil-cement set retarder, trusted for its consistent performance and competitive cost. D Retardant can delay the setting time of soil-cement mixtures up to 48 hours, ideal for large underground works requiring prolonged fluidity.',
            'applications' => 'Soil-cement columns, track laying, tunneling',
            'packaging' => '1m³ container, 18kg drum'
        ),
        3 => array(
            'name' => 'ACK-I',
            'description' => 'A set-retarding dispersant achieving high fluidity for several hours without compromising strength. Prevents cement paste bleeding and ensures stable ground improvement quality.',
            'applications' => 'Jet grouting, chemical grouting',
            'packaging' => '1m³ container, 18kg drum'
        ),
        4 => array(
            'name' => 'D Fluid',
            'description' => 'High-performance superplasticizer reducing water content in cement milk/mortar. Improves flow and pumpability while maintaining high strength. Perfect for precise ground anchors, shotcrete, and mortar spraying operations.',
            'applications' => 'Ground anchors, shotcrete, mortar spraying',
            'packaging' => '1m³ container, 18kg drum'
        )
    );
    
    for ($i = 1; $i <= 4; $i++) {
        foreach (array('name', 'description', 'applications', 'packaging') as $field) {
            $wp_customize->add_setting("product{$i}_{$field}", array(
                'default' => $product_defaults[$i][$field],
                'sanitize_callback' => $field == 'description' ? 'sanitize_textarea_field' : 'sanitize_text_field',
            ));
            $wp_customize->add_control("product{$i}_{$field}", array(
                'label' => "Product {$i} " . ucfirst($field),
                'section' => 'products_section',
                'type' => $field == 'description' ? 'textarea' : 'text',
            ));
        }
    }
    
    // Timeline Section
    $wp_customize->add_section('timeline_section', array(
        'title' => 'Timeline Section',
        'priority' => 60,
    ));
    
    $wp_customize->add_setting('timeline_title', array(
        'default' => 'TECHNOLOGY TIMELINE',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('timeline_title', array(
        'label' => 'Timeline Title',
        'section' => 'timeline_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('timeline_subtitle', array(
        'default' => 'A history of innovation in jet grouting technology',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('timeline_subtitle', array(
        'label' => 'Timeline Subtitle',
        'section' => 'timeline_section',
        'type' => 'textarea',
    ));
    
    // Timeline items
    $timeline_defaults = array(
        1 => array(
            'year' => '1969',
            'title' => 'High-Pressure Jet Injection Method',
            'description' => 'The Single-Pipe Method was initially developed to overcome the limitations of conventional chemical grouting. High-pressure pumps enabled grout injection and jetting to cut and mix the soil for improved ground strength.',
            'applications' => 'Applications included soil stabilization for excavation, retaining walls, tunnel reinforcement, and more.'
        ),
        2 => array(
            'year' => '1970',
            'title' => 'Development of Jet Mixing Methods',
            'description' => 'The Double-Pipe Method was introduced to expand improvement diameters. It used an inner pipe for grout and an outer pipe for compressed air. Later, the Triple-Pipe Method added water jets, reducing surrounding soil impact and enabling larger improvements.',
            'applications' => ''
        ),
        3 => array(
            'year' => '1993 - 2011',
            'title' => 'Recent Advancements',
            'description' => 'Continuous innovation led to multiple breakthrough technologies:',
            'applications' => ''
        )
    );
    
    for ($i = 1; $i <= 3; $i++) {
        foreach (array('year', 'title', 'description', 'applications') as $field) {
            if ($timeline_defaults[$i][$field] != '') {
                $wp_customize->add_setting("timeline{$i}_{$field}", array(
                    'default' => $timeline_defaults[$i][$field],
                    'sanitize_callback' => 'sanitize_textarea_field',
                ));
                $wp_customize->add_control("timeline{$i}_{$field}", array(
                    'label' => "Timeline {$i} " . ucfirst($field),
                    'section' => 'timeline_section',
                    'type' => $field == 'year' ? 'text' : 'textarea',
                ));
            }
        }
        
        // Timeline circle image
        $wp_customize->add_setting("timeline{$i}_image", array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "timeline{$i}_image", array(
            'label' => "Timeline {$i} Circle Image",
            'section' => 'timeline_section',
            'description' => 'Upload an image to display in the timeline circle (optional)',
        )));
    }
    
    // Timeline 3 items
    for ($i = 1; $i <= 4; $i++) {
        $items = array('1992 - L-Dia Method', '1993 - Super Jet Method', '1994 - Cross Jet Method', '1999 - JEP Method');
        $wp_customize->add_setting("timeline3_item{$i}", array(
            'default' => $items[$i-1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("timeline3_item{$i}", array(
            'label' => "Timeline Item {$i}",
            'section' => 'timeline_section',
            'type' => 'text',
        ));
    }
    
    $wp_customize->add_setting('timeline_cta', array(
        'default' => 'Join Our Innovation!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('timeline_cta', array(
        'label' => 'Timeline CTA Button Text',
        'section' => 'timeline_section',
        'type' => 'text',
    ));
    
    // Contact Section
    $wp_customize->add_section('contact_section', array(
        'title' => 'Contact Section',
        'priority' => 70,
    ));
    
    $wp_customize->add_setting('contact_title', array(
        'default' => 'CONTACT US',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('contact_title', array(
        'label' => 'Contact Title',
        'section' => 'contact_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('contact_subtitle', array(
        'default' => 'We look forward to collaborating with you',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('contact_subtitle', array(
        'label' => 'Contact Subtitle',
        'section' => 'contact_section',
        'type' => 'textarea',
    ));
    
    $wp_customize->add_setting('company_name', array(
        'default' => 'Daisho Chemical R&D',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('company_name', array(
        'label' => 'Company Name',
        'section' => 'contact_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('company_address', array(
        'default' => '5-51-7 Asakusa, Taito-ku, Tokyo',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('company_address', array(
        'label' => 'Company Address',
        'section' => 'contact_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('company_phone', array(
        'default' => '03-6801-6018',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('company_phone', array(
        'label' => 'Company Phone',
        'section' => 'contact_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('company_email', array(
        'default' => 'info@daishokagaku.com',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('company_email', array(
        'label' => 'Company Email',
        'section' => 'contact_section',
        'type' => 'email',
    ));
    
    $wp_customize->add_setting('copyright_text', array(
        'default' => '© Daisho Chemical R&D 2025',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('copyright_text', array(
        'label' => 'Copyright Text',
        'section' => 'contact_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('contact_map_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'contact_map_image', array(
        'label' => 'Map Image',
        'section' => 'contact_section',
        'description' => 'Upload a map image to display in the contact section',
    )));
    
    // Navigation Section
    $wp_customize->add_section('navigation_section', array(
        'title' => 'Navigation',
        'priority' => 20,
    ));
    
    $wp_customize->add_setting('site_logo', array(
        'default' => 'DAISHO CHEMICAL',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('site_logo', array(
        'label' => 'Site Logo Text',
        'section' => 'navigation_section',
        'type' => 'text',
    ));
    
    $nav_items = array('services' => 'SERVICES', 'products' => 'PRODUCTS', 'timeline' => 'TIMELINE', 'contact' => 'CONTACT');
    foreach ($nav_items as $key => $default) {
        $wp_customize->add_setting("nav_{$key}", array(
            'default' => $default,
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("nav_{$key}", array(
            'label' => "Navigation: {$default}",
            'section' => 'navigation_section',
            'type' => 'text',
        ));
    }
    
    $wp_customize->add_setting('language_text', array(
        'default' => 'LANGUAGE',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('language_text', array(
        'label' => 'Language Button Text',
        'section' => 'navigation_section',
        'type' => 'text',
    ));
    
    // AI Chatbot Section
    $wp_customize->add_section('chatbot_section', array(
        'title' => 'AI Chatbot',
        'priority' => 80,
        'description' => 'Configure the AI chatbot widget',
    ));
    
    // Enable/Disable Chatbot
    $wp_customize->add_setting('chatbot_enabled', array(
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ));
    $wp_customize->add_control('chatbot_enabled', array(
        'label' => 'Enable AI Chatbot',
        'section' => 'chatbot_section',
        'type' => 'checkbox',
    ));
    
    // Chatbot Server URL
    $wp_customize->add_setting('chatbot_server_url', array(
        'default' => 'http://localhost:3001',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('chatbot_server_url', array(
        'label' => 'Chat Server URL',
        'section' => 'chatbot_section',
        'type' => 'url',
        'description' => 'URL of your Node.js chat server (e.g., http://your-domain.com:3001)',
    ));
    
    // Agent Avatar Image
    $wp_customize->add_setting('chatbot_agent_image', array(
        'default' => get_template_directory_uri() . '/assets/agent_bubble1.jpg',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'chatbot_agent_image', array(
        'label' => 'Agent Avatar Image',
        'section' => 'chatbot_section',
        'description' => 'Image shown in the chat bubble (square, 200x200px recommended)',
    )));
    
    // Chatbot Primary Color
    $wp_customize->add_setting('chatbot_color', array(
        'default' => '#2563eb',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'chatbot_color', array(
        'label' => 'Chatbot Primary Color',
        'section' => 'chatbot_section',
        'description' => 'Color for chat bubble and messages',
    )));
}
add_action('customize_register', 'daisho_customize_register');

// Enqueue chatbot scripts
function daisho_enqueue_chatbot() {
    // Only load if chatbot is enabled
    if (!get_theme_mod('chatbot_enabled', true)) {
        return;
    }
    
    // Load Socket.IO from CDN
    wp_enqueue_script('socket-io', 'https://cdn.socket.io/4.7.5/socket.io.min.js', array(), '4.7.5', true);
    
    // Load chatbot widget
    wp_enqueue_script('daisho-chatbot', get_template_directory_uri() . '/js/chatbot-widget.js', array('socket-io'), '1.0.0', true);
    
    // Pass configuration to JavaScript
    wp_localize_script('daisho-chatbot', 'daishoChatConfig', array(
        'serverUrl' => get_theme_mod('chatbot_server_url', 'http://localhost:3001'),
        'agentImage' => get_theme_mod('chatbot_agent_image', get_template_directory_uri() . '/assets/agent_bubble1.jpg'),
        'primaryColor' => get_theme_mod('chatbot_color', '#2563eb'),
    ));
}
add_action('wp_enqueue_scripts', 'daisho_enqueue_chatbot');