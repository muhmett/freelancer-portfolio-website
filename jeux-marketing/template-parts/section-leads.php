<?php
/**
 * Capture d'email et aperçu du tableau de participants.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="leads">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'leads_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'leads_h2_a' ); ?>
				<span class="accent-text"><?php jmk_e( 'leads_h2_b' ); ?></span></h2>
			<p class="lede"><?php jmk_e( 'leads_lede' ); ?></p>
		</div>
		<div class="two rv">
			<?php get_template_part( 'template-parts/form', 'lead' ); ?>
			<div>
				<div class="sheet">
					<div class="sheet-bar"><span class="led"></span>
						<?php jmk_e( 'sheet_bar' ); ?></div>
					<div class="tbl-scroll">
						<table>
							<thead>
								<tr>
									<th><?php jmk_e( 'th_time' ); ?></th>
									<th><?php jmk_e( 'first_name' ); ?></th>
									<th><?php jmk_e( 'email' ); ?></th>
									<th><?php jmk_e( 'th_prize' ); ?></th>
									<th><?php jmk_e( 'th_code' ); ?></th>
								</tr>
							</thead>
							<tbody id="sheetBody">
								<tr><td colspan="5" class="sheet-empty"><?php jmk_e( 'sheet_empty' ); ?></td></tr>
							</tbody>
						</table>
					</div>
				</div>
				<p class="small mt-13"><?php jmk_e( 'leads_note' ); ?></p>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
