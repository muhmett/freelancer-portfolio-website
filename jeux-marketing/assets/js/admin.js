/* Jeux Marketing — script de l'administration */
( function () {
	'use strict';

	/* ── onglets ── */
	var tabs  = Array.prototype.slice.call( document.querySelectorAll( '.jmk-tab' ) );
	var panes = Array.prototype.slice.call( document.querySelectorAll( '.jmk-pane' ) );

	function show( id ) {
		tabs.forEach( function ( t ) { t.classList.toggle( 'active', t.dataset.tab === id ); } );
		panes.forEach( function ( p ) { p.classList.toggle( 'active', p.dataset.pane === id ); } );
		try { window.localStorage.setItem( 'jmkTab', id ); } catch ( e ) {}
	}
	tabs.forEach( function ( t ) {
		t.addEventListener( 'click', function () { show( t.dataset.tab ); } );
	} );
	var saved = null;
	try { saved = window.localStorage.getItem( 'jmkTab' ); } catch ( e ) {}
	show( ( saved && document.querySelector( '.jmk-pane[data-pane="' + saved + '"]' ) ) ? saved : 'general' );

	/* ── gabarits de nouvelle ligne ── */
	function nextIndex( container ) {
		var rows = container.children.length, max = -1;
		Array.prototype.forEach.call( container.querySelectorAll( '[name]' ), function ( el ) {
			var m = el.name.match( /\[(\d+)\]/ );
			if ( m ) { max = Math.max( max, parseInt( m[ 1 ], 10 ) ); }
		} );
		return Math.max( rows, max + 1 );
	}

	var TPL = {
		lots: function ( i ) {
			return '<div class="jmk-lot">' +
				'<input type="text" name="jmk_settings[lots][' + i + '][label]" placeholder="Nom du lot">' +
				'<input type="number" step="0.1" min="0" class="jmk-weight" name="jmk_settings[lots][' + i + '][weight]" value="10">' +
				'<input type="number" min="0" name="jmk_settings[lots][' + i + '][cap]" value="0">' +
				'<input type="text" name="jmk_settings[lots][' + i + '][code]" placeholder="PROMO10">' +
				'<input type="number" step="1" min="-180" max="180" name="jmk_settings[lots][' + i + '][hue]" value="0">' +
				'<label class="jmk-mini"><input type="checkbox" name="jmk_settings[lots][' + i + '][losing]" value="1"></label>' +
				'<button type="button" class="jmk-del">×</button></div>';
		},
		options: function ( i ) {
			return '<div class="jmk-opt">' +
				'<input type="text" name="jmk_settings[options][' + i + '][label]" placeholder="Intitulé de l\'option">' +
				'<input type="number" step="1" min="0" name="jmk_settings[options][' + i + '][price]" value="20">' +
				'<input type="number" step="1" min="0" name="jmk_settings[options][' + i + '][days]" value="0">' +
				'<label class="jmk-mini"><input type="checkbox" name="jmk_settings[options][' + i + '][on]" value="1"></label>' +
				'<label class="jmk-mini"><input type="checkbox" name="jmk_settings[options][' + i + '][fixed]" value="1"></label>' +
				'<button type="button" class="jmk-del">×</button></div>';
		},
		quiz: function ( i ) {
			return '<div class="jmk-row"><span class="jmk-handle">≡</span><div class="jmk-row-body">' +
				'<input type="text" name="jmk_settings[quiz][' + i + '][q]" placeholder="Question">' +
				'<textarea rows="3" name="jmk_settings[quiz][' + i + '][a]" placeholder="Une réponse par ligne"></textarea>' +
				'<label class="jmk-inline">Bonne réponse n° <input type="number" min="1" name="jmk_settings[quiz][' + i + '][c]" value="1"></label>' +
				'</div><button type="button" class="jmk-del">×</button></div>';
		},
		faq: function ( i ) {
			return '<div class="jmk-row"><span class="jmk-handle">≡</span><div class="jmk-row-body">' +
				'<input type="text" name="jmk_settings[faq][' + i + '][q]" placeholder="Question">' +
				'<textarea rows="3" name="jmk_settings[faq][' + i + '][a]" placeholder="Réponse"></textarea>' +
				'</div><button type="button" class="jmk-del">×</button></div>';
		},
		specs: function ( i ) {
			return '<div class="jmk-row"><span class="jmk-handle">≡</span><div class="jmk-row-body">' +
				'<input type="text" name="jmk_settings[specs][' + i + '][q]" placeholder="Titre">' +
				'<textarea rows="3" name="jmk_settings[specs][' + i + '][a]" placeholder="Description"></textarea>' +
				'</div><button type="button" class="jmk-del">×</button></div>';
		}
	};

	document.addEventListener( 'click', function ( e ) {
		var add = e.target.closest( '.jmk-add' );
		if ( add ) {
			var key  = add.dataset.add,
				host = document.querySelector( '.jmk-rep[data-rep="' + key + '"]' );
			if ( host && TPL[ key ] ) {
				host.insertAdjacentHTML( 'beforeend', TPL[ key ]( nextIndex( host ) ) );
				shares();
			}
			return;
		}
		var del = e.target.closest( '.jmk-del' );
		if ( del ) {
			var row = del.closest( '.jmk-lot, .jmk-opt, .jmk-row' );
			if ( row && window.confirm( 'Supprimer cette ligne ?' ) ) {
				row.parentNode.removeChild( row );
				shares();
			}
		}
	} );

	/* ── aperçu des probabilités réelles ── */
	function shares() {
		var host = document.getElementById( 'jmk-shares' );
		if ( ! host ) { return; }
		var rows  = Array.prototype.slice.call( document.querySelectorAll( '.jmk-lots .jmk-lot' ) );
		var total = 0;
		var data  = rows.map( function ( r ) {
			var label  = r.querySelector( '[name*="[label]"]' ),
				weight = r.querySelector( '[name*="[weight]"]' ),
				cap    = r.querySelector( '[name*="[cap]"]' ),
				losing = r.querySelector( '[name*="[losing]"]' ),
				w      = parseFloat( weight ? weight.value : 0 ) || 0;
			total += w;
			return {
				label: ( label && label.value ) ? label.value : '(sans nom)',
				w: w,
				cap: cap ? parseInt( cap.value, 10 ) || 0 : 0,
				losing: !! ( losing && losing.checked )
			};
		} );

		host.innerHTML = data.map( function ( d ) {
			var pct = total ? ( d.w / total ) * 100 : 0;
			return '<div class="jmk-share' + ( d.losing ? ' jmk-losing' : '' ) + '">' +
				'<span>' + d.label.replace( /[<>]/g, '' ) + ( d.cap ? ' (max ' + d.cap + ')' : '' ) + '</span>' +
				'<span class="jmk-share-track"><span class="jmk-share-fill" style="width:' + pct.toFixed( 1 ) + '%"></span></span>' +
				'<span class="jmk-share-num">' + pct.toFixed( 1 ) + ' %</span></div>';
		} ).join( '' );

		if ( ! total ) {
			host.innerHTML = '<p class="jmk-help">Renseignez au moins un poids supérieur à zéro.</p>';
		}
	}

	document.addEventListener( 'input', function ( e ) {
		if ( e.target.closest( '.jmk-lots' ) ) { shares(); }
	} );
	document.addEventListener( 'change', function ( e ) {
		if ( e.target.closest( '.jmk-lots' ) ) { shares(); }
	} );
	shares();
} )();
