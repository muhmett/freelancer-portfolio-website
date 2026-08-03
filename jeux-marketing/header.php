<?php
/**
 * En-tête du site.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<canvas id="jmk-confetti" aria-hidden="true"></canvas>

<a class="jmk-skip" href="#jmk-main"><?php esc_html_e( 'Aller au contenu', 'jeux-marketing' ); ?></a>

<header class="topbar">
	<div class="wrap">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="dot" aria-hidden="true"></span>
				<span id="brandLabel"><?php
					$bn = jmk_get( 'brand_name' );
					echo esc_html( $bn ? $bn : get_bloginfo( 'name' ) );
				?></span>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="topnav" aria-label="<?php esc_attr_e( 'Navigation principale', 'jeux-marketing' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php elseif ( is_front_page() ) : ?>
			<nav class="topnav" aria-label="<?php esc_attr_e( 'Navigation principale', 'jeux-marketing' ); ?>">
				<?php if ( jmk_get( 'sec_brand' ) ) : ?><a href="#marque"><?php esc_html_e( 'Vos couleurs', 'jeux-marketing' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_lab' ) ) : ?><a href="#labo"><?php esc_html_e( 'Probabilités', 'jeux-marketing' ); ?></a><?php endif; ?>
				<a href="#autres"><?php esc_html_e( 'Jeux', 'jeux-marketing' ); ?></a>
				<?php if ( jmk_get( 'sec_roi' ) ) : ?><a href="#roi"><?php esc_html_e( 'Rentabilité', 'jeux-marketing' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_quote' ) ) : ?><a href="#devis"><?php esc_html_e( 'Devis', 'jeux-marketing' ); ?></a><?php endif; ?>
			</nav>
		<?php endif; ?>

		<button class="btn btn-ghost" id="topCta"><?php esc_html_e( 'Me contacter', 'jeux-marketing' ); ?></button>
	</div>
	<div class="progress" id="prog" aria-hidden="true"></div>
</header>

<main id="jmk-main">
