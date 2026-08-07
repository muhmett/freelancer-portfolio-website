<?php
/**
 * Template Name: Bannières display
 *
 * La page du second savoir-faire : le portfolio de campagnes et la
 * démonstration multi-formats.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wrap page-head">
	<p class="eyebrow rv"><?php jmk_e( 'page_banners_eyebrow' ); ?></p>
	<h1 class="rv"><?php jmk_e( 'nav_banners' ); ?></h1>
	<p class="lede rv"><?php jmk_e( 'page_banners_lede' ); ?></p>
</div>

<?php
get_template_part( 'template-parts/section', 'banner-work' );
get_template_part( 'template-parts/section', 'banners' );
if ( jmk_get( 'sec_process' ) ) {
	get_template_part( 'template-parts/section', 'process' );
}
get_template_part( 'template-parts/section', 'cta' );

get_footer();
