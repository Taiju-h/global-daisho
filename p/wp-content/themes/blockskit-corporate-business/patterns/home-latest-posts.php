<?php
/**
 * Title: Latest Posts
 * Slug: blockskit-corporate-business/home-latest-posts
 * Categories: theme
 * Keywords: posts
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|x-small","left":"var:preset|spacing|x-small"},"margin":{"top":"100px","bottom":"100px"},"blockGap":"var:preset|spacing|x-large"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="margin-top:100px;margin-bottom:100px;padding-right:var(--wp--preset--spacing--x-small);padding-left:var(--wp--preset--spacing--x-small)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"var:preset|color|highlight"}}}},"textColor":"highlight","fontSize":"x-small","fontFamily":"body"} -->
<h6 class="wp-block-heading has-text-align-center has-highlight-color has-text-color has-link-color has-body-font-family has-x-small-font-size"><?php esc_html_e( 'LATEST BLOGS', 'blockskit-corporate-business' ); ?></h6>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"lineHeight":"1.1","fontStyle":"normal","fontWeight":"700"}},"fontSize":"xxx-large","fontFamily":"body"} -->
<h3 class="wp-block-heading has-text-align-center has-body-font-family has-xxx-large-font-size" style="font-style:normal;font-weight:700;line-height:1.1"><?php esc_html_e( 'Our Insights &amp; Blogs', 'blockskit-corporate-business' ); ?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|medium"}},"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"},"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} /-->

<!-- wp:group {"className":"is-style-bk-box-shadow","style":{"spacing":{"padding":{"top":"var:preset|spacing|medium","bottom":"var:preset|spacing|small","left":"var:preset|spacing|medium","right":"var:preset|spacing|medium"}}},"backgroundColor":"accent-text","layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-group is-style-bk-box-shadow has-accent-text-background-color has-background" style="padding-top:var(--wp--preset--spacing--medium);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--small);padding-left:var(--wp--preset--spacing--medium)"><!-- wp:post-title {"isLink":true,"style":{"spacing":{"padding":{"top":"0","bottom":"0"},"margin":{"bottom":"var:preset|spacing|x-small"}},"typography":{"letterSpacing":"0px","fontStyle":"normal","fontWeight":"600"}},"fontSize":"large"} /-->

<!-- wp:post-date {"isLink":true,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|xx-small"}}},"fontSize":"x-small"} /-->

<!-- wp:post-excerpt {"moreText":"LEARN MORE","excerptLength":15,"className":"link-no-underline","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"textDecoration":"none"},"spacing":{"margin":{"bottom":"var:preset|spacing|x-small"}}},"fontSize":"small"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->