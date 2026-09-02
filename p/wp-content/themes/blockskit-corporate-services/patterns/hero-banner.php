<?php
/**
 * Title: Hero Banner
 * Slug: blockskit-corporate-services/hero-banner
 * Categories: theme
 * Keywords: hero banner
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-banner-img1.jpg' ) ); ?>","id":349,"dimRatio":80,"overlayColor":"foreground","isUserOverlayColor":true,"minHeight":700,"style":{"spacing":{"blockGap":"var:preset|spacing|small","padding":{"right":"var:preset|spacing|x-small","left":"var:preset|spacing|x-small","top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="padding-top:0;padding-right:var(--wp--preset--spacing--x-small);padding-bottom:0;padding-left:var(--wp--preset--spacing--x-small);min-height:700px"><span aria-hidden="true" class="wp-block-cover__background has-foreground-background-color has-background-dim-80 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-349" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-banner-img1.jpg' ) ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"700","lineHeight":"1.1"}},"fontSize":"xxxx-large"} -->
<h2 class="wp-block-heading has-text-align-center has-xxxx-large-font-size" style="font-style:normal;font-weight:700;line-height:1.1"><?php esc_html_e( 'Best Optimal Corporate', 'blockskit-corporate-services' ); ?><br><?php esc_html_e( 'Business Solutions', 'blockskit-corporate-services' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
<p class="has-text-align-center has-medium-font-size"><?php esc_html_e( 'Per vivamus excepteur non temporibus qui. Officiis magnis, cum magna blanditiis', 'blockskit-corporate-services' ); ?><br><?php esc_html_e( 'pharetra, sagittis, quisque sociosqu voluptatibus, imperdiet blandit.', 'blockskit-corporate-services' ); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|large"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--large)"><!-- wp:button {"style":{"spacing":{"padding":{"left":"var:preset|spacing|medium","right":"var:preset|spacing|medium","top":"var:preset|spacing|x-small","bottom":"var:preset|spacing|x-small"}},"border":{"radius":"5px"}},"fontSize":"x-small"} -->
<div class="wp-block-button has-custom-font-size has-x-small-font-size"><a class="wp-block-button__link wp-element-button" style="border-radius:5px;padding-top:var(--wp--preset--spacing--x-small);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--x-small);padding-left:var(--wp--preset--spacing--medium)"><?php esc_html_e( 'LEARN MORE', 'blockskit-corporate-services' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->