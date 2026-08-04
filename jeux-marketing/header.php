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
<html lang="<?php echo esc_attr( jmk_langs()[ jmk_lang() ]['html'] ); ?>" dir="<?php echo esc_attr( jmk_dir() ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="aurora" aria-hidden="true"><i></i><i></i><i></i></div>

<canvas id="jmk-confetti" aria-hidden="true"></canvas>

<a class="jmk-skip" href="#jmk-main"><?php jmk_e( 'skip' ); ?></a>

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
			<nav class="topnav" aria-label="<?php echo esc_attr( jmk_t( 'nav_main' ) ); ?>">
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
			<nav class="topnav" aria-label="<?php echo esc_attr( jmk_t( 'nav_main' ) ); ?>">
				<?php if ( jmk_get( 'sec_services' ) ) : ?><a href="#services"><?php jmk_e( 'nav_prices' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_work' ) ) : ?><a href="#realisations"><?php jmk_e( 'nav_work' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_skills' ) ) : ?><a href="#competences"><?php jmk_e( 'nav_skills' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_brand' ) ) : ?><a href="#marque"><?php jmk_e( 'nav_brand' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_lab' ) ) : ?><a href="#labo"><?php jmk_e( 'nav_lab' ); ?></a><?php endif; ?>
				<a href="#autres"><?php jmk_e( 'nav_games' ); ?></a>
				<?php if ( jmk_get( 'sec_roi' ) ) : ?><a href="#roi"><?php jmk_e( 'nav_roi' ); ?></a><?php endif; ?>
				<?php if ( jmk_get( 'sec_quote' ) ) : ?><a href="#devis"><?php jmk_e( 'nav_quote' ); ?></a><?php endif; ?>
			</nav>
		<?php endif; ?>

		<div class="topbar-end">
			<?php jmk_lang_switcher(); ?>
			<button class="btn btn-ghost" id="topCta"><?php jmk_e( 'contact' ); ?></button>
		</div>
	</div>
	<div class="progress" id="prog" aria-hidden="true"></div>
</header>

<main id="jmk-main">
