<?php
/**
 * Title: Latest Posts
 * Slug: blockskit-corporate-services/latest-posts
 * Categories: theme
 * Keywords: blog posts
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"margin":{"top":"var:preset|spacing|xx-large","bottom":"var:preset|spacing|xx-large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="margin-top:var(--wp--preset--spacing--xx-large);margin-bottom:var(--wp--preset--spacing--xx-large)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:separator {"className":"is-style-wide","style":{"layout":{"selfStretch":"fixed","flexSize":"70px"}},"backgroundColor":"primary"} -->
<hr class="wp-block-separator has-text-color has-primary-color has-alpha-channel-opacity has-primary-background-color has-background is-style-wide"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":6,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"x-small"} -->
<h6 class="wp-block-heading has-primary-color has-text-color has-link-color has-x-small-font-size"><?php esc_html_e( 'INSIGHTS', 'blockskit-corporate-services' ); ?></h6>
<!-- /wp:heading -->

<!-- wp:separator {"className":"is-style-wide","style":{"layout":{"selfStretch":"fixed","flexSize":"70px"}},"backgroundColor":"primary"} -->
<hr class="wp-block-separator has-text-color has-primary-color has-alpha-channel-opacity has-primary-background-color has-background is-style-wide"/>
<!-- /wp:separator --></div>
<!-- /wp:group -->

<!-- wp:heading {"textAlign":"center","style":{"typography":{"lineHeight":1.1,"letterSpacing":"-0.03em"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="letter-spacing:-0.03em;line-height:1.1"><?php esc_html_e( 'Latest from the blog', 'blockskit-corporate-services' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|small"}}}} -->
<p class="has-text-align-center" style="padding-bottom:var(--wp--preset--spacing--small)"><?php esc_html_e( 'Elementum quia fugit cum euismod, varius hymenaeos.', 'blockskit-corporate-services' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:query {"queryId":42,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|large"}},"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small","padding":{"top":"var:preset|spacing|xx-small","bottom":"0","right":"0"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--xx-small);padding-right:0;padding-bottom:0"><!-- wp:post-featured-image {"isLink":true,"width":"","height":"250px","style":{"border":{"radius":"10px"}}} /-->

<!-- wp:post-terms {"term":"category","className":"is-style-swt-post-terms-pill","style":{"typography":{"fontStyle":"normal","fontWeight":"500"},"spacing":{"padding":{"top":"var:preset|spacing|x-small"}},"color":{"text":"#33c6a7"}},"fontSize":"x-small"} /-->

<!-- wp:post-title {"level":5,"isLink":true,"style":{"spacing":{"margin":{"top":"var:preset|spacing|xx-small","bottom":"var:preset|spacing|xx-small"}},"typography":{"letterSpacing":"-0.03em","lineHeight":"1.3"}}} /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:post-author {"avatarSize":24,"showAvatar":false,"fontSize":"x-small"} /-->

<!-- wp:paragraph {"fontSize":"x-small"} -->
<p class="has-x-small-font-size">·</p>
<!-- /wp:paragraph -->

<!-- wp:post-date {"format":"M j, Y","fontSize":"x-small"} /--></div>
<!-- /wp:group -->

<!-- wp:post-excerpt {"style":{"spacing":{"margin":{"top":"var:preset|spacing|xx-small"}}}} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","placeholder":"Add text or blocks that will display when a query returns no results."} -->
<p class="has-text-align-center"></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->