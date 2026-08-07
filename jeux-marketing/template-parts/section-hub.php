<?php
/**
 * Le carrefour : un savoir-faire, une carte, une page.
 *
 * La vignette de chaque carte n'est pas une image mais la démonstration en
 * miniature — la roue tourne, la bannière défile. C'est le parti pris du
 * thème depuis le début : ce qui se montre n'a pas à se raconter.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmk_skills = jmk_skills_hub();
if ( ! $jmk_skills ) {
	return;
}
?>
<section id="savoir-faire">
	<div class="wrap">
		<div class="hub-grid">
			<?php foreach ( $jmk_skills as $jmk_s ) : ?>
				<article class="hub-card rv" data-kind="<?php echo esc_attr( $jmk_s['kind'] ); ?>">

					<div class="hub-demo" data-demo="<?php echo esc_attr( $jmk_s['kind'] ); ?>"></div>

					<div class="hub-body">
						<p class="eyebrow"><?php echo esc_html( $jmk_s['eyebrow'] ); ?></p>
						<h2><?php echo esc_html( $jmk_s['title'] ); ?></h2>
						<p class="hub-text"><?php echo esc_html( $jmk_s['text'] ); ?></p>

						<ul class="hub-points">
							<?php foreach ( $jmk_s['points'] as $jmk_p ) : ?>
								<li><?php echo esc_html( $jmk_p ); ?></li>
							<?php endforeach; ?>
						</ul>

						<a class="btn" href="<?php echo esc_url( jmk_page_url( $jmk_s['key'] ) ); ?>">
							<?php echo esc_html( $jmk_s['cta'] ); ?>
						</a>
					</div>

				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
