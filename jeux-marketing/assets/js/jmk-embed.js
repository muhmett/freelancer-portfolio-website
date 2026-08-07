/*!
 * Jeux Marketing — moteur autonome.
 *
 * Un seul fichier, aucune dépendance : ni WordPress, ni jQuery, ni feuille de
 * style à joindre. C'est le fichier qui part chez le client, et c'est celui que
 * le créateur de jeu (Jeux Marketing → Créateur) configure et exporte.
 *
 * Deux façons de le poser sur une page :
 *
 *   <script src="jmk-embed.js" data-jeu="roue" data-couleur="#D9A441"></script>
 *
 *   JMKGame.mount( '#mon-jeu', { game: 'wheel', accent: '#D9A441', lots: [ … ] } );
 *
 * Le tirage est local par défaut. Renseigner `drawUrl` le confie au serveur :
 * c'est la seule façon de faire respecter un plafond entre plusieurs visiteurs.
 */
( function ( global ) {
	'use strict';

	var VERSION = '1.0.0';
	var injected = false;

	/* ─────────────────────────── réglages livrés ─────────────────────────── */

	var BASE = {
		game:     'wheel',
		accent:   '#D9A441',
		skin:     'elegant',
		dir:      'ltr',
		title:    'Tentez votre chance',
		subtitle: 'Une partie par personne.',
		onePlay:  true,

		// Tirage. Vide = tirage local dans le navigateur.
		drawUrl:  '',
		leadUrl:  '',
		brand:    '',

		lots: [
			{ label: '-10 %',    weight: 34, cap: null, code: 'DIX',   hue: 0,    losing: false },
			{ label: '-20 %',    weight: 20, cap: null, code: 'VINGT', hue: -34,  losing: false },
			{ label: 'Cadeau',   weight: 12, cap: 25,   code: 'GIFT',  hue: 128,  losing: false },
			{ label: 'Livraison', weight: 18, cap: null, code: 'PORT', hue: -128, losing: false },
			{ label: 'Réessayez', weight: 16, cap: null, code: '',     hue: null, losing: true }
		],

		quiz: [
			{ q: 'Quelle couleur porte notre logo ?', o: [ 'Or', 'Vert', 'Bleu' ], a: 0 },
			{ q: 'Depuis quelle année ?',             o: [ '2015', '2019', '2023' ], a: 1 }
		],

		form: {
			on:      true,
			name:    true,
			phone:   false,
			consent: true,
			privacy: ''
		},

		t: {
			play:      'Je joue',
			again:     'Rejouer',
			scratch:   'Grattez ici',
			tapPick:   'Choisissez une boîte',
			win:       'Gagné :',
			lose:      'Perdu cette fois',
			loseSub:   'Retentez votre chance bientôt.',
			revealed:  'Révélé',
			code:      'Votre code',
			name:      'Prénom',
			email:     'Email',
			phone:     'Téléphone',
			consent:   'J’accepte de recevoir mon code par email.',
			privacy:   'Politique de confidentialité',
			send:      'Recevoir mon code',
			sent:      'C’est envoyé. Vérifiez votre boîte mail.',
			errName:   'Indiquez votre prénom.',
			errMail:   'Cette adresse email semble incomplète.',
			errPhone:  'Indiquez un numéro de téléphone.',
			errCons:   'Merci de cocher la case.',
			errNet:    'Envoi impossible pour le moment.',
			exhausted: 'épuisé',
			score:     'Score : %1$s / %2$s'
		}
	};

	/* ───────────────────────────── outillage ───────────────────────────── */

	function assign( target ) {
		for ( var i = 1; i < arguments.length; i++ ) {
			var src = arguments[ i ];
			if ( ! src ) { continue; }
			for ( var k in src ) {
				if ( Object.prototype.hasOwnProperty.call( src, k ) ) { target[ k ] = src[ k ]; }
			}
		}
		return target;
	}

	function esc( s ) {
		return String( s ).replace( /[<>&"]/g, function ( c ) {
			return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[ c ];
		} );
	}

	function el( tag, cls, html ) {
		var n = document.createElement( tag );
		if ( cls )  { n.className = cls; }
		if ( null != html ) { n.innerHTML = html; }
		return n;
	}

	function reduced() {
		return global.matchMedia && global.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
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

	function hsl( h, s, l ) {
		return 'hsl(' + ( ( ( h % 360 ) + 360 ) % 360 ) + ' ' +
			Math.max( 0, Math.min( 100, s ) ) + '% ' + Math.max( 0, Math.min( 100, l ) ) + '%)';
	}

	/* ctx.roundRect manque encore sur les Safari d'avant 16.4. */
	function roundPath( c, x, y, w, h, r ) {
		r = Math.min( r, w / 2, h / 2 );
		c.beginPath();
		c.moveTo( x + r, y );
		c.arcTo( x + w, y, x + w, y + h, r );
		c.arcTo( x + w, y + h, x, y + h, r );
		c.arcTo( x, y + h, x, y, r );
		c.arcTo( x, y, x + w, y, r );
		c.closePath();
	}

	/* ─────────────────────────────── styles ─────────────────────────────── */

	var CSS = [
		'.jmkg{--jmkg-bg:#1B1226;--jmkg-card:#241733;--jmkg-line:#3B2B50;--jmkg-ink:#F5EFE6;',
		'--jmkg-muted:#A99BBA;--jmkg-rose:#F2506B;--jmkg-mint:#48C9A9;--jmkg-r:16px;',
		'box-sizing:border-box;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;',
		'color:var(--jmkg-ink);background:var(--jmkg-bg);border-radius:var(--jmkg-r);',
		'padding:26px 22px;max-width:520px;margin:0 auto;line-height:1.5;position:relative;overflow:hidden}',
		'.jmkg *,.jmkg *::before,.jmkg *::after{box-sizing:border-box}',
		'.jmkg[dir=rtl]{direction:rtl;letter-spacing:0}',

		'.jmkg-h{margin:0 0 4px;font-size:22px;font-weight:800;letter-spacing:-.02em}',
		'.jmkg-sub{margin:0 0 18px;font-size:14px;color:var(--jmkg-muted)}',
		'.jmkg-stage{display:flex;flex-direction:column;align-items:center;gap:14px}',

		'.jmkg-btn{appearance:none;border:0;cursor:pointer;font:inherit;font-weight:700;font-size:15px;',
		'padding:13px 26px;border-radius:999px;background:var(--jmkg-accent);color:var(--jmkg-accent-ink);',
		'transition:transform .12s ease,filter .12s ease}',
		'.jmkg-btn:hover:not(:disabled){filter:brightness(1.07)}',
		'.jmkg-btn:active:not(:disabled){transform:translateY(1px)}',
		'.jmkg-btn:disabled{opacity:.5;cursor:default}',
		'.jmkg-btn.ghost{background:transparent;color:var(--jmkg-muted);border:1px solid var(--jmkg-line)}',

		'.jmkg-msg{min-height:24px;font-size:15px;font-weight:700;text-align:center}',
		'.jmkg-code{display:block;margin-top:6px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;',
		'font-size:18px;letter-spacing:.12em;color:var(--jmkg-ink);',
		'background:rgba(255,255,255,.07);border-radius:8px;padding:7px 12px}',

		/* roue */
		'.jmkg-wheelwrap{position:relative;width:100%;max-width:320px;aspect-ratio:1}',
		'.jmkg-wheelwrap canvas{width:100%;height:100%;display:block}',
		'.jmkg-needle{position:absolute;top:-6px;left:50%;transform:translateX(-50%);',
		'width:0;height:0;border-left:11px solid transparent;border-right:11px solid transparent;',
		'border-top:20px solid var(--jmkg-ink);filter:drop-shadow(0 2px 3px rgba(0,0,0,.5))}',

		/* grattage */
		'.jmkg-card{position:relative;width:100%;max-width:340px;height:150px;border-radius:12px;',
		'overflow:hidden;background:var(--jmkg-card);display:grid;place-items:center}',
		'.jmkg-prize{font-size:26px;font-weight:800;color:var(--jmkg-accent);text-align:center;padding:0 12px}',
		'.jmkg-card canvas{position:absolute;inset:0;width:100%;height:100%;cursor:crosshair;touch-action:none}',

		/* tap */
		'.jmkg-boxes{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}',
		'.jmkg-box{width:84px;height:84px;border-radius:14px;border:1px solid var(--jmkg-line);',
		'background:var(--jmkg-card);cursor:pointer;display:grid;place-items:center;font-size:30px;',
		'color:var(--jmkg-accent);transition:transform .15s ease,border-color .15s ease}',
		'.jmkg-box:hover:not(.done){transform:translateY(-4px);border-color:var(--jmkg-accent)}',
		'.jmkg-box.done{cursor:default}',
		'.jmkg-box.win{border-color:var(--jmkg-accent);background:rgba(255,255,255,.08)}',
		'.jmkg-box.lose{opacity:.35}',

		/* machine à sous */
		'.jmkg-reels{display:flex;gap:8px;justify-content:center;background:var(--jmkg-card);',
		'padding:10px;border-radius:14px;border:1px solid var(--jmkg-line)}',
		'.jmkg-reel{width:96px;height:58px;overflow:hidden;border-radius:8px;background:#150C1D}',
		'.jmkg-strip{display:flex;flex-direction:column}',
		'.jmkg-cell{height:58px;flex:0 0 58px;display:grid;place-items:center;font-size:13px;',
		'font-weight:700;text-align:center;padding:0 4px}',

		/* pluie de lots */
		'.jmkg-plinko{width:100%;max-width:340px;height:240px;background:var(--jmkg-card);',
		'border-radius:12px;border:1px solid var(--jmkg-line)}',
		'.jmkg-plinko canvas{width:100%;height:100%;display:block}',

		/* quiz */
		'.jmkg-quiz{width:100%;max-width:380px}',
		'.jmkg-q{font-size:16px;font-weight:700;margin:0 0 12px}',
		'.jmkg-opt{display:block;width:100%;text-align:start;margin-bottom:8px;font:inherit;font-size:14px;',
		'padding:11px 14px;border-radius:10px;border:1px solid var(--jmkg-line);background:var(--jmkg-card);',
		'color:var(--jmkg-ink);cursor:pointer}',
		'.jmkg-opt:hover:not(:disabled){border-color:var(--jmkg-accent)}',
		'.jmkg-opt.ok{border-color:var(--jmkg-mint);color:var(--jmkg-mint)}',
		'.jmkg-opt.no{border-color:var(--jmkg-rose);color:var(--jmkg-rose)}',
		'.jmkg-count{font-size:12px;color:var(--jmkg-muted);margin-bottom:8px}',

		/* formulaire */
		'.jmkg-form{margin-top:20px;padding-top:18px;border-top:1px solid var(--jmkg-line)}',
		'.jmkg-form[hidden]{display:none}',
		'.jmkg-field{margin-bottom:10px}',
		'.jmkg-field label{display:block;font-size:12px;color:var(--jmkg-muted);margin-bottom:5px}',
		'.jmkg-field input{width:100%;font:inherit;font-size:15px;padding:11px 13px;border-radius:10px;',
		'border:1px solid var(--jmkg-line);background:var(--jmkg-card);color:var(--jmkg-ink)}',
		'.jmkg-field input:focus{outline:2px solid var(--jmkg-accent);outline-offset:1px}',
		'.jmkg-consent{display:flex;gap:9px;align-items:flex-start;font-size:12.5px;',
		'color:var(--jmkg-muted);margin:12px 0}',
		'.jmkg-consent input{margin-top:2px;flex:0 0 auto;accent-color:var(--jmkg-accent)}',
		'.jmkg-consent a{color:var(--jmkg-accent)}',
		'.jmkg-err{min-height:20px;font-size:13px;color:var(--jmkg-rose);margin-top:8px}',
		'.jmkg-err.ok{color:var(--jmkg-mint)}',
		'.jmkg-form .jmkg-btn{width:100%}',

		'.jmkg-confetti{position:absolute;inset:0;pointer-events:none;display:none}',

		/* habillage fête foraine */
		'.jmkg.skin-arcade{--jmkg-bg:#2A0F4D;--jmkg-card:#3A1A66;--jmkg-line:#6B3FB5;',
		'background-image:radial-gradient(120% 90% at 50% 0,rgba(255,255,255,.10),transparent 60%)}',
		'.jmkg.skin-arcade .jmkg-h{text-shadow:0 2px 0 rgba(0,0,0,.45)}',
		'.jmkg.skin-arcade .jmkg-btn{border:2px solid rgba(255,255,255,.55);',
		'box-shadow:0 5px 0 rgba(0,0,0,.35),inset 0 2px 0 rgba(255,255,255,.4)}',
		'.jmkg.skin-arcade .jmkg-btn:active:not(:disabled){transform:translateY(3px);',
		'box-shadow:0 2px 0 rgba(0,0,0,.35),inset 0 2px 0 rgba(255,255,255,.4)}',

		/* habillage casino : feutre, filets or, bouton jeton */
		'.jmkg.skin-casino{--jmkg-bg:#0B3325;--jmkg-card:#134A38;--jmkg-line:rgba(217,164,65,.42);',
		'--jmkg-muted:#9FBFB1;',
		/* Le feutre est peint en CSS pur : le moteur doit tenir dans un seul
		   fichier, donc aucune image extérieure n'est chargée. Deux trames
		   croisées à 45° suffisent à casser l'aplat. */
		'background-image:repeating-linear-gradient(45deg,rgba(255,255,255,.022) 0 2px,transparent 2px 4px),',
		'repeating-linear-gradient(-45deg,rgba(0,0,0,.05) 0 2px,transparent 2px 4px),',
		'radial-gradient(120% 90% at 50% 0,rgba(217,164,65,.14),transparent 62%);',
		'box-shadow:inset 0 0 0 3px rgba(217,164,65,.16)}',
		'.jmkg.skin-casino .jmkg-h{color:#FFFBF0;text-shadow:0 1px 0 rgba(0,0,0,.5)}',
		'.jmkg.skin-casino .jmkg-btn{border-radius:999px;color:#FFF3E0;',
		'background:linear-gradient(180deg,#D32433,#A50E1B 60%,#7C0912);',
		'border:2px solid #D9A441;box-shadow:inset 0 0 0 3px rgba(255,251,240,.14),',
		'inset 0 1px 0 rgba(255,255,255,.28),0 10px 26px -14px rgba(0,0,0,.9)}',
		'.jmkg.skin-casino .jmkg-code{color:#FFE9A8;border:1px dashed #D9A441;background:#07211A}',
		'.jmkg.skin-casino .jmkg-box,.jmkg.skin-casino .jmkg-reel,',
		'.jmkg.skin-casino .jmkg-opt{border-color:rgba(217,164,65,.45)}',
		'.jmkg.skin-casino .jmkg-reels,.jmkg.skin-casino .jmkg-plinko{',
		'border-color:rgba(217,164,65,.45);background:#07211A}'
	].join( '' );

	function injectCss() {
		if ( injected || ! document.head ) { return; }
		injected = true;
		var s = el( 'style' );
		s.setAttribute( 'data-jmk-embed', VERSION );
		s.textContent = CSS;
		document.head.appendChild( s );
	}

	/* ──────────────────────────────  tirage  ────────────────────────────── */

	/**
	 * Tirage pondéré avec plafonds.
	 *
	 * Le compteur de plafond vit dans le navigateur tant qu'aucun `drawUrl`
	 * n'est renseigné. C'est suffisant pour une démonstration ; ça ne l'est
	 * pas pour une vraie campagne à stock limité, où deux visiteurs ne
	 * partagent aucun compteur. D'où le tirage serveur.
	 */
	function Draw( cfg ) {
		var lots = cfg.lots.map( function ( l ) {
			return assign( {}, l, { awarded: 0, cap: ( null == l.cap || '' === l.cap ) ? null : Number( l.cap ) } );
		} );

		var key = 'jmkg:' + ( cfg.brand || 'jeu' ) + ':' + cfg.game;

		function store() {
			try {
				var saved = JSON.parse( global.localStorage.getItem( key ) || 'null' );
				if ( saved && saved.length === lots.length ) {
					lots.forEach( function ( l, i ) { l.awarded = saved[ i ] | 0; } );
				}
			} catch ( e ) {}
		}
		function keep() {
			try {
				global.localStorage.setItem( key, JSON.stringify( lots.map( function ( l ) { return l.awarded; } ) ) );
			} catch ( e ) {}
		}
		store();

		function local() {
			var pool = lots.filter( function ( l ) { return null === l.cap || l.awarded < l.cap; } );
			if ( ! pool.length ) { pool = lots.filter( function ( l ) { return l.losing; } ); }
			if ( ! pool.length ) { pool = lots.slice(); }
			var total = pool.reduce( function ( s, l ) { return s + Number( l.weight || 0 ); }, 0 );
			if ( total <= 0 ) { return pool[ pool.length - 1 ]; }
			var r = Math.random() * total;
			for ( var i = 0; i < pool.length; i++ ) {
				r -= Number( pool[ i ].weight || 0 );
				if ( r <= 0 ) { return pool[ i ]; }
			}
			return pool[ pool.length - 1 ];
		}

		/**
		 * @param {Function} done Reçoit ( lot, code, token ).
		 */
		function pick( done ) {
			if ( ! cfg.drawUrl ) { return finishLocal( done ); }
			var body = new URLSearchParams();
			body.append( 'action', 'jmk_play' );
			if ( cfg.nonce ) { body.append( 'nonce', cfg.nonce ); }
			fetch( cfg.drawUrl, { method: 'POST', body: body, credentials: 'omit' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( j ) {
					var d = j && j.success ? j.data : null;
					if ( ! d || 'undefined' === typeof d.index || ! lots[ d.index ] ) { return finishLocal( done ); }
					if ( d.caps ) {
						lots.forEach( function ( l, i ) {
							if ( 'undefined' !== typeof d.caps[ i ] ) { l.awarded = parseInt( d.caps[ i ], 10 ) || 0; }
						} );
					}
					done( lots[ d.index ], d.code || '', d.token || '' );
				} )
				.catch( function () { finishLocal( done ); } );
		}

		function finishLocal( done ) {
			var lot  = local();
			var code = lot.code ? lot.code + '-' + Math.random().toString( 36 ).slice( 2, 6 ).toUpperCase() : '';
			if ( code && null !== lot.cap ) { lot.awarded++; keep(); }
			done( lot, code, '' );
		}

		return { lots: lots, pick: pick, indexOf: function ( lot ) { return lots.indexOf( lot ); } };
	}

	/* ───────────────────────────── une instance ─────────────────────────── */

	function Game( host, cfg ) {
		var self  = this;
		var slow  = reduced();
		var draw  = Draw( cfg );
		var lots  = draw.lots;
		var accent = cfg.accent;
		var hslA   = hexToHsl( accent );
		var T      = cfg.t;
		var played = false;
		var token  = '';
		var lastLot = null, lastCode = '';

		var root = el( 'div', 'jmkg' + ( 'elegant' === cfg.skin ? '' : ' skin-' + cfg.skin ) );
		root.setAttribute( 'dir', cfg.dir );
		root.style.setProperty( '--jmkg-accent', accent );
		root.style.setProperty( '--jmkg-accent-ink', hslA[ 2 ] > 58 ? '#221503' : '#FFF6E4' );

		if ( cfg.title )    { root.appendChild( el( 'h3', 'jmkg-h', esc( cfg.title ) ) ); }
		if ( cfg.subtitle ) { root.appendChild( el( 'p', 'jmkg-sub', esc( cfg.subtitle ) ) ); }

		var stage = el( 'div', 'jmkg-stage' );
		root.appendChild( stage );

		var confetti = el( 'canvas', 'jmkg-confetti' );
		root.appendChild( confetti );

		host.appendChild( root );

		function lotColor( lot ) {
			if ( null === lot.hue || '' === lot.hue || lot.losing ) {
				if ( 'arcade' === cfg.skin ) { return '#4C2A9B'; }
				if ( 'casino' === cfg.skin ) { return '#0B3325'; }
				return '#3A2B4D';
			}
			return hsl( hslA[ 0 ] + Number( lot.hue ), Math.max( 35, Math.min( 88, hslA[ 1 ] ) ),
				Math.max( 38, Math.min( 66, hslA[ 2 ] ) ) );
		}
		function lotInk( lot ) {
			return ( null === lot.hue || '' === lot.hue || lot.losing ) ? '#9C90AC' : '#150C1D';
		}

		/* Une partie, quel que soit le jeu. */
		function play( done ) {
			if ( played ) { return; }
			draw.pick( function ( lot, code, tk ) {
				if ( cfg.onePlay ) { played = true; }
				token    = tk;
				lastLot  = lot;
				lastCode = code;
				done( lot, code );
				if ( code ) { burst(); }
				showForm();
			} );
		}

		function burst() {
			if ( slow ) { return; }
			var x = confetti.getContext( '2d' ),
				w = root.clientWidth, h = root.clientHeight;
			confetti.width = w; confetti.height = h;
			confetti.style.display = 'block';
			var P = [], cols = [ accent, '#F2506B', '#48C9A9', '#F5EFE6' ];
			for ( var i = 0; i < 90; i++ ) {
				P.push( {
					x: w / 2 + ( Math.random() - 0.5 ) * w * 0.5, y: h * 0.4,
					vx: ( Math.random() - 0.5 ) * 8, vy: Math.random() * -9 - 3,
					r: Math.random() * 4 + 2, c: cols[ i % cols.length ], a: 1, rot: Math.random() * 6
				} );
			}
			( function loop() {
				x.clearRect( 0, 0, w, h );
				var alive = false;
				P.forEach( function ( p ) {
					p.vy += 0.3; p.x += p.vx; p.y += p.vy; p.a -= 0.009; p.rot += 0.12;
					if ( p.a <= 0 ) { return; }
					alive = true;
					x.save();
					x.globalAlpha = Math.max( 0, p.a );
					x.translate( p.x, p.y );
					x.rotate( p.rot );
					x.fillStyle = p.c;
					x.fillRect( -p.r, -p.r * 0.5, p.r * 2, p.r );
					x.restore();
				} );
				if ( alive ) { requestAnimationFrame( loop ); } else { confetti.style.display = 'none'; }
			} )();
		}

		/* ── message de résultat, partagé ── */
		var msg = el( 'p', 'jmkg-msg' );

		function say( lot, code ) {
			msg.innerHTML = code
				? T.win + ' ' + esc( lot.label ) + '<span class="jmkg-code">' + esc( code ) + '</span>'
				: T.lose;
			msg.style.color = code ? accent : 'var(--jmkg-muted)';
		}

		/* ══════════════════════════ les mécaniques ═════════════════════════ */

		var games = {};

		/* ── roue ── */
		games.wheel = function () {
			var wrap = el( 'div', 'jmkg-wheelwrap' );
			var cv   = el( 'canvas' );
			cv.width = cv.height = 520;
			wrap.appendChild( cv );
			wrap.appendChild( el( 'div', 'jmkg-needle' ) );
			stage.appendChild( wrap );

			var ctx = cv.getContext( '2d' ), R = 260, rot = 0, spinning = false;
			var arcade = ( 'arcade' === cfg.skin ),
				casino = ( 'casino' === cfg.skin ),
				RIM    = arcade ? 40 : ( casino ? 14 : 6 ),
				bulb   = 0;

			function rim( n ) {
				var band = ctx.createLinearGradient( -R, -R, R, R );
				band.addColorStop( 0, '#F7DE9B' );
				band.addColorStop( 0.34, '#D9A441' );
				band.addColorStop( 0.62, '#8C5F14' );
				band.addColorStop( 1, '#F0C96B' );
				ctx.beginPath();
				ctx.arc( 0, 0, R - 3, 0, Math.PI * 2 );
				ctx.arc( 0, 0, R - RIM + 4, 0, Math.PI * 2, true );
				ctx.fillStyle = band;
				ctx.fill( 'evenodd' );
				for ( var i = 0; i < n; i++ ) {
					var a = ( i / n ) * Math.PI * 2 - Math.PI / 2,
						x = Math.cos( a ) * ( R - RIM / 2 + 1 ),
						y = Math.sin( a ) * ( R - RIM / 2 + 1 );
					ctx.beginPath();
					ctx.arc( x, y, RIM * 0.20, 0, Math.PI * 2 );
					ctx.fillStyle = ( ( i + bulb ) % 2 === 0 ) ? '#FFF6D8' : '#B98A2E';
					ctx.fill();
				}
			}

			function paint() {
				var n = lots.length, seg = ( Math.PI * 2 ) / n, rad = R - RIM;
				ctx.clearRect( 0, 0, 520, 520 );
				ctx.save();
				ctx.translate( R, R );
				if ( arcade ) { rim( Math.max( 16, n * 4 ) ); }
				if ( casino ) {
					// Anneau or plein : l'habillage du site le pose en CSS
					// autour du canevas, mais un fichier exporté n'a que le
					// canevas — l'anneau doit donc être dessiné.
					var ring = ctx.createLinearGradient( -R, -R, R, R );
					ring.addColorStop( 0, '#FFE9A8' );
					ring.addColorStop( 0.4, '#D9A441' );
					ring.addColorStop( 0.7, '#8A5D12' );
					ring.addColorStop( 1, '#FFE9A8' );
					ctx.beginPath();
					ctx.arc( 0, 0, R - 2, 0, Math.PI * 2 );
					ctx.arc( 0, 0, R - RIM + 3, 0, Math.PI * 2, true );
					ctx.fillStyle = ring;
					ctx.fill( 'evenodd' );
				}
				ctx.save();
				ctx.rotate( rot );
				lots.forEach( function ( lot, i ) {
					var a0  = -Math.PI / 2 + i * seg,
						out = null !== lot.cap && lot.awarded >= lot.cap;
					ctx.beginPath();
					ctx.moveTo( 0, 0 );
					ctx.arc( 0, 0, rad, a0, a0 + seg );
					ctx.closePath();
					ctx.fillStyle   = lotColor( lot );
					ctx.globalAlpha = out ? 0.28 : 1;
					ctx.fill();
					ctx.globalAlpha = 1;
					ctx.lineWidth   = arcade ? 5 : ( casino ? 2 : 3 );
					ctx.strokeStyle = arcade ? '#7A4E0B' : ( casino ? '#D9A441' : '#150C1D' );
					ctx.stroke();

					ctx.save();
					ctx.rotate( a0 + seg / 2 );
					ctx.textAlign = 'right';
					ctx.textBaseline = 'middle';
					ctx.font = '800 34px system-ui, sans-serif';
					ctx.fillStyle = lotInk( lot );
					var w = String( lot.label ).split( ' ' );
					if ( w.length > 1 && lot.label.length > 10 ) {
						ctx.fillText( w[ 0 ], rad - 34, -20 );
						ctx.fillText( w.slice( 1 ).join( ' ' ), rad - 34, 20 );
					} else {
						ctx.fillText( lot.label, rad - 34, 0 );
					}
					if ( out ) {
						ctx.font = '700 17px ui-monospace, monospace';
						ctx.fillStyle = '#F2506B';
						ctx.fillText( T.exhausted, rad - 34, 44 );
					}
					ctx.restore();
				} );
				ctx.restore();
				ctx.restore();
			}

			var btn = el( 'button', 'jmkg-btn', esc( T.play ) );
			btn.type = 'button';
			stage.appendChild( msg );
			stage.appendChild( btn );

			btn.addEventListener( 'click', function () {
				if ( spinning || played ) { return; }
				spinning = true;
				btn.disabled = true;
				play( function ( lot, code ) {
					var n = lots.length, seg = ( Math.PI * 2 ) / n, idx = Math.max( 0, draw.indexOf( lot ) );
					var target = ( Math.PI * 2 ) * 6 - ( idx + 0.5 ) * seg + ( Math.random() - 0.5 ) * seg * 0.5;
					var from = rot % ( Math.PI * 2 ), dur = slow ? 200 : 4400, t0 = performance.now();
					( function frame( now ) {
						var p = Math.min( ( now - t0 ) / dur, 1 );
						rot = from + ( target - from ) * ( 1 - Math.pow( 1 - p, 4 ) );
						bulb = ( now / 140 ) & 1;
						paint();
						if ( p < 1 ) {
							requestAnimationFrame( frame );
						} else {
							spinning = false;
							btn.disabled = played;
							say( lot, code );
							paint();
						}
					} )( t0 );
				} );
			} );

			return { paint: paint };
		};

		/* ── carte à gratter ── */
		games.scratch = function () {
			var card  = el( 'div', 'jmkg-card' );
			var prize = el( 'div', 'jmkg-prize', '—' );
			var cv    = el( 'canvas' );
			card.appendChild( prize );
			card.appendChild( cv );
			stage.appendChild( card );
			stage.appendChild( msg );

			var sx = cv.getContext( '2d', { willReadFrequently: true } );
			var scratching = false, done = false, ticks = 0, pending = false, lot = null, code = '';

			function paint() {
				if ( done ) { return; }
				var r = card.getBoundingClientRect(), d = global.devicePixelRatio || 1;
				if ( r.width < 2 ) { return; }
				cv.width  = Math.round( r.width * d );
				cv.height = Math.round( r.height * d );
				sx.setTransform( d, 0, 0, d, 0, 0 );
				sx.globalCompositeOperation = 'source-over';
				var g = sx.createLinearGradient( 0, 0, r.width, r.height );
				g.addColorStop( 0, '#8E95A2' ); g.addColorStop( 0.4, '#C3C9D2' );
				g.addColorStop( 0.6, '#7E8593' ); g.addColorStop( 1, '#A9B0BB' );
				sx.fillStyle = g;
				sx.fillRect( 0, 0, r.width, r.height );
				sx.fillStyle = 'rgba(21,12,29,.36)';
				sx.font      = '600 13px ui-monospace, monospace';
				sx.textAlign = 'center';
				sx.fillText( T.scratch, r.width / 2, r.height / 2 );
			}

			/* Le lot n'est tiré qu'au premier grattage : afficher la page ne
			   consomme aucun plafond. */
			function ensure() {
				if ( lot || pending || played ) { return; }
				pending = true;
				play( function ( l, c ) {
					pending = false;
					lot = l; code = c;
					prize.textContent = c ? l.label : T.lose;
					prize.style.color = c ? accent : 'var(--jmkg-muted)';
				} );
			}

			function at( e ) {
				if ( done ) { return; }
				ensure();
				var r  = cv.getBoundingClientRect(),
					pt = e.touches ? e.touches[ 0 ] : e;
				sx.globalCompositeOperation = 'destination-out';
				sx.beginPath();
				sx.arc( pt.clientX - r.left, pt.clientY - r.top, 20, 0, Math.PI * 2 );
				sx.fill();
				if ( ++ticks % 6 ) { return; }
				var d = sx.getImageData( 0, 0, cv.width, cv.height ).data, clear = 0, n = 0;
				for ( var i = 3; i < d.length; i += 160 ) { n++; if ( d[ i ] < 40 ) { clear++; } }
				if ( n && ( clear / n ) > 0.46 && lot ) {
					done = true;
					sx.clearRect( 0, 0, cv.width, cv.height );
					say( lot, code );
				}
			}

			cv.addEventListener( 'mousedown', function ( e ) { scratching = true; at( e ); } );
			cv.addEventListener( 'mousemove', function ( e ) { if ( scratching ) { at( e ); } } );
			global.addEventListener( 'mouseup', function () { scratching = false; } );
			cv.addEventListener( 'touchstart', function ( e ) { e.preventDefault(); scratching = true; at( e ); }, { passive: false } );
			cv.addEventListener( 'touchmove', function ( e ) { e.preventDefault(); if ( scratching ) { at( e ); } }, { passive: false } );
			cv.addEventListener( 'touchend', function () { scratching = false; } );

			return { paint: paint };
		};

		/* ── tap to win ── */
		games.tap = function () {
			var wrap = el( 'div', 'jmkg-boxes' );
			var gift = '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
				'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
				'<path d="M3 10h18v10H3z"/><path d="M2 6h20v4H2z"/><path d="M12 6v14"/>' +
				'<path d="M12 6S10.5 3 8.5 3a2 2 0 000 4H12z"/><path d="M12 6s1.5-3 3.5-3a2 2 0 010 4H12z"/></svg>';
			var boxes = [];
			for ( var i = 0; i < 3; i++ ) {
				var b = el( 'button', 'jmkg-box', gift );
				b.type = 'button';
				boxes.push( b );
				wrap.appendChild( b );
			}
			stage.appendChild( wrap );
			stage.appendChild( msg );
			msg.textContent = T.tapPick;
			msg.style.color = 'var(--jmkg-muted)';

			boxes.forEach( function ( b ) {
				b.addEventListener( 'click', function () {
					if ( played ) { return; }
					play( function ( lot, code ) {
						boxes.forEach( function ( o ) {
							o.classList.add( 'done' );
							if ( o === b ) {
								o.classList.add( code ? 'win' : 'lose' );
								o.textContent = code ? '★' : '—';
							} else {
								o.classList.add( 'lose' );
								o.textContent = '—';
							}
						} );
						say( lot, code );
					} );
				} );
			} );

			return { paint: function () {} };
		};

		/* ── machine à sous ── */
		games.slot = function () {
			var CELL = 58;
			var reels = el( 'div', 'jmkg-reels' ), strips = [];
			for ( var i = 0; i < 3; i++ ) {
				var r = el( 'div', 'jmkg-reel' ), s = el( 'div', 'jmkg-strip' );
				r.appendChild( s );
				strips.push( s );
				reels.appendChild( r );
			}
			stage.appendChild( reels );
			stage.appendChild( msg );

			var btn = el( 'button', 'jmkg-btn', esc( T.play ) );
			btn.type = 'button';
			stage.appendChild( btn );

			function paint() {
				strips.forEach( function ( strip ) {
					var html = '';
					for ( var c = 0; c < 9; c++ ) {
						lots.forEach( function ( lot ) {
							var face = String( lot.label );
							if ( face.length > 14 ) { face = face.slice( 0, 13 ) + '…'; }
							html += '<span class="jmkg-cell" style="background:' + lotColor( lot ) +
								';color:' + lotInk( lot ) + '">' + esc( face ) + '</span>';
						} );
					}
					strip.innerHTML = html;
					strip.style.transition = 'none';
					strip.style.transform  = 'translateY(0)';
				} );
			}
			paint();

			/* Trois faces différentes quand c'est perdu. */
			function miss( drawn ) {
				var others = lots.map( function ( l, i ) { return i; } )
					.filter( function ( i ) { return i !== drawn; } );
				if ( others.length < 2 ) { return [ drawn, drawn, others[ 0 ] || drawn ]; }
				var a = others[ Math.floor( Math.random() * others.length ) ];
				var rest = others.filter( function ( i ) { return i !== a; } );
				return [ drawn, a, rest[ Math.floor( Math.random() * rest.length ) ] ];
			}

			btn.addEventListener( 'click', function () {
				if ( played ) { return; }
				btn.disabled = true;
				play( function ( lot, code ) {
					var idx   = Math.max( 0, draw.indexOf( lot ) ),
						faces = code ? [ idx, idx, idx ] : miss( idx ),
						base  = slow ? 120 : 900,
						step  = slow ? 40 : 420;
					strips.forEach( function ( strip, i ) {
						var y = -( ( ( 3 + i * 2 ) * lots.length ) + faces[ i ] ) * CELL;
						strip.style.transition = 'none';
						strip.style.transform  = 'translateY(0)';
						void strip.offsetWidth;
						strip.style.transition = 'transform ' + ( base + i * step ) + 'ms cubic-bezier(.16,.84,.28,1)';
						strip.style.transform  = 'translateY(' + y + 'px)';
					} );
					setTimeout( function () {
						btn.disabled = played;
						say( lot, code );
					}, base + 2 * step + 140 );
				} );
			} );

			return { paint: paint };
		};

		/* ── pluie de lots ── */
		games.plinko = function () {
			var ROWS = 9;
			var wrap = el( 'div', 'jmkg-plinko' );
			var cv   = el( 'canvas' );
			wrap.appendChild( cv );
			stage.appendChild( wrap );
			stage.appendChild( msg );

			var btn = el( 'button', 'jmkg-btn', esc( T.play ) );
			btn.type = 'button';
			stage.appendChild( btn );

			var px = cv.getContext( '2d' ), ball = null, landed = -1;

			function size() {
				var r = wrap.getBoundingClientRect(), d = global.devicePixelRatio || 1;
				if ( r.width < 2 ) { return null; }
				var w = r.width, h = r.height;
				if ( cv.width !== Math.round( w * d ) || cv.height !== Math.round( h * d ) ) {
					cv.width  = Math.round( w * d );
					cv.height = Math.round( h * d );
				}
				px.setTransform( d, 0, 0, d, 0, 0 );
				return { w: w, h: h };
			}

			function paint() {
				var s = size();
				if ( ! s || ! lots.length ) { return; }
				var slotH = 30, top = 14,
					gapY = ( s.h - slotH - top ) / ROWS,
					gapX = s.w / ( ROWS + 2 );
				px.clearRect( 0, 0, s.w, s.h );

				px.fillStyle = '#5A4770';
				for ( var row = 0; row < ROWS; row++ ) {
					var count = row + 2, y = top + ( row + 0.5 ) * gapY;
					for ( var i = 0; i < count; i++ ) {
						px.beginPath();
						px.arc( s.w / 2 + ( i - ( count - 1 ) / 2 ) * gapX, y, 2.6, 0, Math.PI * 2 );
						px.fill();
					}
				}

				var cw = s.w / lots.length;
				lots.forEach( function ( lot, i ) {
					var out = null !== lot.cap && lot.awarded >= lot.cap;
					px.globalAlpha = out ? 0.3 : 1;
					px.fillStyle   = lotColor( lot );
					roundPath( px, i * cw + 2, s.h - slotH, cw - 4, slotH - 2, 6 );
					px.fill();
					px.globalAlpha = 1;
					px.fillStyle    = lotInk( lot );
					px.font         = '600 10px ui-monospace, monospace';
					px.textAlign    = 'center';
					px.textBaseline = 'middle';
					var label = String( lot.label );
					if ( label.length > 9 ) { label = label.slice( 0, 8 ) + '…'; }
					px.fillText( label, i * cw + cw / 2, s.h - slotH / 2 - 1 );
					if ( landed === i ) {
						px.strokeStyle = '#F5EFE6';
						px.lineWidth   = 2;
						roundPath( px, i * cw + 2, s.h - slotH, cw - 4, slotH - 2, 6 );
						px.stroke();
					}
				} );

				if ( ball ) {
					px.fillStyle = '#F5EFE6';
					px.beginPath();
					px.arc( ball.x, ball.y, 6, 0, Math.PI * 2 );
					px.fill();
				}
			}
			paint();

			btn.addEventListener( 'click', function () {
				if ( played ) { return; }
				btn.disabled = true;
				landed = -1;
				play( function ( lot, code ) {
					var s = size();
					if ( ! s ) { say( lot, code ); return; }
					var idx  = Math.max( 0, draw.indexOf( lot ) ),
						slotH = 30, top = 14,
						gapY = ( s.h - slotH - top ) / ROWS,
						gapX = s.w / ( ROWS + 2 ),
						col  = Math.round( ( idx + 0.5 ) * ROWS / lots.length ),
						moves = [];

					for ( var i = 0; i < ROWS; i++ ) { moves.push( i < col ? 1 : 0 ); }
					for ( var j = moves.length - 1; j > 0; j-- ) {
						var k = Math.floor( Math.random() * ( j + 1 ) ), t = moves[ j ];
						moves[ j ] = moves[ k ]; moves[ k ] = t;
					}

					var pts = [ { x: s.w / 2, y: top - 6 } ], cx = s.w / 2;
					moves.forEach( function ( m, r ) {
						cx += ( m ? 1 : -1 ) * gapX / 2;
						pts.push( { x: cx, y: top + ( r + 0.5 ) * gapY } );
					} );
					pts.push( { x: ( idx + 0.5 ) * ( s.w / lots.length ), y: s.h - slotH - 6 } );

					function land() {
						landed = idx;
						btn.disabled = played;
						paint();
						say( lot, code );
					}

					if ( slow ) { ball = pts[ pts.length - 1 ]; paint(); return land(); }

					var seg = 0, t0 = performance.now(), per = 170;
					( function step( now ) {
						var p = Math.min( ( now - t0 ) / per, 1 ), a = pts[ seg ], b = pts[ seg + 1 ];
						ball = {
							x: a.x + ( b.x - a.x ) * p,
							y: a.y + ( b.y - a.y ) * p - Math.sin( p * Math.PI ) * 7
						};
						paint();
						if ( p < 1 ) { requestAnimationFrame( step ); }
						else if ( seg < pts.length - 2 ) { seg++; t0 = now; requestAnimationFrame( step ); }
						else { land(); }
					} )( t0 );
				} );
			} );

			return { paint: paint };
		};

		/* ── quiz ── */
		games.quiz = function () {
			var box   = el( 'div', 'jmkg-quiz' );
			var count = el( 'p', 'jmkg-count' );
			var body  = el( 'div' );
			box.appendChild( count );
			box.appendChild( body );
			stage.appendChild( box );
			stage.appendChild( msg );

			var qs = cfg.quiz || [], i = 0, ok = 0;

			function render() {
				if ( ! qs.length ) { return; }
				if ( i >= qs.length ) {
					count.textContent = T.score.replace( '%1$s', ok ).replace( '%2$s', qs.length );
					body.innerHTML = '';
					play( function ( lot, code ) { say( lot, code ); } );
					return;
				}
				var q = qs[ i ];
				count.textContent = ( i + 1 ) + ' / ' + qs.length;
				body.innerHTML = '<p class="jmkg-q">' + esc( q.q ) + '</p>';
				q.o.forEach( function ( o, j ) {
					var b = el( 'button', 'jmkg-opt', esc( o ) );
					b.type = 'button';
					b.addEventListener( 'click', function () {
						if ( j === Number( q.a ) ) { ok++; }
						Array.prototype.forEach.call( body.querySelectorAll( '.jmkg-opt' ), function ( xb, k ) {
							xb.disabled = true;
							if ( k === Number( q.a ) ) { xb.classList.add( 'ok' ); }
							else if ( k === j ) { xb.classList.add( 'no' ); }
						} );
						setTimeout( function () { i++; render(); }, slow ? 10 : 700 );
					} );
					body.appendChild( b );
				} );
			}
			render();

			return { paint: function () {} };
		};

		/* ═══════════════════════════ formulaire ════════════════════════════ */

		var form = el( 'form', 'jmkg-form' );
		form.hidden = true;

		function field( id, label, type ) {
			var f = el( 'div', 'jmkg-field' );
			f.innerHTML = '<label for="' + id + '">' + esc( label ) + '</label>' +
				'<input id="' + id + '" name="' + id + '" type="' + type + '" autocomplete="on">';
			return f;
		}

		var uid = 'jmkg' + Math.random().toString( 36 ).slice( 2, 7 );
		if ( cfg.form.name )  { form.appendChild( field( uid + 'n', T.name, 'text' ) ); }
		form.appendChild( field( uid + 'e', T.email, 'email' ) );
		if ( cfg.form.phone ) { form.appendChild( field( uid + 'p', T.phone, 'tel' ) ); }

		if ( cfg.form.consent ) {
			var privacy = cfg.form.privacy
				? ' <a href="' + esc( cfg.form.privacy ) + '" target="_blank" rel="noopener">' + esc( T.privacy ) + '</a>'
				: '';
			var c = el( 'label', 'jmkg-consent',
				'<input type="checkbox" id="' + uid + 'c"><span>' + esc( T.consent ) + privacy + '</span>' );
			form.appendChild( c );
		}

		var send = el( 'button', 'jmkg-btn', esc( T.send ) );
		send.type = 'submit';
		form.appendChild( send );

		var err = el( 'p', 'jmkg-err' );
		form.appendChild( err );
		root.appendChild( form );

		function showForm() {
			if ( ! cfg.form.on ) { return; }
			form.hidden = false;
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var name  = cfg.form.name ? form.querySelector( '#' + uid + 'n' ).value.trim() : '',
				mail  = form.querySelector( '#' + uid + 'e' ).value.trim().toLowerCase(),
				phone = cfg.form.phone ? form.querySelector( '#' + uid + 'p' ).value.trim() : '',
				agree = cfg.form.consent ? form.querySelector( '#' + uid + 'c' ).checked : true;

			err.className = 'jmkg-err';
			err.textContent = '';

			if ( cfg.form.name && ! name ) { err.textContent = T.errName; return; }
			if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( mail ) ) { err.textContent = T.errMail; return; }
			if ( cfg.form.phone && ! phone ) { err.textContent = T.errPhone; return; }
			if ( ! agree ) { err.textContent = T.errCons; return; }

			var payload = {
				name: name, email: mail, phone: phone,
				lot: lastLot ? lastLot.label : '', code: lastCode,
				token: token, brand: cfg.brand, game: cfg.game
			};

			if ( self.onLead ) { self.onLead( payload ); }

			if ( ! cfg.leadUrl ) { return done(); }

			send.disabled = true;
			var body = new URLSearchParams();
			body.append( 'action', 'jmk_lead' );
			if ( cfg.nonce ) { body.append( 'nonce', cfg.nonce ); }
			Object.keys( payload ).forEach( function ( k ) { body.append( k, payload[ k ] ); } );

			fetch( cfg.leadUrl, { method: 'POST', body: body, credentials: 'omit' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( j ) {
					send.disabled = false;
					if ( j && false === j.success ) {
						err.textContent = ( j.data && j.data.message ) ? j.data.message : T.errNet;
						return;
					}
					done();
				} )
				.catch( function () {
					send.disabled = false;
					err.textContent = T.errNet;
				} );

			function done() {
				err.className = 'jmkg-err ok';
				err.textContent = T.sent;
				send.disabled = true;
			}
		} );

		/* ══════════════════════════ mise en place ══════════════════════════ */

		var built = ( games[ cfg.game ] || games.wheel )();

		/* Un canevas construit alors que son conteneur mesure zéro reste vide
		   pour toujours : onglet en arrière-plan, accordéon fermé, police pas
		   encore appliquée. On redessine dès que la taille devient réelle. */
		function refresh() { built.paint(); }

		if ( global.ResizeObserver ) {
			var ro = new ResizeObserver( refresh );
			ro.observe( root );
		} else {
			global.addEventListener( 'resize', refresh );
		}
		if ( global.IntersectionObserver ) {
			var io = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( en ) { if ( en.isIntersecting ) { refresh(); } } );
			} );
			io.observe( root );
		}
		global.addEventListener( 'load', refresh );
		if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( refresh ); }
		refresh();

		this.el      = root;
		this.config  = cfg;
		this.refresh = refresh;
		this.onLead  = null;
		this.destroy = function () {
			if ( root.parentNode ) { root.parentNode.removeChild( root ); }
		};
	}

	/* ────────────────────────────── interface ───────────────────────────── */

	var SKINS = [ 'elegant', 'arcade', 'casino' ];

	var ALIASES = {
		roue: 'wheel', wheel: 'wheel',
		grattage: 'scratch', scratch: 'scratch',
		tap: 'tap',
		machine: 'slot', slot: 'slot',
		plinko: 'plinko', pluie: 'plinko',
		quiz: 'quiz'
	};

	function normalise( raw ) {
		var cfg = assign( {}, BASE, raw || {} );
		cfg.t    = assign( {}, BASE.t, ( raw && raw.t ) || {} );
		cfg.form = assign( {}, BASE.form, ( raw && raw.form ) || {} );
		cfg.game = ALIASES[ String( cfg.game ).toLowerCase() ] || 'wheel';
		if ( SKINS.indexOf( cfg.skin ) < 0 ) { cfg.skin = 'elegant'; }
		cfg.lots = ( cfg.lots || [] ).filter( function ( l ) { return l && '' !== l.label; } );
		if ( ! cfg.lots.length ) { cfg.lots = BASE.lots.slice(); }
		return cfg;
	}

	var JMKGame = {
		version: VERSION,

		/**
		 * Pose un jeu dans un conteneur.
		 *
		 * @param {string|Element} target Sélecteur ou élément d'accueil.
		 * @param {Object}         config Réglages ; voir BASE.
		 * @return {Game|null}
		 */
		mount: function ( target, config ) {
			injectCss();
			var host = ( 'string' === typeof target ) ? document.querySelector( target ) : target;
			if ( ! host ) { return null; }
			return new Game( host, normalise( config ) );
		},

		defaults: function () { return JSON.parse( JSON.stringify( BASE ) ); }
	};

	/* Démarrage automatique depuis les attributs du <script>. */
	function autoboot() {
		var s = document.currentScript ||
			( function () {
				var all = document.querySelectorAll( 'script[data-jeu]' );
				return all.length ? all[ all.length - 1 ] : null;
			} )();
		if ( ! s || ! s.getAttribute( 'data-jeu' ) ) { return; }

		var d = s.dataset;
		var cfg = {
			game:    d.jeu,
			accent:  d.couleur || BASE.accent,
			skin:    d.style || BASE.skin,
			dir:     ( 'ar' === d.langue ) ? 'rtl' : 'ltr',
			brand:   d.marque || '',
			drawUrl: d.tirage || '',
			leadUrl: d.webhook || '',
			title:   d.titre || BASE.title,
			subtitle: d.soustitre || BASE.subtitle
		};
		if ( d.lots ) {
			try { cfg.lots = JSON.parse( d.lots ); } catch ( e ) {}
		}
		if ( d.rgpd ) { cfg.form = { privacy: d.rgpd }; }

		var host = d.cible ? document.querySelector( d.cible ) : null;
		if ( ! host ) {
			host = el( 'div' );
			s.parentNode.insertBefore( host, s );
		}
		JMKGame.mount( host, cfg );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', autoboot );
	} else {
		autoboot();
	}

	global.JMKGame = JMKGame;

	if ( 'undefined' !== typeof module && module.exports ) { module.exports = JMKGame; }

} )( typeof window !== 'undefined' ? window : this );
