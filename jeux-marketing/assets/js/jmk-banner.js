/*!
 * Jeux Marketing — bannières HTML5.
 *
 * Un seul fichier, aucune dépendance. Il rend une même création dans
 * n'importe quel format d'affichage, et c'est tout l'enjeu : un 728×90 et un
 * 160×600 ne sont pas le même dessin agrandi, ce sont deux mises en page.
 *
 * Conforme à ce qu'attendent les régies :
 *
 *   <meta name="ad.size" content="width=300,height=250">
 *   var clickTag = "https://…";
 *
 * Le clic passe par clickTag, jamais par un href en dur : c'est ce que la
 * régie réécrit pour compter le clic.
 */
( function ( global ) {
	'use strict';

	var VERSION = '1.0.0';

	var FONT = 'system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';

	/* Repli quand il n'y a pas de navigateur : largeur moyenne d'un caractère
	   en fraction de la taille de police. Une approximation, et elle se voyait
	   — à 0,55 le mot « Roasted » débordait d'un 300×600 alors que le calcul
	   le déclarait à l'aise. D'où la mesure réelle juste en dessous. */
	var CHAR_W = 0.60;
	var SPACE_W = 0.28;

	var gauge = null;

	/**
	 * Largeur réelle d'un texte.
	 *
	 * Passe par un canevas hors écran : measureText donne la vraie largeur,
	 * dans la vraie police, sans rien insérer dans la page ni déclencher de
	 * recalcul de mise en page. C'est ce qui permet de faire tenir vingt
	 * formats d'un coup sans les mesurer un par un dans le DOM.
	 *
	 * @param {string} text   Le texte.
	 * @param {number} size   Taille de police.
	 * @param {string} weight Graisse CSS.
	 * @return {number}
	 */
	function textW( text, size, weight ) {
		if ( ! gauge && global.document && global.document.createElement ) {
			var cv = global.document.createElement( 'canvas' );
			gauge = cv.getContext ? cv.getContext( '2d' ) : null;
		}
		if ( gauge ) {
			gauge.font = ( weight || '800' ) + ' ' + size + 'px ' + FONT;
			return gauge.measureText( text ).width;
		}
		return String( text ).length * CHAR_W * size;
	}

	/* ───────────────────────── formats d'affichage ───────────────────────── */

	/* Le jeu standard du Réseau Display de Google, plus les formats mobiles.
	   `star` marque ceux qui portent l'essentiel du volume : c'est la
	   sélection par défaut, et celle qu'on vend en petit paquet. */
	var SIZES = [
		{ w: 300,  h: 250,  name: 'Medium Rectangle',     star: true },
		{ w: 336,  h: 280,  name: 'Large Rectangle' },
		{ w: 728,  h: 90,   name: 'Leaderboard',          star: true },
		{ w: 300,  h: 600,  name: 'Half Page',            star: true },
		{ w: 160,  h: 600,  name: 'Wide Skyscraper',      star: true },
		{ w: 320,  h: 50,   name: 'Mobile Leaderboard',   star: true },
		{ w: 320,  h: 100,  name: 'Large Mobile Banner',  star: true },
		{ w: 970,  h: 250,  name: 'Billboard' },
		{ w: 970,  h: 90,   name: 'Large Leaderboard' },
		{ w: 468,  h: 60,   name: 'Banner' },
		{ w: 250,  h: 250,  name: 'Square' },
		{ w: 200,  h: 200,  name: 'Small Square' },
		{ w: 240,  h: 400,  name: 'Vertical Rectangle' },
		{ w: 120,  h: 600,  name: 'Skyscraper' },
		{ w: 300,  h: 1050, name: 'Portrait' },
		{ w: 980,  h: 120,  name: 'Panorama' },
		{ w: 930,  h: 180,  name: 'Top Banner' },
		{ w: 250,  h: 360,  name: 'Triple Widescreen' },
		{ w: 580,  h: 400,  name: 'Netboard' },
		{ w: 750,  h: 200,  name: 'Half Horizontal' },
		{ w: 300,  h: 50,   name: 'Mobile Banner' },
		{ w: 320,  h: 480,  name: 'Mobile Interstitial' }
	];

	/* ───────────────────────────── réglages ───────────────────────────── */

	var BASE = {
		w: 300,
		h: 250,

		brand:  '',
		logo:   '',
		frames: [ { kicker: '', title: 'Votre message', sub: '' } ],
		cta:    'En savoir plus',

		bg:     '#0B3325',
		bg2:    '',
		ink:    '#FFFBF0',
		accent: '#D9A441',

		clickUrl: '',
		hold:     2200,
		loops:    3,
		border:   true,
		sheen:    true,

		/* Imagerie. `image` couvre le fond, `cutout` se pose au bord — une
		   découpe de produit sur fond transparent. Les deux acceptent une
		   adresse ou une donnée en ligne (data:). */
		image:  '',
		cutout: '',
		// Le voile sombre posé sur l'image. Sans lui, un fond photographique
		// avale le texte : c'est la première chose qui rate quand on colle
		// une photo derrière une accroche.
		scrim:  0.58,
		focus:  '50% 50%'
	};

	/* ───────────────────────────── outillage ───────────────────────────── */

	function assign( t ) {
		for ( var i = 1; i < arguments.length; i++ ) {
			var s = arguments[ i ];
			if ( ! s ) { continue; }
			for ( var k in s ) {
				if ( Object.prototype.hasOwnProperty.call( s, k ) ) { t[ k ] = s[ k ]; }
			}
		}
		return t;
	}

	function esc( s ) {
		return String( s ).replace( /[<>&"]/g, function ( c ) {
			return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[ c ];
		} );
	}

	function clamp( v, lo, hi ) {
		return Math.max( lo, Math.min( hi, v ) );
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

	/* Encre lisible sur un fond donné : le bouton doit rester lisible quelle
	   que soit la couleur de marque, et personne ne relit vingt formats. */
	function inkOn( hex ) {
		return hexToHsl( hex )[ 2 ] > 58 ? '#141210' : '#FFFBF0';
	}

	/**
	 * Une couleur du thème, en transparence.
	 *
	 * @param {string} hex   Couleur.
	 * @param {number} alpha Opacité.
	 * @return {string}
	 */
	function hexA( hex, alpha ) {
		hex = String( hex ).replace( '#', '' );
		if ( 3 === hex.length ) {
			hex = hex.split( '' ).map( function ( c ) { return c + c; } ).join( '' );
		}
		return 'rgba(' + parseInt( hex.slice( 0, 2 ), 16 ) + ',' +
			parseInt( hex.slice( 2, 4 ), 16 ) + ',' +
			parseInt( hex.slice( 4, 6 ), 16 ) + ',' +
			Math.round( clamp( alpha, 0, 1 ) * 100 ) / 100 + ')';
	}

	function shade( hex, delta ) {
		var c = hexToHsl( hex );
		return 'hsl(' + Math.round( ( ( c[ 0 ] % 360 ) + 360 ) % 360 ) + ' ' +
			Math.round( clamp( c[ 1 ], 0, 100 ) ) + '% ' +
			Math.round( clamp( c[ 2 ] + delta, 0, 100 ) ) + '%)';
	}

	/* Le texte sans les étoiles d'emphase : elles ne s'affichent pas, elles ne
	   doivent donc pas compter dans la largeur. */
	function plain( s ) {
		return String( s || '' ).replace( /\*/g, '' );
	}

	/**
	 * Nombre de lignes qu'occupe un texte, et si le plus long mot passe.
	 *
	 * @param {string} text  Le texte.
	 * @param {number} size  Taille de police.
	 * @param {number} availW Largeur disponible.
	 * @return {Object} { lines, fits }
	 */
	function wrap( text, size, availW, weight ) {
		var words = plain( text ).split( /\s+/ ).filter( Boolean );
		if ( ! words.length ) { return { lines: 0, fits: true }; }

		var lines = 1, cur = 0, fits = true;

		for ( var i = 0; i < words.length; i++ ) {
			var wWidth = textW( words[ i ], size, weight );
			// Un mot plus large que la colonne déborde quoi qu'il arrive :
			// aucune césure ne le sauvera, il faut réduire la police.
			if ( wWidth > availW ) { fits = false; }

			if ( 0 === cur ) {
				cur = wWidth;
			} else if ( cur + SPACE_W * size + wWidth <= availW ) {
				cur += SPACE_W * size + wWidth;
			} else {
				lines++;
				cur = wWidth;
			}
		}
		return { lines: lines, fits: fits };
	}

	/* ─────────────────────────── mise en page ─────────────────────────── */

	/**
	 * Le gabarit d'un format.
	 *
	 * Quatre familles, choisies sur le rapport largeur/hauteur, parce que
	 * c'est lui qui décide si le texte tient à côté du bouton ou doit passer
	 * dessous.
	 *
	 * La taille du titre, elle, n'est pas une fraction de la hauteur : elle
	 * est *cherchée*. On part de la plus grande plausible et on descend
	 * jusqu'à ce que le bloc tienne vraiment dans la place restante. Sans
	 * cela, le même texte déborde d'un 200×200 et flotte dans un 970×250.
	 *
	 * @param {number} w   Largeur.
	 * @param {number} h   Hauteur.
	 * @param {Object} cfg Réglages, pour le texte à faire tenir.
	 * @return {Object}
	 */
	function layout( w, h, cfg ) {
		var ratio = w / h,
			min   = Math.min( w, h ),
			kind;

		if ( h <= 110 && ratio >= 3 ) {
			kind = 'strip';        // 728×90, 320×50 : une seule ligne.
		} else if ( ratio >= 1.9 ) {
			kind = 'wide';         // 970×250, 580×400 : texte à gauche, bouton à droite.
		} else if ( ratio <= 0.55 ) {
			kind = 'tall';         // 160×600, 300×1050 : tout empilé, très haut.
		} else {
			kind = 'box';          // 300×250, 336×280 : le cas courant.
		}

		var row  = ( 'strip' === kind || 'wide' === kind ),
			unit = row ? h : min,
			pad  = Math.max( 6, Math.round( unit * ( 'strip' === kind ? 0.13 : 0.075 ) ) ),
			gap  = Math.max( 3, Math.round( unit * 0.035 ) );

		/* Les tailles secondaires viennent de la boîte, pas du titre : sinon
		   le bouton dépend du titre qui dépend de la place que laisse le
		   bouton, et le calcul tourne en rond. */
		var ctaFs    = clamp( Math.round( unit * ( row ? 0.17 : 0.095 ) ), 9, 19 ),
			kickerFs = clamp( Math.round( unit * 0.055 ), 8, 13 ),
			subFs    = clamp( Math.round( unit * 0.072 ), 9, 17 ),
			brandFs  = clamp( Math.round( unit * ( row ? 0.20 : 0.10 ) ), 9, 22 ),
			logoH    = clamp( Math.round( unit * ( row ? 0.42 : 0.17 ) ), 12, 64 );

		var ctaPadX = Math.round( ctaFs * 1.25 ),
			ctaPadY = Math.round( ctaFs * 0.6 ),
			ctaW    = textW( plain( cfg.cta ), ctaFs, '800' ) + ctaPadX * 2,
			ctaH    = ctaFs * 1.15 + ctaPadY * 2;

		var brandW = cfg.logo
			? logoH * 2.4
			: textW( plain( cfg.brand ), brandFs, '800' );

		var hasCta   = !! plain( cfg.cta ),
			hasBrand = !! ( cfg.logo || plain( cfg.brand ) );

		/* En pile, le nom de marque occupe toute la largeur et ne se coupe
		   pas — il est en `nowrap`, sinon « anomalydev by Hsk » se casserait
		   en deux lignes au milieu du mot. Il faut donc le faire tenir : on
		   réduit sa taille, et s'il ne rentre toujours pas, on le retire. Le
		   message passe avant la signature. */
		if ( ! row && hasBrand && ! cfg.logo ) {
			var brandRoom = w - pad * 2;
			while ( brandFs > 9 && textW( plain( cfg.brand ), brandFs, '800' ) > brandRoom ) {
				brandFs--;
			}
			if ( textW( plain( cfg.brand ), brandFs, '800' ) > brandRoom ) {
				hasBrand = false;
			}
			brandW = hasBrand ? textW( plain( cfg.brand ), brandFs, '800' ) : 0;
		}

		/* La place qui reste au texte, une fois logo et bouton posés. */
		var availW, availH;
		if ( row ) {
			availW = w - pad * 2 - ( hasBrand ? brandW + gap : 0 ) - ( hasCta ? ctaW + gap : 0 );
			availH = h - pad * 2;
		} else {
			availW = w - pad * 2;
			availH = h - pad * 2 -
				( hasBrand ? ( cfg.logo ? logoH : brandFs * 1.2 ) + gap : 0 ) -
				( hasCta ? ctaH + Math.round( gap * 1.6 ) : 0 );
		}
		/* Sur un bandeau court, marque et bouton peuvent ne rien laisser au
		   message — un 320×100 finissait avec une colonne de texte de trois
		   caractères de large. Le message prime : c'est la marque qui saute. */
		if ( row && hasBrand && availW < w * 0.34 ) {
			hasBrand = false;
			availW   = w - pad * 2 - ( hasCta ? ctaW + gap : 0 );
		}

		availW = Math.max( 24, availW );
		availH = Math.max( 16, availH );

		/* Le pire cas parmi les messages : la mise en page est la même pour
		   tous, elle doit donc tenir pour le plus long. */
		function blockHeight( size, withKicker, withSub ) {
			var worst = 0;
			for ( var i = 0; i < cfg.frames.length; i++ ) {
				var f = cfg.frames[ i ],
					t = wrap( f.title, size, availW, '800' );
				if ( ! t.fits ) { return Infinity; }

				var tall = t.lines * size * 1.08;
				if ( withKicker && f.kicker ) {
					tall += kickerFs * 1.25 + gap * 0.6;
				}
				if ( withSub && f.sub ) {
					var s = wrap( f.sub, subFs, availW, '500' );
					if ( ! s.fits ) { return Infinity; }
					tall += s.lines * subFs * 1.25 + gap * 0.7;
				}
				worst = Math.max( worst, tall );
			}
			return worst;
		}

		/* Trois niveaux de richesse. On garde le plus complet qui laisse au
		   titre une taille encore lisible ; en dessous, mieux vaut un titre
		   net qu'un sous-titre illisible. */
		var levels = [
				{ kicker: true,  sub: true },
				{ kicker: true,  sub: false },
				{ kicker: false, sub: false }
			],
			floor = 'strip' === kind ? 11 : 13,
			start = Math.round( unit * ( 'strip' === kind ? 0.34 : 0.22 ) ),
			best  = null;

		for ( var li = 0; li < levels.length && ! best; li++ ) {
			for ( var size = start; size >= 9; size-- ) {
				if ( blockHeight( size, levels[ li ].kicker, levels[ li ].sub ) <= availH ) {
					// Un titre trop petit ne vaut pas qu'on garde le reste :
					// on retente sans, au niveau suivant.
					if ( size < floor && li < levels.length - 1 ) { break; }
					best = { size: size, level: levels[ li ] };
					break;
				}
			}
		}

		if ( ! best ) {
			best = { size: 9, level: { kicker: false, sub: false } };
		}

		/* La hauteur réelle du bloc de messages. La pile est en position
		   absolue — elle ne pousse plus rien — donc il faut la lui rendre à
		   la main. Une valeur au jugé (« trois fois et demie le titre »)
		   débordait : le bouton passait sous le bord et disparaissait dans
		   l'image de repli. */
		var blockH = blockHeight( best.size, best.level.kicker, best.level.sub );
		if ( ! isFinite( blockH ) ) { blockH = best.size * 2; }

		return {
			blockH:  Math.ceil( blockH ),
			kind:    kind,
			row:     row,
			pad:     pad,
			gap:     gap,
			title:   best.size,
			sub:     subFs,
			kicker:  kickerFs,
			brand:   brandFs,
			logoH:   logoH,
			cta:     ctaFs,
			ctaPadX: ctaPadX,
			ctaPadY: ctaPadY,
			hasCta:   hasCta,
			hasBrand: hasBrand,
			showSub:    best.level.sub,
			showKicker: best.level.kicker
		};
	}

	/* ─────────────────────────────── styles ───────────────────────────── */

	/**
	 * La feuille de style d'une bannière.
	 *
	 * Toutes les règles sont préfixées par le sélecteur de la racine. Le
	 * studio en affiche vingt sur une page : sans préfixe, la dernière
	 * montée redessinerait les dix-neuf autres.
	 *
	 * @param {Object} cfg Réglages.
	 * @param {Object} L   Gabarit.
	 * @param {string} sel Sélecteur de la racine.
	 * @param {boolean} page Vraie page exportée (fixe la taille du document).
	 * @return {string}
	 */
	function css( cfg, L, sel, page ) {
		var accentInk = inkOn( cfg.accent ),
			bg2       = cfg.bg2 || shade( cfg.bg, 8 ),
			out       = [];

		if ( page ) {
			out.push( '*{margin:0;padding:0;box-sizing:border-box}' );
			out.push( 'html,body{width:' + cfg.w + 'px;height:' + cfg.h + 'px;overflow:hidden;background:' + cfg.bg + '}' );
		}

		out.push( sel + ',' + sel + ' *{margin:0;padding:0;box-sizing:border-box}' );

		out.push( sel + '{position:relative;width:' + cfg.w + 'px;height:' + cfg.h + 'px;overflow:hidden;' +
			'cursor:pointer;color:' + cfg.ink + ';' +
			'font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;' +
			'-webkit-font-smoothing:antialiased;' +
			'background:linear-gradient(' + ( L.row ? '100deg' : '170deg' ) + ',' + bg2 + ',' + cfg.bg + ' 62%);' +
			( cfg.border ? 'border:1px solid rgba(255,255,255,.22);' : '' ) + '}' );

		if ( cfg.image ) {
			/* La photo, puis le voile. L'ordre compte : le dégradé part du
			   côté où vit le texte et s'ouvre vers l'image, pour qu'on voie
			   la matière sans perdre un mot. */
			out.push( sel + ' .jmkb-bg{position:absolute;inset:0;pointer-events:none;' +
				'background-image:url(' + cfg.image + ');background-size:cover;' +
				'background-position:' + cfg.focus + '}' );

			var dir = L.row ? '95deg' : '175deg';
			out.push( sel + ' .jmkb-scrim{position:absolute;inset:0;pointer-events:none;' +
				'background:linear-gradient(' + dir + ',' +
				hexA( cfg.bg, Math.min( 0.97, cfg.scrim + 0.28 ) ) + ' 0%,' +
				hexA( cfg.bg, cfg.scrim ) + ' 46%,' +
				hexA( cfg.bg, Math.max( 0, cfg.scrim - 0.34 ) ) + ' 100%)}' );
		}

		if ( cfg.cutout ) {
			/* La découpe mord sur le bord opposé au texte. En bandeau elle
			   tient dans sa colonne ; en boîte elle passe derrière, sinon il
			   ne reste plus rien pour le message. */
			out.push( sel + ' .jmkb-cut{position:absolute;pointer-events:none;' +
				( L.row
					? 'right:0;top:0;height:100%;width:34%;'
					: 'right:-6%;bottom:-4%;height:52%;width:62%;' ) +
				'background-image:url(' + cfg.cutout + ');background-size:contain;' +
				'background-repeat:no-repeat;background-position:' +
				( L.row ? 'right center' : 'right bottom' ) + ';' +
				'filter:drop-shadow(0 6px 18px rgba(0,0,0,.5))}' );
		}

		/* Halo de marque : du relief sans un octet d'image. */
		out.push( sel + '::before{content:"";position:absolute;pointer-events:none;z-index:1;' +
			( L.row ? 'right:-10%;top:-40%;width:46%;height:180%' : 'left:-24%;top:-32%;width:150%;height:72%' ) + ';' +
			'background:radial-gradient(circle,' + cfg.accent + '2E,transparent 68%)}' );

		if ( cfg.sheen ) {
			/* Le voile qui balaie la surface : c'est ce mouvement de fond qui
			   sépare une bannière d'une image fixe. */
			out.push( sel + ' .jmkb-sheen{position:absolute;top:-60%;width:26%;height:220%;pointer-events:none;' +
				'background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);' +
				'transform:rotate(14deg);animation:jmkbSweep 4.6s ease-in-out infinite}' );
			out.push( '@keyframes jmkbSweep{0%{left:-40%}55%,100%{left:130%}}' );
		}

		out.push( sel + ' .jmkb-stage{position:absolute;inset:0;z-index:2;padding:' + L.pad + 'px;display:flex;align-items:center;' +
			( L.row
				? 'gap:' + L.gap + 'px;'
				: 'flex-direction:column;justify-content:center;text-align:center;' ) + '}' );

		out.push( sel + ' .jmkb-msgs{position:relative;flex:1 1 auto;min-width:0;' +
			( L.row ? 'align-self:center;' : 'width:100%;' ) + '}' );

		out.push( sel + ' .jmkb-f{' + ( L.row ? '' : 'position:absolute;left:0;right:0;top:50%;' ) +
			( L.row ? 'position:absolute;left:0;right:0;top:50%;' : '' ) +
			'transform:translateY(calc(-50% + ' + Math.round( L.title * 0.45 ) + 'px));opacity:0;' +
			'transition:opacity .42s ease,transform .42s cubic-bezier(.2,.7,.3,1)}' );
		out.push( sel + ' .jmkb-f.jmkb-on{opacity:1;transform:translateY(-50%)}' );
		out.push( sel + ' .jmkb-f.jmkb-out{opacity:0;transition-duration:.3s;' +
			'transform:translateY(calc(-50% - ' + Math.round( L.title * 0.38 ) + 'px))}' );

		/* La pile est absolue : elle ne pousse plus le conteneur. On lui rend
		   la hauteur du plus grand message — celle qui a servi à choisir la
		   taille du titre, pas une approximation. */
		out.push( sel + ' .jmkb-msgs{height:' + L.blockH + 'px}' );

		out.push( sel + ' .jmkb-k{font-size:' + L.kicker + 'px;font-weight:700;letter-spacing:.14em;' +
			'text-transform:uppercase;color:' + cfg.accent + ';margin-bottom:' + Math.round( L.gap * 0.6 ) + 'px}' );
		out.push( sel + ' .jmkb-t{font-size:' + L.title + 'px;font-weight:800;line-height:1.08;letter-spacing:-.02em}' );
		out.push( sel + ' .jmkb-t em{font-style:normal;color:' + cfg.accent + '}' );
		out.push( sel + ' .jmkb-s{font-size:' + L.sub + 'px;font-weight:500;line-height:1.25;opacity:.82;' +
			'margin-top:' + Math.round( L.gap * 0.7 ) + 'px}' );

		out.push( sel + ' .jmkb-logo{flex:0 0 auto;' + ( L.row ? '' : 'margin-bottom:' + L.gap + 'px;' ) + '}' );
		out.push( sel + ' .jmkb-logo img{display:block;height:' + L.logoH + 'px;width:auto;' +
			'max-width:' + Math.round( cfg.w * ( L.row ? 0.22 : 0.5 ) ) + 'px;object-fit:contain}' );
		out.push( sel + ' .jmkb-logo span{display:block;white-space:nowrap;font-size:' + L.brand + 'px;' +
			'font-weight:800;letter-spacing:-.01em}' );

		out.push( sel + ' .jmkb-cta{flex:0 0 auto;white-space:nowrap;' +
			( L.row ? '' : 'margin-top:' + Math.round( L.gap * 1.6 ) + 'px;' ) +
			'background:' + cfg.accent + ';color:' + accentInk + ';' +
			'font-size:' + L.cta + 'px;font-weight:800;line-height:1.15;' +
			'padding:' + L.ctaPadY + 'px ' + L.ctaPadX + 'px;' +
			'border-radius:' + Math.round( L.cta * 1.4 ) + 'px;' +
			'box-shadow:0 ' + Math.round( L.cta * 0.22 ) + 'px ' + Math.round( L.cta * 0.7 ) + 'px rgba(0,0,0,.34);' +
			'animation:jmkbPulse 2.4s ease-in-out infinite}' );
		out.push( '@keyframes jmkbPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}' );

		out.push( '@media(prefers-reduced-motion:reduce){' + sel + ' .jmkb-cta,' + sel + ' .jmkb-sheen{animation:none}' +
			sel + ' .jmkb-f{transition:none}}' );

		return out.join( '' );
	}

	/* ─────────────────────────────── balisage ─────────────────────────── */

	/* *un mot* passe en couleur d'accent. C'est la seule mise en forme
	   acceptée dans un titre : le reste appartient à la mise en page. */
	function emphasis( s ) {
		return esc( s ).replace( /\*([^*]+)\*/g, '<em>$1</em>' );
	}

	function frameHtml( f, L ) {
		var out = '';
		if ( L.showKicker && f.kicker ) { out += '<div class="jmkb-k">' + esc( f.kicker ) + '</div>'; }
		out += '<div class="jmkb-t">' + emphasis( f.title ) + '</div>';
		if ( L.showSub && f.sub ) { out += '<div class="jmkb-s">' + esc( f.sub ) + '</div>'; }
		return out;
	}

	function bodyHtml( cfg, L ) {
		var logo = '';
		if ( L.hasBrand ) {
			logo = cfg.logo
				? '<div class="jmkb-logo"><img src="' + esc( cfg.logo ) + '" alt=""></div>'
				: '<div class="jmkb-logo"><span>' + esc( cfg.brand ) + '</span></div>';
		}

		var frames = cfg.frames.map( function ( f, i ) {
			return '<div class="jmkb-f' + ( 0 === i ? ' jmkb-on' : '' ) + '">' + frameHtml( f, L ) + '</div>';
		} ).join( '' );

		return ( cfg.image ? '<div class="jmkb-bg"></div><div class="jmkb-scrim"></div>' : '' ) +
			( cfg.cutout ? '<div class="jmkb-cut"></div>' : '' ) +
			( cfg.sheen ? '<div class="jmkb-sheen"></div>' : '' ) +
			'<div class="jmkb-stage">' + logo +
			'<div class="jmkb-msgs">' + frames + '</div>' +
			( L.hasCta ? '<div class="jmkb-cta">' + esc( cfg.cta ) + '</div>' : '' ) +
			'</div>';
	}

	/* ─────────────────────────────── minutage ─────────────────────────── */

	/**
	 * Fait défiler les messages, puis s'arrête.
	 *
	 * Les régies plafonnent l'animation : trois boucles au plus, et la
	 * dernière image reste à l'écran — c'est elle qui porte la marque et le
	 * bouton quand plus rien ne bouge.
	 *
	 * Rend une poignée pour l'arrêter. Le minuteur se replante à chaque
	 * message : garder seulement le premier identifiant ne suffit pas à
	 * l'arrêter, et une bannière retirée de la page continuerait de battre
	 * dans le vide — vingt aperçus dans le studio, vingt chaînes orphelines.
	 *
	 * @param {Element} root Racine.
	 * @param {Object}  cfg  Réglages.
	 * @return {Object} { stop }
	 */
	function run( root, cfg ) {
		var frames = [].slice.call( root.querySelectorAll( '.jmkb-f' ) );
		if ( frames.length < 2 ) { return { stop: function () {} }; }

		var i = 0, loop = 0, timer = 0, fade = 0, stopped = false;

		( function step() {
			timer = setTimeout( function () {
				if ( stopped ) { return; }

				var cur  = frames[ i ],
					next = frames[ ( i + 1 ) % frames.length ];

				if ( 0 === ( i + 1 ) % frames.length ) {
					loop++;
					if ( loop >= cfg.loops ) { return; }
				}

				cur.classList.remove( 'jmkb-on' );
				cur.classList.add( 'jmkb-out' );
				next.classList.remove( 'jmkb-out' );

				// Un temps mort entre les deux : les deux textes ne doivent
				// jamais être lisibles en même temps.
				fade = setTimeout( function () { next.classList.add( 'jmkb-on' ); }, 220 );

				i = ( i + 1 ) % frames.length;
				step();
			}, cfg.hold );
		} )();

		return {
			stop: function () {
				stopped = true;
				clearTimeout( timer );
				clearTimeout( fade );
			}
		};
	}

	/* ─────────────────────────────── montage ───────────────────────────── */

	function normalise( raw ) {
		var cfg = assign( {}, BASE, raw || {} );
		cfg.w = Math.max( 40, parseInt( cfg.w, 10 ) || BASE.w );
		cfg.h = Math.max( 40, parseInt( cfg.h, 10 ) || BASE.h );
		cfg.frames = ( cfg.frames || [] ).filter( function ( f ) { return f && plain( f.title ); } );
		if ( ! cfg.frames.length ) { cfg.frames = BASE.frames.slice(); }
		cfg.loops = clamp( parseInt( cfg.loops, 10 ) || 3, 1, 3 );
		cfg.hold  = clamp( parseInt( cfg.hold, 10 ) || 2200, 800, 8000 );
		cfg.scrim = clamp( 'number' === typeof cfg.scrim ? cfg.scrim : 0.58, 0, 1 );

		/* Une image de fond change la place du texte : sur une photo, le
		   sous-titre en petit devient illisible quoi qu'on fasse. On garde la
		   photo, on remonte le contraste, et le reste suit. */
		if ( cfg.image && cfg.scrim < 0.3 ) { cfg.scrim = 0.3; }
		return cfg;
	}

	var seq = 0;

	var JMKBanner = {
		version: VERSION,
		sizes:   function () { return SIZES.map( function ( s ) { return assign( {}, s ); } ); },
		layout:  function ( w, h, cfg ) { return layout( w, h, normalise( cfg ) ); },

		/**
		 * Rend une bannière dans un conteneur.
		 *
		 * @param {string|Element} target Cible.
		 * @param {Object}         config Réglages.
		 * @return {Object|null}
		 */
		mount: function ( target, config ) {
			var host = ( 'string' === typeof target ) ? document.querySelector( target ) : target;
			if ( ! host ) { return null; }

			var cfg = normalise( config ),
				L   = layout( cfg.w, cfg.h, cfg ),
				id  = 'jmkb' + ( ++seq ) + Math.random().toString( 36 ).slice( 2, 6 );

			var style = document.createElement( 'style' );
			style.textContent = css( cfg, L, '#' + id, false );

			var root = document.createElement( 'div' );
			root.id = id;
			root.innerHTML = bodyHtml( cfg, L );

			host.appendChild( style );
			host.appendChild( root );

			root.addEventListener( 'click', function () {
				var url = global.clickTag || cfg.clickUrl;
				if ( url ) { global.open( url, '_blank' ); }
			} );

			var reel = run( root, cfg );

			return {
				el:     root,
				config: cfg,
				layout: L,
				destroy: function () {
					reel.stop();
					if ( root.parentNode ) { root.parentNode.removeChild( root ); }
					if ( style.parentNode ) { style.parentNode.removeChild( style ); }
				}
			};
		},

		/**
		 * La page complète d'une bannière, prête pour la régie.
		 *
		 * Avec `opts.still`, rend l'image de repli : le dernier message,
		 * figé, sans minuteur ni animation. Les régies l'exigent — c'est ce
		 * qui s'affiche là où le HTML5 ne passe pas, et c'est le dernier
		 * message qu'il faut, celui qui porte la marque et le bouton. Une
		 * capture au premier écran donnerait une image sans appel à l'action.
		 *
		 * @param {Object} config Réglages.
		 * @param {Object} opts   { still }.
		 * @return {string}
		 */
		page: function ( config, opts ) {
			opts = opts || {};

			var cfg = normalise( config ),
				L   = layout( cfg.w, cfg.h, cfg ),
				still = !! opts.still;

			if ( still ) {
				// Le repli ne garde que la dernière image, déjà en place.
				cfg = assign( {}, cfg, {
					frames: [ cfg.frames[ cfg.frames.length - 1 ] ],
					sheen:  false
				} );
			}

			var head = '<!doctype html>\n<html lang="en">\n<head>\n' +
				'<meta charset="utf-8">\n' +
				'<meta name="ad.size" content="width=' + cfg.w + ',height=' + cfg.h + '">\n' +
				'<title>' + esc( cfg.brand || 'Banner' ) + ' ' + cfg.w + 'x' + cfg.h +
				( still ? ' (backup)' : '' ) + '</title>\n';

			if ( ! still ) {
				head += '<script type="text/javascript">var clickTag = "' + esc( cfg.clickUrl ) + '";<\/script>\n';
			}

			var sheet = css( cfg, L, '#ad', true );
			if ( still ) {
				// Ni pulsation ni transition : la capture doit être stable.
				sheet += '#ad .jmkb-cta{animation:none}#ad .jmkb-f{transition:none;opacity:1}';
			}

			return head + '<style>' + sheet + '</style>\n' +
				'</head>\n<body>\n<div id="ad">' + bodyHtml( cfg, L ) + '</div>\n' +
				( still ? '' : '<script type="text/javascript">\n' + runtime( cfg ) + '\n<\/script>\n' ) +
				'</body>\n</html>\n';
		}
	};

	/* Le minuteur recopié dans la page exportée. Écrit à la main plutôt que
	   sérialisé depuis run() : une fonction passée par toString() emporte son
	   contexte, et ce contexte n'existe pas dans le fichier livré. */
	function runtime( cfg ) {
		return [
			'(function(){',
			'var ad=document.getElementById("ad");',
			'ad.addEventListener("click",function(){',
			'  var u=window.clickTag||"";if(u){window.open(u,"_blank");}',
			'});',
			'var f=[].slice.call(ad.querySelectorAll(".jmkb-f"));',
			'if(f.length<2){return;}',
			'var i=0,l=0,H=' + cfg.hold + ',L=' + cfg.loops + ';',
			'(function s(){setTimeout(function(){',
			'  var c=f[i],n=f[(i+1)%f.length];',
			'  if((i+1)%f.length===0){l++;if(l>=L){return;}}',
			'  c.classList.remove("jmkb-on");c.classList.add("jmkb-out");n.classList.remove("jmkb-out");',
			'  setTimeout(function(){n.classList.add("jmkb-on");},220);',
			'  i=(i+1)%f.length;s();',
			'},H);})();',
			'})();'
		].join( '\n' );
	}

	global.JMKBanner = JMKBanner;

	if ( 'undefined' !== typeof module && module.exports ) { module.exports = JMKBanner; }

} )( typeof window !== 'undefined' ? window : this );
