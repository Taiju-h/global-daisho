<?php get_header(); ?>

<!-- Hero Section -->
<section id="home" class="hero" style="background-image: url('<?php echo get_theme_mod('hero_bg_image', get_template_directory_uri() . '/assets/hero-bg.jpg'); ?>');">
    <div class="container">
        <div class="hero-content">
            <p class="hero-subtitle"><?php echo get_theme_mod('hero_subtitle', 'Welcome to Daisho Chemical R&D'); ?></p>
            <h1 class="hero-title">
                <span class="highlight"><?php echo get_theme_mod('hero_title_highlight', 'PIONEERS'); ?></span> <?php echo get_theme_mod('hero_title_main', 'IN GROUND IMPROVEMENT'); ?><br>
                <span class="highlight"><?php echo get_theme_mod('hero_title_bottom', 'TECHNOLOGY'); ?></span>
            </h1>
            <a href="#services" class="cta-button"><?php echo get_theme_mod('hero_cta_text', 'LEARN MORE'); ?></a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="section services">
    <div class="container">
        <h2 class="section-title"><?php echo get_theme_mod('services_title', 'OUR SERVICES'); ?></h2>
        <p class="section-subtitle"><?php echo get_theme_mod('services_subtitle', 'Engineering solutions and chemical additives for ground improvement'); ?></p>
        <div class="services-grid">
            <!-- Service 1 -->
            <div class="service-item">
                <div class="service-icon"><?php echo get_theme_mod('service1_icon', '🔧'); ?></div>
                <h3 class="service-title"><?php echo get_theme_mod('service1_title', 'Research & Development'); ?></h3>
                <p class="service-description"><?php echo get_theme_mod('service1_description', 'We develop specialized chemical admixtures and ground improvement technologies tailored to diverse soil conditions and construction requirements.'); ?></p>
            </div>
            <!-- Service 2 -->
            <div class="service-item">
                <div class="service-icon"><?php echo get_theme_mod('service2_icon', '📊'); ?></div>
                <h3 class="service-title"><?php echo get_theme_mod('service2_title', 'Manufacturing & Supply'); ?></h3>
                <p class="service-description"><?php echo get_theme_mod('service2_description', 'We supply high-quality chemical solutions, including QP-Flow series admixtures and various grouting materials, to major infrastructure projects.'); ?></p>
            </div>
            <!-- Service 3 -->
            <div class="service-item">
                <div class="service-icon"><?php echo get_theme_mod('service3_icon', '💻'); ?></div>
                <h3 class="service-title"><?php echo get_theme_mod('service3_title', 'System Development'); ?></h3>
                <p class="service-description"><?php echo get_theme_mod('service3_description', 'Utilizing our industry knowledge, we offer software development services tailored to civil engineering and construction operations.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="section">
    <div class="container">
        <h2 class="section-title"><?php echo get_theme_mod('products_title', 'OUR PRODUCTS'); ?></h2>
        <p class="section-subtitle"><?php echo get_theme_mod('products_subtitle', 'Technical additives and solutions developed by Daisho Chemical R&D'); ?></p>
        <div class="products-grid">
            <!-- Product 1 -->
            <div class="product-item">
                <h3 class="product-title"><?php echo get_theme_mod('product1_name', 'S-Chem'); ?></h3>
                <p class="product-description"><?php echo get_theme_mod('product1_description', 'A dispersant designed for the most challenging clayey soils, including Singapore Marine Clay (SMC). S-Chem not only reduces viscosity but also improves overall soil strength, making construction operations significantly easier and more reliable even in highly viscous conditions.'); ?></p>
                <div class="product-details">
                    <div class="product-detail"><strong>Applications:</strong> <?php echo get_theme_mod('product1_applications', 'Soil improvement, shield tunnel invert, backfilling'); ?></div>
                    <div class="product-detail"><strong>Packaging:</strong> <?php echo get_theme_mod('product1_packaging', '1m³ container, various drums'); ?></div>
                </div>
            </div>
            <!-- Product 2 -->
            <div class="product-item">
                <h3 class="product-title"><?php echo get_theme_mod('product2_name', 'D Retardant'); ?></h3>
                <p class="product-description"><?php echo get_theme_mod('product2_description', 'Japan\'s most widely recognized soil-cement set retarder, trusted for its consistent performance and competitive cost. D Retardant can delay the setting time of soil-cement mixtures up to 48 hours, ideal for large underground works requiring prolonged fluidity.'); ?></p>
                <div class="product-details">
                    <div class="product-detail"><strong>Applications:</strong> <?php echo get_theme_mod('product2_applications', 'Soil-cement columns, track laying, tunneling'); ?></div>
                    <div class="product-detail"><strong>Packaging:</strong> <?php echo get_theme_mod('product2_packaging', '1m³ container, 18kg drum'); ?></div>
                </div>
            </div>
            <!-- Product 3 -->
            <div class="product-item">
                <h3 class="product-title"><?php echo get_theme_mod('product3_name', 'ACK-I'); ?></h3>
                <p class="product-description"><?php echo get_theme_mod('product3_description', 'A set-retarding dispersant achieving high fluidity for several hours without compromising strength. Prevents cement paste bleeding and ensures stable ground improvement quality.'); ?></p>
                <div class="product-details">
                    <div class="product-detail"><strong>Applications:</strong> <?php echo get_theme_mod('product3_applications', 'Jet grouting, chemical grouting'); ?></div>
                    <div class="product-detail"><strong>Packaging:</strong> <?php echo get_theme_mod('product3_packaging', '1m³ container, 18kg drum'); ?></div>
                </div>
            </div>
            <!-- Product 4 -->
            <div class="product-item">
                <h3 class="product-title"><?php echo get_theme_mod('product4_name', 'D Fluid'); ?></h3>
                <p class="product-description"><?php echo get_theme_mod('product4_description', 'High-performance superplasticizer reducing water content in cement milk/mortar. Improves flow and pumpability while maintaining high strength. Perfect for precise ground anchors, shotcrete, and mortar spraying operations.'); ?></p>
                <div class="product-details">
                    <div class="product-detail"><strong>Applications:</strong> <?php echo get_theme_mod('product4_applications', 'Ground anchors, shotcrete, mortar spraying'); ?></div>
                    <div class="product-detail"><strong>Packaging:</strong> <?php echo get_theme_mod('product4_packaging', '1m³ container, 18kg drum'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section id="timeline" class="section timeline">
    <div class="container">
        <h2 class="section-title"><?php echo get_theme_mod('timeline_title', 'TECHNOLOGY TIMELINE'); ?></h2>
        <p class="section-subtitle"><?php echo get_theme_mod('timeline_subtitle', 'A history of innovation in jet grouting technology'); ?></p>
        <div class="timeline-container">
            <div class="timeline-line"></div>
            
            <!-- Timeline Item 1 -->
            <div class="timeline-item">
                <?php $timeline1_img = get_theme_mod('timeline1_image'); ?>
                <div class="timeline-year<?php echo $timeline1_img ? ' has-image' : ''; ?>" <?php if($timeline1_img): ?>style="background-image: url('<?php echo esc_url($timeline1_img); ?>') !important;"<?php endif; ?>>
                    <span class="timeline-year-text"><?php echo get_theme_mod('timeline1_year', '1969'); ?></span>
                </div>
                <div class="timeline-content">
                    <h3 class="timeline-title"><?php echo get_theme_mod('timeline1_title', 'High-Pressure Jet Injection Method'); ?></h3>
                    <p class="timeline-description"><?php echo get_theme_mod('timeline1_description', 'The Single-Pipe Method was initially developed to overcome the limitations of conventional chemical grouting. High-pressure pumps enabled grout injection and jetting to cut and mix the soil for improved ground strength.'); ?></p>
                    <p class="timeline-description" style="color: #888; font-size: 14px;"><?php echo get_theme_mod('timeline1_applications', 'Applications included soil stabilization for excavation, retaining walls, tunnel reinforcement, and more.'); ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 2 -->
            <div class="timeline-item">
                <?php $timeline2_img = get_theme_mod('timeline2_image'); ?>
                <div class="timeline-year<?php echo $timeline2_img ? ' has-image' : ''; ?>" <?php if($timeline2_img): ?>style="background-image: url('<?php echo esc_url($timeline2_img); ?>') !important;"<?php endif; ?>>
                    <span class="timeline-year-text"><?php echo get_theme_mod('timeline2_year', '1970'); ?></span>
                </div>
                <div class="timeline-content">
                    <h3 class="timeline-title"><?php echo get_theme_mod('timeline2_title', 'Development of Jet Mixing Methods'); ?></h3>
                    <p class="timeline-description"><?php echo get_theme_mod('timeline2_description', 'The Double-Pipe Method was introduced to expand improvement diameters. It used an inner pipe for grout and an outer pipe for compressed air. Later, the Triple-Pipe Method added water jets, reducing surrounding soil impact and enabling larger improvements.'); ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 3 -->
            <div class="timeline-item">
                <?php $timeline3_img = get_theme_mod('timeline3_image'); ?>
                <div class="timeline-year<?php echo $timeline3_img ? ' has-image' : ''; ?>" <?php if($timeline3_img): ?>style="background-image: url('<?php echo esc_url($timeline3_img); ?>') !important;"<?php endif; ?>>
                    <span class="timeline-year-text"><?php echo get_theme_mod('timeline3_year', '1993 - 2011'); ?></span>
                </div>
                <div class="timeline-content">
                    <h3 class="timeline-title"><?php echo get_theme_mod('timeline3_title', 'Recent Advancements'); ?></h3>
                    <p class="timeline-description"><?php echo get_theme_mod('timeline3_description', 'Continuous innovation led to multiple breakthrough technologies:'); ?></p>
                    <ul class="timeline-list">
                        <li><?php echo get_theme_mod('timeline3_item1', '1992 - L-Dia Method'); ?></li>
                        <li><?php echo get_theme_mod('timeline3_item2', '1993 - Super Jet Method'); ?></li>
                        <li><?php echo get_theme_mod('timeline3_item3', '1994 - Cross Jet Method'); ?></li>
                        <li><?php echo get_theme_mod('timeline3_item4', '1999 - JEP Method'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 50px;">
            <a href="#contact" class="join-btn"><?php echo get_theme_mod('timeline_cta', 'Join Our Innovation!'); ?></a>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section contact">
    <?php if(get_theme_mod('contact_map_image')): ?>
    <div class="contact-map">
        <img src="<?php echo esc_url(get_theme_mod('contact_map_image')); ?>" alt="Location Map">
    </div>
    <?php endif; ?>
    
    <div class="container">
        <h2 class="section-title"><?php echo get_theme_mod('contact_title', 'CONTACT US'); ?></h2>
        <p class="section-subtitle"><?php echo get_theme_mod('contact_subtitle', 'We look forward to collaborating with you'); ?></p>
        <div class="contact-info">
            <div class="company-name"><?php echo get_theme_mod('company_name', 'Daisho Chemical R&D'); ?></div>
            <div class="contact-detail"><?php echo get_theme_mod('company_address', '5-51-7 Asakusa, Taito-ku, Tokyo'); ?></div>
            <div class="contact-detail">Phone: <a href="tel:<?php echo get_theme_mod('company_phone', '03-6801-6018'); ?>"><?php echo get_theme_mod('company_phone', '03-6801-6018'); ?></a></div>
            <div class="contact-detail">Email: <a href="mailto:<?php echo get_theme_mod('company_email', 'info@daishokagaku.com'); ?>"><?php echo get_theme_mod('company_email', 'info@daishokagaku.com'); ?></a></div>
        </div>
        
        <div class="copyright">
            <?php echo get_theme_mod('copyright_text', ' Daisho Chemical R&D 2025'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>