/* Jeux Marketing — créateur de jeu.
 *
 * Un seul état, `cfg`. Tout ce qui est saisi le modifie, et tout ce qui est
 * affiché en découle : aperçu, code d'intégration, champs cachés des deux
 * formulaires d'export. Rien n'est recalculé à deux endroits.
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof window.JMKB ) {
		return;
	}

	var B   = window.JMKB;
	var T   = B.i18n;
	var cfg = JSON.parse( JSON.stringify( B.config ) );

	function $( s )  { return document.querySelector( s ); }
	function $$( s ) { return Array.prototype.slice.call( document.querySelectorAll( s ) ); }
	function esc( s ) {
		return String( s ).replace( /[<>&"]/g, function ( c ) {
			return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[ c ];
		} );
	}

	/* ─────────────────────────────── aperçu ─────────────────────────────── */

	var preview = $( '#jmkPreview' );
	var timer   = null;

	/**
	 * Le squelette de l'aperçu.
	 *
	 * Volontairement identique à celui que produit jmk_builder_html() côté
	 * PHP : une div, le moteur, un appel à mount(). Ce que le client voit ici
	 * est ce qu'il téléchargera.
	 *
	 * @return {string}
	 */
	function skeleton() {
		return '<!doctype html><html dir="' + esc( cfg.dir ) + '"><head><meta charset="utf-8">' +
			'<meta name="viewport" content="width=device-width,initial-scale=1">' +
			'<style>body{margin:0;padding:22px 14px;background:#0E0916;' +
			'font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}</style></head>' +
			'<body><div id="jeu"></div>' +
			'<script src="' + esc( B.engine ) + '"><\/script>' +
			'<script>window.addEventListener("load",function(){' +
			'JMKGame.mount("#jeu",' + JSON.stringify( cfg ) + ');});<\/script>' +
			'</body></html>';
	}

	function refreshPreview() {
		clearTimeout( timer );
		timer = setTimeout( function () {
			preview.srcdoc = skeleton();
		}, 260 );
	}

	/* ─────────────────────── code d'intégration ─────────────────────────── */

	function embedCode() {
		var attrs = [
			'  data-jeu="' + cfg.game + '"',
			'  data-couleur="' + cfg.accent.toUpperCase() + '"',
			'  data-style="' + cfg.skin + '"'
		];
		if ( cfg.brand )    { attrs.push( '  data-marque="' + cfg.brand + '"' ); }
		if ( 'rtl' === cfg.dir ) { attrs.push( '  data-langue="ar"' ); }
		if ( cfg.drawUrl )  { attrs.push( '  data-tirage="' + cfg.drawUrl + '"' ); }
		if ( cfg.leadUrl )  { attrs.push( '  data-webhook="' + cfg.leadUrl + '"' ); }
		if ( cfg.form.privacy ) { attrs.push( '  data-rgpd="' + cfg.form.privacy + '"' ); }
		attrs.push( "  data-lots='" + JSON.stringify( cfg.lots ) + "'" );

		return '<script src="' + B.engine.split( '?' )[ 0 ] + '"\n' + attrs.join( '\n' ) + '></' + 'script>';
	}

	function refreshEmbed() {
		$( '#jmkEmbed' ).textContent = embedCode();
	}

	/* Les deux formulaires d'export partent avec l'état courant. */
	function refreshForms() {
		$$( '.jmk-cfg' ).forEach( function ( input ) {
			input.value = JSON.stringify( cfg );
		} );
	}

	function changed() {
		refreshPreview();
		refreshEmbed();
		refreshForms();
	}

	/* ────────────────────────────── mécanique ───────────────────────────── */

	function bindGames() {
		$$( '#jmkGames input[name=jmk_game]' ).forEach( function ( r ) {
			r.checked = ( r.value === cfg.game );
			r.addEventListener( 'change', function () {
				cfg.game = r.value;
				$( '#jmkQuizPanel' ).hidden = ( 'quiz' !== cfg.game );
				changed();
			} );
		} );
		$( '#jmkQuizPanel' ).hidden = ( 'quiz' !== cfg.game );
	}

	/* ──────────────────────────────── marque ────────────────────────────── */

	var PRESETS = [ '#D9A441', '#F2506B', '#3E9BF5', '#48C9A9', '#B45CF0', '#FF7A3D' ];

	function bindBrand() {
		var text = [
			[ '#jmkBrand', 'brand' ],
			[ '#jmkTitle', 'title' ],
			[ '#jmkSub',   'subtitle' ]
		];
		text.forEach( function ( pair ) {
			var el = $( pair[ 0 ] );
			el.value = cfg[ pair[ 1 ] ] || '';
			el.addEventListener( 'input', function () {
				cfg[ pair[ 1 ] ] = el.value;
				changed();
			} );
		} );

		var col = $( '#jmkAccent' );
		col.value = cfg.accent;
		col.addEventListener( 'input', function () {
			cfg.accent = col.value;
			paintLots();
			changed();
		} );

		var host = $( '#jmkSwatches' );
		PRESETS.forEach( function ( c ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'jmk-sw';
			b.style.background = c;
			b.setAttribute( 'aria-label', c );
			b.addEventListener( 'click', function () {
				cfg.accent = c;
				col.value = c;
				paintLots();
				changed();
			} );
			host.appendChild( b );
		} );

		var skin = $( '#jmkSkin' );
		skin.value = cfg.skin;
		skin.addEventListener( 'change', function () {
			cfg.skin = skin.value;
			changed();
		} );

		// La langue commande trois choses d'un coup : les libellés du jeu, le
		// sens de lecture, et rien d'autre. Les titres saisis à la main
		// restent ceux de l'utilisateur.
		var lang = $( '#jmkLang' );
		lang.value = cfg.lang;
		lang.addEventListener( 'change', function () {
			cfg.lang = lang.value;
			cfg.dir  = B.dirs[ cfg.lang ] || 'ltr';
			cfg.t    = B.strings[ cfg.lang ] || cfg.t;
			changed();
		} );
	}

	/* ───────────────────────────────── lots ─────────────────────────────── */

	function totalWeight() {
		return cfg.lots.reduce( function ( s, l ) { return s + ( Number( l.weight ) || 0 ); }, 0 );
	}

	/* Les parts se recalculent seules : c'est la colonne que l'on regarde en
	   réglant les poids, et une valeur périmée y serait pire que pas de
	   valeur du tout. */
	function paintShares() {
		var total = totalWeight();
		$$( '#jmkLots tbody tr' ).forEach( function ( tr, i ) {
			var lot = cfg.lots[ i ];
			if ( ! lot ) { return; }
			var pct = total ? ( ( Number( lot.weight ) || 0 ) / total ) * 100 : 0;
			tr.querySelector( '.jmk-share' ).textContent = pct.toFixed( 1 ) + ' %';
		} );
	}

	function paintLots() {
		$$( '#jmkLots tbody tr' ).forEach( function ( tr, i ) {
			var lot = cfg.lots[ i ];
			if ( ! lot ) { return; }
			tr.querySelector( '.jmk-chip' ).style.background = lotColor( lot );
		} );
	}

	function hexToHsl( hex ) {
		hex = String( hex ).replace( '#', '' );
		if ( 3 === hex.length ) {
			hex = hex.split( '' ).map( function ( c ) { return c + c; } ).join( '' );
		}
		var r = parseInt( hex.slice( 0, 2 ), 16 ) / 255,
			g = parseInt( hex.slice( 2, 4 ), 16 ) / 255,
			b = parseInt( hex.slice( 4, 6 ), 16 ) / 255,
			mx = Math.max( r, g, b ), mn = Math.min( r, g, b ), d = mx - mn, h = 0;
		if ( d ) {
			h = mx === r ? ( ( g - b ) / d + ( g < b ? 6 : 0 ) ) : mx === g ? ( ( b - r ) / d + 2 ) : ( ( r - g ) / d + 4 );
			h *= 60;
		}
		var l = ( mx + mn ) / 2;
		return [ h, ( d ? d / ( 1 - Math.abs( 2 * l - 1 ) ) : 0 ) * 100, l * 100 ];
	}

	function lotColor( lot ) {
		if ( lot.losing || null === lot.hue ) { return '#3A2B4D'; }
		var c = hexToHsl( cfg.accent );
		return 'hsl(' + ( ( ( ( c[ 0 ] + Number( lot.hue ) ) % 360 ) + 360 ) % 360 ) + ' ' +
			Math.max( 35, Math.min( 88, c[ 1 ] ) ) + '% ' +
			Math.max( 38, Math.min( 66, c[ 2 ] ) ) + '%)';
	}

	function lotRow( lot, i ) {
		var tr = document.createElement( 'tr' );
		tr.innerHTML =
			'<td><span class="jmk-chip"></span>' +
				'<input type="text" class="jmk-l-label" value="' + esc( lot.label ) + '"></td>' +
			'<td class="num"><input type="number" min="0" max="1000" class="jmk-l-weight" value="' + ( Number( lot.weight ) || 0 ) + '"></td>' +
			'<td class="num"><span class="jmk-share">—</span></td>' +
			'<td class="num"><input type="number" min="0" class="jmk-l-cap" value="' + ( null === lot.cap ? 0 : lot.cap ) + '"></td>' +
			'<td><input type="text" class="jmk-l-code" value="' + esc( lot.code || '' ) + '"></td>' +
			'<td class="num"><input type="number" min="-180" max="180" class="jmk-l-hue" value="' + ( null === lot.hue ? 0 : lot.hue ) + '"></td>' +
			'<td class="num"><input type="checkbox" class="jmk-l-losing"' + ( lot.losing ? ' checked' : '' ) + '></td>' +
			'<td class="num"><button type="button" class="button-link jmk-l-del" aria-label="' + esc( T.remove ) + '">×</button></td>';

		function read() {
			var idx = cfg.lots.indexOf( lot );
			if ( idx < 0 ) { return; }
			lot.label  = tr.querySelector( '.jmk-l-label' ).value;
			lot.weight = Number( tr.querySelector( '.jmk-l-weight' ).value ) || 0;
			var cap    = Number( tr.querySelector( '.jmk-l-cap' ).value ) || 0;
			lot.cap    = cap > 0 ? cap : null;
			lot.losing = tr.querySelector( '.jmk-l-losing' ).checked;
			lot.code   = lot.losing ? '' : tr.querySelector( '.jmk-l-code' ).value.toUpperCase();
			lot.hue    = lot.losing ? null : ( Number( tr.querySelector( '.jmk-l-hue' ).value ) || 0 );

			// Un lot perdant n'a ni code ni teinte : on le montre plutôt que
			// de laisser croire que les champs comptent encore.
			tr.querySelector( '.jmk-l-code' ).disabled = lot.losing;
			tr.querySelector( '.jmk-l-hue' ).disabled  = lot.losing;
			tr.querySelector( '.jmk-l-code' ).value    = lot.code;

			paintShares();
			paintLots();
			changed();
		}

		tr.addEventListener( 'input', read );
		tr.addEventListener( 'change', read );
		tr.querySelector( '.jmk-l-del' ).addEventListener( 'click', function () {
			var idx = cfg.lots.indexOf( lot );
			if ( idx > -1 ) { cfg.lots.splice( idx, 1 ); }
			renderLots();
			changed();
		} );

		tr.querySelector( '.jmk-l-code' ).disabled = lot.losing;
		tr.querySelector( '.jmk-l-hue' ).disabled  = lot.losing;
		return tr;
	}

	function renderLots() {
		var body = $( '#jmkLots tbody' );
		body.innerHTML = '';
		cfg.lots.forEach( function ( lot, i ) { body.appendChild( lotRow( lot, i ) ); } );
		paintShares();
		paintLots();
	}

	function bindLots() {
		renderLots();
		$( '#jmkAddLot' ).addEventListener( 'click', function () {
			cfg.lots.push( { label: '', weight: 10, cap: null, code: '', hue: 0, losing: false } );
			renderLots();
			changed();
		} );
	}

	/* ───────────────────────────────── quiz ─────────────────────────────── */

	function renderQuiz() {
		var host = $( '#jmkQuiz' );
		host.innerHTML = '';
		( cfg.quiz || [] ).forEach( function ( q ) {
			var box = document.createElement( 'div' );
			box.className = 'jmk-qbox';
			box.innerHTML =
				'<input type="text" class="jmk-q-q widefat" value="' + esc( q.q ) + '">' +
				'<div class="jmk-q-opts"></div>' +
				'<p><button type="button" class="button-link jmk-q-del">' + esc( T.remove ) + '</button></p>';

			var opts = box.querySelector( '.jmk-q-opts' );
			q.o.forEach( function ( o, j ) {
				var line = document.createElement( 'label' );
				line.className = 'jmk-q-opt';
				line.innerHTML =
					'<input type="radio" name="ok' + cfg.quiz.indexOf( q ) + '"' + ( Number( q.a ) === j ? ' checked' : '' ) + '>' +
					'<input type="text" value="' + esc( o ) + '">';
				line.querySelector( 'input[type=radio]' ).addEventListener( 'change', function () {
					q.a = j;
					changed();
				} );
				line.querySelector( 'input[type=text]' ).addEventListener( 'input', function ( e ) {
					q.o[ j ] = e.target.value;
					changed();
				} );
				opts.appendChild( line );
			} );

			box.querySelector( '.jmk-q-q' ).addEventListener( 'input', function ( e ) {
				q.q = e.target.value;
				changed();
			} );
			box.querySelector( '.jmk-q-del' ).addEventListener( 'click', function () {
				var idx = cfg.quiz.indexOf( q );
				if ( idx > -1 ) { cfg.quiz.splice( idx, 1 ); }
				renderQuiz();
				changed();
			} );

			host.appendChild( box );
		} );
	}

	function bindQuiz() {
		if ( ! cfg.quiz ) { cfg.quiz = []; }
		renderQuiz();
		$( '#jmkAddQ' ).addEventListener( 'click', function () {
			cfg.quiz.push( { q: '', o: [ '', '', '' ], a: 0 } );
			renderQuiz();
			changed();
		} );
	}

	/* ────────────────────────────── formulaire ──────────────────────────── */

	function bindForm() {
		var map = [
			[ '#jmkFormOn',      'on' ],
			[ '#jmkFormName',    'name' ],
			[ '#jmkFormPhone',   'phone' ],
			[ '#jmkFormConsent', 'consent' ]
		];
		map.forEach( function ( pair ) {
			var el = $( pair[ 0 ] );
			el.checked = !! cfg.form[ pair[ 1 ] ];
			el.addEventListener( 'change', function () {
				cfg.form[ pair[ 1 ] ] = el.checked;
				changed();
			} );
		} );

		var priv = $( '#jmkPrivacy' );
		priv.value = cfg.form.privacy || '';
		priv.addEventListener( 'input', function () {
			cfg.form.privacy = priv.value;
			changed();
		} );

		var one = $( '#jmkOnePlay' );
		one.checked = !! cfg.onePlay;
		one.addEventListener( 'change', function () {
			cfg.onePlay = one.checked;
			changed();
		} );
	}

	/* ──────────────────────────── destination ───────────────────────────── */

	function warnDraw() {
		$( '#jmkDrawWarn' ).hidden = !! cfg.drawUrl;
	}

	function bindDest() {
		var lead = $( '#jmkLead' ),
			srv  = $( '#jmkServerDraw' );

		lead.value  = cfg.leadUrl || '';
		srv.checked = !! cfg.drawUrl;

		lead.addEventListener( 'input', function () {
			cfg.leadUrl = lead.value;
			// Le tirage serveur n'a de sens que vers une adresse : sans elle,
			// on décoche plutôt que de produire une configuration morte.
			if ( ! cfg.leadUrl ) {
				cfg.drawUrl = '';
				srv.checked = false;
			} else if ( srv.checked ) {
				cfg.drawUrl = cfg.leadUrl;
			}
			warnDraw();
			changed();
		} );

		srv.addEventListener( 'change', function () {
			cfg.drawUrl = srv.checked ? cfg.leadUrl : '';
			if ( srv.checked && ! cfg.leadUrl ) { srv.checked = false; }
			warnDraw();
			changed();
		} );

		$( '#jmkUseSite' ).addEventListener( 'click', function () {
			cfg.leadUrl = B.ajax;
			cfg.drawUrl = B.ajax;
			lead.value  = B.ajax;
			srv.checked = true;
			warnDraw();
			changed();
		} );

		warnDraw();
	}

	/* ──────────────────────────────── divers ────────────────────────────── */

	function bindTools() {
		$( '#jmkReplay' ).addEventListener( 'click', function () {
			preview.srcdoc = skeleton();
		} );

		var copy = $( '#jmkCopy' );
		copy.addEventListener( 'click', function () {
			navigator.clipboard.writeText( embedCode() ).then( function () {
				copy.textContent = T.copied;
				setTimeout( function () { copy.textContent = T.copy; }, 1800 );
			} );
		} );
	}

	/* ─────────────────────────────── départ ─────────────────────────────── */

	bindGames();
	bindBrand();
	bindLots();
	bindQuiz();
	bindForm();
	bindDest();
	bindTools();
	changed();

} )();
