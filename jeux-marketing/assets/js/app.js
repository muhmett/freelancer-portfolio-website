/* Jeux Marketing — script public */
( function () {
	'use strict';

	if ( typeof window.JMK === 'undefined' ) {
		return;
	}

	var C       = window.JMK;
	var T       = C.i18n;
	var LOTS    = ( C.lots || [] ).map( function ( l ) { return Object.assign( {}, l ); } );
	var OPTS    = ( C.options || [] ).map( function ( o ) { return Object.assign( {}, o ); } );
	var QUIZ    = C.quiz || [];
	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var brandName  = C.brand || '';
	var brandColor = C.accent || '#D9A441';
	var lang       = C.lang || 'fr';

	function $( s )  { return document.querySelector( s ); }
	function $$( s ) { return Array.prototype.slice.call( document.querySelectorAll( s ) ); }
	function esc( s ) {
		return String( s ).replace( /[<>&"]/g, function ( c ) {
			return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[ c ];
		} );
	}

	/* ── couleurs ── */
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
	function lotColor( lot ) {
		if ( null === lot.hue || lot.losing ) {
			// La case « réessayez » prend le fond de l'habillage, pas une
			// teinte de la marque : c'est ce qui la fait lire comme un vide.
			if ( 'arcade' === C.skin ) { return '#4C2A9B'; }
			if ( 'casino' === C.skin ) { return '#0B3325'; }
			return '#3A2B4D';
		}
		var c = hexToHsl( brandColor );
		return hsl( c[ 0 ] + lot.hue, Math.max( 35, Math.min( 88, c[ 1 ] ) ), Math.max( 38, Math.min( 66, c[ 2 ] ) ) );
	}

	/* ── tirage local (aperçu et repli) ── */
	function eligible() {
		return LOTS.filter( function ( l ) { return null === l.cap || l.awarded < l.cap; } );
	}
	function drawLocal() {
		var pool = eligible(),
			t    = pool.reduce( function ( s, l ) { return s + l.weight; }, 0 );
		if ( t <= 0 ) { return LOTS[ LOTS.length - 1 ]; }
		var r = Math.random() * t;
		for ( var i = 0; i < pool.length; i++ ) {
			r -= pool[ i ].weight;
			if ( r <= 0 ) { return pool[ i ]; }
		}
		return pool[ pool.length - 1 ];
	}
	function shareOf( lot ) {
		var t = LOTS.reduce( function ( s, l ) { return s + l.weight; }, 0 );
		return t ? ( lot.weight / t ) * 100 : 0;
	}

	/* ── appel serveur ── */
	function serverPlay( done ) {
		if ( ! C.ajax ) { return done( null ); }
		var body = new URLSearchParams();
		body.append( 'action', 'jmk_play' );
		body.append( 'nonce', C.nonce );
		fetch( C.ajax, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( j ) { done( j && j.success ? j.data : null ); } )
			.catch( function () { done( null ); } );
	}

	/**
	 * Tirage d'une partie, quel que soit le jeu.
	 *
	 * Le serveur fait autorité : c'est lui qui applique les plafonds et
	 * génère le code. Le tirage local ne sert que de repli si l'appel
	 * échoue (hors ligne, nonce expiré sur une page mise en cache).
	 *
	 * @param {Function} done Reçoit ( lot, code ).
	 */
	function play( done ) {
		serverPlay( function ( data ) {
			if ( data && 'undefined' !== typeof data.index && LOTS[ data.index ] ) {
				syncCaps( data.caps );
				drawToken = data.token || '';
				done( LOTS[ data.index ], data.code );
				return;
			}
			var lot  = drawLocal();
			var code = lot.code ? lot.code + '-' + Math.random().toString( 36 ).slice( 2, 6 ).toUpperCase() : '';
			if ( code ) { lot.awarded++; }
			drawToken = '';
			done( lot, code );
		} );
	}

	/* ══════════ ROUE ══════════ */
	var cv = $( '#wheel' ), ctx = cv ? cv.getContext( '2d' ) : null, R = cv ? cv.width / 2 : 0;
	var rotation = 0, spinning = false, hasPlayed = false, drawToken = '';

	var ARCADE = ( 'arcade' === C.skin );
	var CASINO = ( 'casino' === C.skin );
	/* Le bord du plateau appartient à la monture, pas aux quartiers.
	   En arcade, c'est la couronne d'ampoules, dessinée ici même.
	   Ailleurs, c'est la monture en laiton, posée en CSS par-dessus le
	   canevas : son trou tombe à 78 % du rayon, donc les quartiers
	   s'arrêtent là. Deux points de moins et un liseré de segment
	   dépasserait du laiton ; deux de plus et un croissant vide
	   apparaîtrait entre les deux. */
	var RIM  = ARCADE ? 46 : Math.round( R * 0.22 );
	var bulb = 0;

	/**
	 * Couronne d'ampoules, comme sur une vraie roue de fête foraine.
	 *
	 * Dessinée hors de la rotation : les ampoules restent fixes pendant que
	 * la roue tourne, et c'est ce qui rend le mouvement lisible.
	 *
	 * @param {number} n Nombre d'ampoules.
	 */
	function drawRim( n ) {
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

		var ring = ( R - RIM / 2 ) + 1;
		for ( var i = 0; i < n; i++ ) {
			var a  = ( i / n ) * Math.PI * 2 - Math.PI / 2,
				x  = Math.cos( a ) * ring,
				y  = Math.sin( a ) * ring,
				on = ( ( i + bulb ) % 2 === 0 );
			ctx.beginPath();
			ctx.arc( x, y, RIM * 0.20, 0, Math.PI * 2 );
			ctx.fillStyle = on ? '#FFF6D8' : '#B98A2E';
			ctx.fill();
			if ( on ) {
				var glow = ctx.createRadialGradient( x, y, 0, x, y, RIM * 0.52 );
				glow.addColorStop( 0, 'rgba(255,246,216,.75)' );
				glow.addColorStop( 1, 'rgba(255,246,216,0)' );
				ctx.beginPath();
				ctx.arc( x, y, RIM * 0.52, 0, Math.PI * 2 );
				ctx.fillStyle = glow;
				ctx.fill();
			}
		}
	}

	function drawWheel() {
		if ( ! ctx ) { return; }
		var n = LOTS.length, seg = ( Math.PI * 2 ) / n, rad = R - RIM;
		ctx.clearRect( 0, 0, cv.width, cv.height );
		ctx.save();
		ctx.translate( R, R );

		if ( ARCADE ) {
			drawRim( Math.max( 16, n * 4 ) );
		}

		ctx.save();
		ctx.rotate( rotation );
		LOTS.forEach( function ( lot, i ) {
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

			if ( ARCADE && ! out ) {
				// Un voile clair vers l'extérieur donne le relief bombé.
				var gl = ctx.createRadialGradient( 0, 0, rad * 0.18, 0, 0, rad );
				gl.addColorStop( 0, 'rgba(255,255,255,.26)' );
				gl.addColorStop( 0.55, 'rgba(255,255,255,.05)' );
				gl.addColorStop( 1, 'rgba(0,0,0,.24)' );
				ctx.beginPath();
				ctx.moveTo( 0, 0 );
				ctx.arc( 0, 0, rad, a0, a0 + seg );
				ctx.closePath();
				ctx.fillStyle = gl;
				ctx.fill();
			}

			if ( CASINO && ! out ) {
				// Un fondu vers le centre, comme le vernis d'une roue de
				// table. Plus discret que le bombé de la fête foraine.
				var cg = ctx.createRadialGradient( 0, 0, rad * 0.2, 0, 0, rad );
				cg.addColorStop( 0, 'rgba(0,0,0,.26)' );
				cg.addColorStop( 0.6, 'rgba(255,255,255,.06)' );
				cg.addColorStop( 1, 'rgba(255,255,255,.16)' );
				ctx.beginPath();
				ctx.moveTo( 0, 0 );
				ctx.arc( 0, 0, rad, a0, a0 + seg );
				ctx.closePath();
				ctx.fillStyle = cg;
				ctx.fill();
			}

			ctx.lineWidth   = ARCADE ? 5 : ( CASINO ? 2 : 3 );
			ctx.strokeStyle = ARCADE ? '#7A4E0B' : ( CASINO ? '#D9A441' : '#150C1D' );
			ctx.stroke();

			ctx.save();
			ctx.rotate( a0 + seg / 2 );
			ctx.textAlign    = 'right';
			ctx.textBaseline = 'middle';
			ctx.font         = ( ARCADE ? '800 ' : '700 ' ) + '40px "Bricolage Grotesque", sans-serif';
			var ink = ( null === lot.hue || lot.losing )
				? ( ARCADE ? '#D9C9FF' : '#9C90AC' )
				: '#150C1D';
			var w   = String( lot.label ).split( ' ' );
			var lightOutline = ARCADE && ! lot.losing && null !== lot.hue;
			var put = function ( txt, dy ) {
				if ( lightOutline ) {
					ctx.lineWidth   = 5;
					ctx.strokeStyle = 'rgba(255,255,255,.55)';
					ctx.lineJoin    = 'round';
					ctx.strokeText( txt, rad - 44, dy );
				}
				ctx.fillStyle = ink;
				ctx.fillText( txt, rad - 44, dy );
			};
			if ( w.length > 1 && lot.label.length > 10 ) {
				put( w[ 0 ], -24 );
				put( w.slice( 1 ).join( ' ' ), 24 );
			} else {
				put( lot.label, 0 );
			}
			if ( out ) {
				ctx.font      = '700 21px "JetBrains Mono", monospace';
				ctx.fillStyle = '#F2506B';
				ctx.fillText( T.exhausted, rad - 44, 54 );
			}
			ctx.restore();
		} );
		ctx.restore();
		ctx.restore();
	}

	function animateTo( idx, after ) {
		var n = LOTS.length, seg = ( Math.PI * 2 ) / n;
		var target  = ( Math.PI * 2 ) * 6 - ( idx + 0.5 ) * seg + ( Math.random() - 0.5 ) * seg * 0.55;
		var from    = rotation % ( Math.PI * 2 );
		var dur     = reduced ? 200 : 4700;
		var t0      = performance.now();
		var lastSeg = -1;
		( function frame( now ) {
			var p = Math.min( ( now - t0 ) / dur, 1 ),
				e = 1 - Math.pow( 1 - p, 4 );
			rotation = from + ( target - from ) * e;
			drawWheel();
			var cur = Math.floor( ( ( ( -rotation ) % ( Math.PI * 2 ) + Math.PI * 2 ) % ( Math.PI * 2 ) ) / seg );
			if ( cur !== lastSeg && p < 0.985 && ! reduced ) {
				lastSeg = cur;
				bulb ^= 1;
				var nd = $( '#needle' );
				if ( nd ) {
					nd.classList.remove( 'tick' );
					void nd.offsetWidth;
					nd.classList.add( 'tick' );
				}
			}
			if ( p < 1 ) { requestAnimationFrame( frame ); } else { after(); }
		} )( t0 );
	}

	/* L'inclinaison de la roue est pilotée par deux classes, pas par une
	   valeur calculée ici : la mise en forme reste dans la feuille de style,
	   et « mouvement réduit » la neutralise d'un seul endroit. */
	function leanWheel( on ) {
		var holder = $( '.wheel-holder' );
		if ( ! holder || reduced ) { return; }

		holder.classList.toggle( 'spinning', !! on );
		if ( on ) {
			holder.classList.remove( 'settling' );
			return;
		}
		// Relancer l'animation demande de la retirer, de forcer un calcul de
		// style, puis de la remettre — sinon le navigateur ne voit aucun
		// changement et ne rejoue rien.
		void holder.offsetWidth;
		holder.classList.add( 'settling' );
		setTimeout( function () { holder.classList.remove( 'settling' ); }, 800 );
	}

	function spin() {
		if ( spinning || hasPlayed ) { return; }
		spinning = true;
		$( '#spinBtn' ).disabled = true;
		var glow = $( '#glow' );
		if ( glow ) { glow.classList.add( 'on' ); }
		leanWheel( true );
		$( '#wheelResult' ).classList.remove( 'show' );

		play( function ( lot, code ) {
			animateTo( Math.max( 0, LOTS.indexOf( lot ) ), function () { finish( lot, code ); } );
		} );
	}

	function syncCaps( caps ) {
		if ( ! caps ) { return; }
		LOTS.forEach( function ( l, i ) {
			if ( 'undefined' !== typeof caps[ i ] ) { l.awarded = parseInt( caps[ i ], 10 ) || 0; }
		} );
	}

	function finish( lot, code ) {
		spinning  = false;
		hasPlayed = !! C.onePlay;
		var glow = $( '#glow' );
		if ( glow ) { glow.classList.remove( 'on' ); }
		leanWheel( false );

		var b = $( '#wheelResult' );
		b.innerHTML = code
			? T.win + ' ' + esc( lot.label ) + '<span class="code">' + esc( code ) + '</span>'
			: T.lose + '<span class="lot-sub jmk-block">' + T.loseSub + '</span>';
		b.style.color = code ? 'var(--accent)' : 'var(--muted)';
		b.classList.add( 'show' );

		var rp = $( '#resetPlay' );
		if ( rp && ! C.onePlay ) {
			$( '#spinBtn' ).disabled = false;
		} else if ( rp ) {
			rp.hidden = false;
		}
		renderLots();
		drawWheel();
		if ( code && ! reduced ) { burst(); }
	}

	if ( $( '#spinBtn' ) ) { $( '#spinBtn' ).addEventListener( 'click', spin ); }
	if ( $( '#resetPlay' ) ) {
		$( '#resetPlay' ).addEventListener( 'click', function () {
			hasPlayed = false;
			$( '#spinBtn' ).disabled = false;
			$( '#wheelResult' ).classList.remove( 'show' );
			this.hidden = true;
		} );
	}

	/* ── confettis ── */
	function burst() {
		var cf = $( '#jmk-confetti' );
		if ( ! cf ) { return; }
		var x = cf.getContext( '2d' );
		cf.style.display = 'block';
		cf.width  = window.innerWidth;
		cf.height = window.innerHeight;
		var P = [], colors = [ brandColor, '#F2506B', '#48C9A9', '#F5EFE6' ];
		for ( var i = 0; i < 130; i++ ) {
			P.push( {
				x: window.innerWidth * 0.5 + ( Math.random() - 0.5 ) * 260,
				y: window.innerHeight * 0.42,
				vx: ( Math.random() - 0.5 ) * 11,
				vy: Math.random() * -13 - 4,
				r: Math.random() * 5 + 2,
				c: colors[ i % colors.length ],
				a: 1,
				rot: Math.random() * 6
			} );
		}
		( function loop() {
			x.clearRect( 0, 0, cf.width, cf.height );
			var alive = false;
			P.forEach( function ( p ) {
				p.vy += 0.34; p.x += p.vx; p.y += p.vy; p.a -= 0.008; p.rot += 0.12;
				if ( p.a > 0 ) {
					alive = true;
					x.save();
					x.globalAlpha = Math.max( 0, p.a );
					x.translate( p.x, p.y );
					x.rotate( p.rot );
					x.fillStyle = p.c;
					x.fillRect( -p.r, -p.r * 0.5, p.r * 2, p.r );
					x.restore();
				}
			} );
			if ( alive ) { requestAnimationFrame( loop ); } else { cf.style.display = 'none'; }
		} )();
	}

	/* ── panneau des lots ── */
	function renderLots() {
		var host = $( '#lotList' );
		if ( ! host ) { return; }
		host.innerHTML = '';
		LOTS.forEach( function ( lot ) {
			var out = null !== lot.cap && lot.awarded >= lot.cap;
			var row = document.createElement( 'div' );
			row.className = 'lot-row';
			row.innerHTML =
				'<span class="swatch" style="background:' + lotColor( lot ) + '"></span>' +
				'<span><span class="lot-name">' + esc( lot.label ) + '</span>' +
				( null !== lot.cap ? '<span class="cap-flag' + ( out ? ' out' : '' ) + '">' + ( out ? 'stock épuisé' : 'plafond ' + lot.cap ) + '</span>' : '' ) +
				'</span><span class="lot-ctl">' +
				'<input type="range" min="0" max="60" value="' + lot.weight + '" aria-label="' + esc( lot.label ) + '" data-id="' + lot.id + '">' +
				'<span class="pct" data-pct="' + lot.id + '">' + shareOf( lot ).toFixed( 1 ) + '%</span></span>';
			host.appendChild( row );
		} );
		host.querySelectorAll( 'input[type=range]' ).forEach( function ( sl ) {
			sl.addEventListener( 'input', function ( e ) {
				LOTS.forEach( function ( l ) {
					if ( l.id === e.target.dataset.id ) { l.weight = Number( e.target.value ); }
				} );
				LOTS.forEach( function ( l ) {
					var el = document.querySelector( '[data-pct="' + l.id + '"]' );
					if ( el ) { el.textContent = shareOf( l ).toFixed( 1 ) + '%'; }
				} );
				drawWheel();
			} );
		} );
		renderCaps();
	}

	function renderCaps() {
		var panel = $( '#capPanel' ), host = $( '#capRows' );
		if ( ! panel || ! host ) { return; }
		var capped = LOTS.filter( function ( l ) { return null !== l.cap; } );
		panel.hidden = 0 === capped.length;
		host.innerHTML = '';
		capped.forEach( function ( lot ) {
			var row = document.createElement( 'div' );
			row.className = 'simrow';
			row.style.gridTemplateColumns = '128px 1fr auto';
			row.innerHTML =
				'<span>' + esc( lot.label ) + '</span>' +
				'<div class="bartrack"><div class="barfill" style="background:var(--mint);width:' +
					Math.min( 100, ( lot.awarded / lot.cap ) * 100 ) + '%"></div></div>' +
				'<span class="mono simnum">' + lot.awarded + ' / ' + lot.cap + '</span>';
			host.appendChild( row );
		} );
	}

	/* ── simulation ── */
	if ( $( '#simBtn' ) ) {
		$( '#simBtn' ).addEventListener( 'click', function () {
			var N = 1000,
				sb = LOTS.map( function ( l ) { return Object.assign( {}, l, { awarded: 0 } ); } ),
				c = {};
			sb.forEach( function ( l ) { c[ l.id ] = 0; } );
			for ( var i = 0; i < N; i++ ) {
				var pool = sb.filter( function ( l ) { return null === l.cap || l.awarded < l.cap; } ),
					t    = pool.reduce( function ( s, l ) { return s + l.weight; }, 0 ),
					r    = Math.random() * t, w = pool[ pool.length - 1 ];
				for ( var j = 0; j < pool.length; j++ ) {
					r -= pool[ j ].weight;
					if ( r <= 0 ) { w = pool[ j ]; break; }
				}
				if ( ! w ) { continue; }
				c[ w.id ]++;
				if ( null !== w.cap ) { w.awarded++; }
			}
			var host = $( '#simResults' );
			host.innerHTML = '';
			LOTS.forEach( function ( lot ) {
				var ob  = ( c[ lot.id ] / N ) * 100,
					cfg = shareOf( lot ),
					row = document.createElement( 'div' );
				row.className = 'simrow';
				row.innerHTML =
					'<span>' + esc( lot.label ) + '</span>' +
					'<div class="bartrack"><div class="barfill" style="background:' + lotColor( lot ) + '"></div>' +
					'<span class="target-tick" style="left:' + Math.min( cfg, 99 ) + '%"></span></div>' +
					'<span class="simnum">' + ob.toFixed( 1 ) + '%</span>';
				host.appendChild( row );
				requestAnimationFrame( function () {
					row.querySelector( '.barfill' ).style.width = Math.min( ob, 100 ) + '%';
				} );
			} );
		} );
	}

	/* ══════════ CARTE À GRATTER ══════════ */
	var sc = $( '#scratchCv' ), sx = sc ? sc.getContext( '2d', { willReadFrequently: true } ) : null;
	var scratching = false, scratchDone = false, scratchLot = null, mTick = 0;
	var scratchCode = '', scratchPending = false;

	/* Pellicule argentée. Tant qu'elle n'est pas chargée, le dégradé
	   tient lieu de repli : la carte est jouable immédiatement. */
	var foil = null;
	if ( C.foil && sc ) {
		foil = new Image();
		foil.onload = function () {
			if ( ! scratchDone && 0 === mTick ) { sizeScratch(); }
		};
		foil.src = C.foil;
	}
	function foilReady() {
		return foil && foil.complete && foil.naturalWidth > 0;
	}

	function sizeScratch() {
		if ( ! sx ) { return; }
		var r = sc.getBoundingClientRect(), d = window.devicePixelRatio || 1;
		sc.width  = Math.max( 1, r.width * d );
		sc.height = Math.max( 1, r.height * d );
		sx.setTransform( d, 0, 0, d, 0, 0 );
		sx.globalCompositeOperation = 'source-over';
		if ( foilReady() ) {
			// Couvrir la zone sans déformer la texture.
			var ratio = Math.max( r.width / foil.naturalWidth, r.height / foil.naturalHeight ),
				dw    = foil.naturalWidth * ratio,
				dh    = foil.naturalHeight * ratio;
			sx.drawImage( foil, ( r.width - dw ) / 2, ( r.height - dh ) / 2, dw, dh );
		} else {
			var g = sx.createLinearGradient( 0, 0, r.width, r.height );
			g.addColorStop( 0, '#8E95A2' ); g.addColorStop( 0.4, '#C3C9D2' );
			g.addColorStop( 0.6, '#7E8593' ); g.addColorStop( 1, '#A9B0BB' );
			sx.fillStyle = g;
			sx.fillRect( 0, 0, r.width, r.height );
		}
		sx.fillStyle = 'rgba(21,12,29,.34)';
		sx.font      = '600 13px "JetBrains Mono", monospace';
		sx.textAlign = 'center';
		sx.fillText( T.scratch, r.width / 2, r.height / 2 );
	}
	function newCard() {
		if ( ! sx ) { return; }
		scratching = false; scratchDone = false; mTick = 0;
		scratchLot = null; scratchCode = '';
		$( '#scratchPrize' ).textContent = '—';
		$( '#scratchPrize' ).style.color = 'var(--muted)';
		$( '#scratchPct' ).textContent   = '0 %';
		sizeScratch();
	}

	/* Le lot n'est tiré qu'au premier grattage : afficher la page ne
	   consomme aucun plafond. Il reste caché sous la pellicule. */
	function ensureScratchLot() {
		if ( scratchLot || scratchPending ) { return; }
		scratchPending = true;
		play( function ( lot, code ) {
			scratchPending = false;
			scratchLot     = lot;
			scratchCode    = code;
			$( '#scratchPrize' ).textContent = code ? lot.label : T.lose;
			$( '#scratchPrize' ).style.color = code ? 'var(--accent)' : 'var(--muted)';
			renderLots();
			drawWheel();
		} );
	}

	function scratchAt( e ) {
		if ( scratchDone || ! sx ) { return; }
		ensureScratchLot();
		var r = sc.getBoundingClientRect(), pt = e.touches ? e.touches[ 0 ] : e;
		sx.globalCompositeOperation = 'destination-out';
		sx.beginPath();
		sx.arc( pt.clientX - r.left, pt.clientY - r.top, 20, 0, Math.PI * 2 );
		sx.fill();
		if ( ++mTick % 6 ) { return; }
		var d = sx.getImageData( 0, 0, sc.width, sc.height ).data, cl = 0, t = 0;
		for ( var i = 3; i < d.length; i += 160 ) { t++; if ( d[ i ] < 40 ) { cl++; } }
		var p = t ? ( cl / t ) * 100 : 0;
		$( '#scratchPct' ).textContent = Math.round( p ) + ' %';
		if ( p > 46 && scratchLot ) {
			scratchDone = true;
			sx.clearRect( 0, 0, sc.width, sc.height );
			$( '#scratchPct' ).textContent = T.revealed;
			if ( scratchCode ) {
				if ( ! reduced ) { burst(); }
			}
		}
	}
	if ( sc ) {
		sc.addEventListener( 'mousedown', function ( e ) { scratching = true; scratchAt( e ); } );
		sc.addEventListener( 'mousemove', function ( e ) { if ( scratching ) { scratchAt( e ); } } );
		window.addEventListener( 'mouseup', function () { scratching = false; } );
		sc.addEventListener( 'touchstart', function ( e ) { e.preventDefault(); scratching = true; scratchAt( e ); }, { passive: false } );
		sc.addEventListener( 'touchmove', function ( e ) { e.preventDefault(); if ( scratching ) { scratchAt( e ); } }, { passive: false } );
		sc.addEventListener( 'touchend', function () { scratching = false; } );
		window.addEventListener( 'resize', function () { if ( ! scratchDone ) { sizeScratch(); } } );
	}
	if ( $( '#scratchReset' ) ) { $( '#scratchReset' ).addEventListener( 'click', newCard ); }

	/* ══════════ TAP TO WIN ══════════ */
	var tapDone = false;

	/* Le résultat s'écrit sous le couvercle, jamais dans le bouton : y
	   écrire directement effaçait couvercle et cadeau, et la boîte
	   s'ouvrait sur rien. */
	function tapMark( box, txt ) {
		var slot = box.querySelector( '.box-prize' );
		if ( slot ) { slot.textContent = txt; } else { box.textContent = txt; }
	}

	$$( '.box' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			if ( tapDone ) { return; }
			tapDone = true;
			$( '#tapMsg' ).textContent = '';
			play( function ( w, code ) {
				$$( '.box' ).forEach( function ( o ) {
					o.classList.add( 'done' );
					if ( o === b ) {
						o.classList.add( code ? 'win' : 'lose' );
						tapMark( o, code ? '★' : '—' );
					} else {
						o.classList.add( 'lose' );
						tapMark( o, '—' );
					}
				} );
				$( '#tapMsg' ).textContent = code ? T.win + ' ' + w.label : T.lose;
				if ( code ) {
					renderLots();
					drawWheel();
					if ( ! reduced ) { burst(); }
				}
			} );
		} );
	} );
	if ( $( '#tapReset' ) ) {
		$( '#tapReset' ).addEventListener( 'click', function () {
			tapDone = false;
			// Remettre la classe suffit : le couvercle est resté en place,
			// il se rabat tout seul puisque « done » disparaît.
			$$( '.box' ).forEach( function ( b ) { b.className = 'box'; tapMark( b, '' ); } );
			$( '#tapMsg' ).textContent = '';
		} );
	}

	/* ══════════ MACHINE À SOUS ══════════ */
	/* Trois rouleaux identiques = gagné. Le serveur ayant déjà tranché, les
	   rouleaux ne font qu'illustrer sa décision : ils s'arrêtent sur le lot
	   tiré si c'est gagné, sur une combinaison dépareillée sinon. */
	var CELL = 58, CYCLES = 9, slotBusy = false;

	function slotStrips() {
		return $$( '#reels .strip' );
	}
	function buildReels() {
		var strips = slotStrips();
		if ( ! strips.length || ! LOTS.length ) { return; }
		strips.forEach( function ( strip ) {
			var html = '';
			for ( var c = 0; c < CYCLES; c++ ) {
				LOTS.forEach( function ( lot ) {
					var face = String( lot.label );
					if ( face.length > 18 ) { face = face.slice( 0, 17 ) + '…'; }
					html += '<span class="cell" style="background:' + lotColor( lot ) + ';color:' +
						( ( null === lot.hue || lot.losing ) ? '#9C90AC' : '#150C1D' ) + '">' +
						esc( face ) + '</span>';
				} );
			}
			strip.innerHTML = html;
			strip.style.transition = 'none';
			strip.style.transform  = 'translateY(0)';
		} );
	}

	/**
	 * Fait glisser un rouleau jusqu'au lot voulu.
	 *
	 * @param {Element} strip Bande du rouleau.
	 * @param {number}  idx   Index du lot.
	 * @param {number}  turns Nombre de tours avant l'arrêt.
	 * @param {number}  ms    Durée.
	 */
	function stopReel( strip, idx, turns, ms ) {
		var y = -( ( turns * LOTS.length ) + idx ) * CELL;
		strip.style.transition = 'none';
		strip.style.transform  = 'translateY(0)';
		void strip.offsetWidth;
		strip.style.transition = 'transform ' + ms + 'ms cubic-bezier(.16,.84,.28,1)';
		strip.style.transform  = 'translateY(' + y + 'px)';
	}

	/* Combinaison dépareillée : on affiche trois lots différents. */
	function missCombo( drawn ) {
		var others = LOTS.map( function ( l, i ) { return i; } )
			.filter( function ( i ) { return i !== drawn; } );
		if ( others.length < 2 ) { return [ drawn, drawn, others[ 0 ] || drawn ]; }
		var a = others[ Math.floor( Math.random() * others.length ) ];
		var b = others.filter( function ( i ) { return i !== a; } );
		return [ drawn, a, b[ Math.floor( Math.random() * b.length ) ] ];
	}

	if ( $( '#slotBtn' ) ) {
		buildReels();
		$( '#slotBtn' ).addEventListener( 'click', function () {
			if ( slotBusy ) { return; }
			slotBusy = true;
			var btn = this;
			btn.disabled = true;
			$( '#slotMsg' ).textContent = '';

			play( function ( lot, code ) {
				var idx    = Math.max( 0, LOTS.indexOf( lot ) );
				var faces  = code ? [ idx, idx, idx ] : missCombo( idx );
				var strips = slotStrips();
				var base   = reduced ? 120 : 900;

				strips.forEach( function ( strip, i ) {
					stopReel( strip, faces[ i ], 3 + i * 2, base + i * ( reduced ? 40 : 420 ) );
				} );

				setTimeout( function () {
					slotBusy     = false;
					btn.disabled = false;
					$( '#slotMsg' ).textContent = code ? T.win + ' ' + lot.label : T.lose;
					if ( code ) {
						renderLots();
						drawWheel();
						if ( ! reduced ) { burst(); }
					}
				}, base + 2 * ( reduced ? 40 : 420 ) + ( reduced ? 20 : 160 ) );
			} );
		} );
	}

	/* ══════════ PLUIE DE LOTS ══════════ */
	/* La case d'arrivée est connue avant le lancer : on construit un chemin
	   qui y mène, puis on l'anime. Le hasard est déjà passé, côté serveur. */
	var pk        = $( '#plinkoCv' ),
		px        = pk ? pk.getContext( '2d' ) : null,
		PK_ROWS   = 9,
		pkBusy    = false,
		pkBall    = null,
		pkLanded  = -1;

	/* ctx.roundRect n'existe pas partout : on trace le chemin à la main. */
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

	function pkSize() {
		if ( ! px ) { return null; }
		var r = pk.getBoundingClientRect(),
			d = window.devicePixelRatio || 1,
			w = Math.max( 200, r.width ),
			h = Math.max( 180, r.height );
		if ( pk.width !== Math.round( w * d ) || pk.height !== Math.round( h * d ) ) {
			pk.width  = Math.round( w * d );
			pk.height = Math.round( h * d );
		}
		px.setTransform( d, 0, 0, d, 0, 0 );
		return { w: w, h: h };
	}

	function pkDraw() {
		var s = pkSize();
		if ( ! s || ! LOTS.length ) { return; }
		var slotH = 30,
			top   = 14,
			usable = s.h - slotH - top,
			gapY  = usable / PK_ROWS,
			gapX  = s.w / ( PK_ROWS + 2 );

		px.clearRect( 0, 0, s.w, s.h );

		/* Le fond du plateau : sombre en haut, éclairé au centre. Sans lui,
		   les clous flottent sur du vide et la planche reste un schéma. */
		var fond = px.createLinearGradient( 0, 0, 0, s.h );
		fond.addColorStop( 0, 'rgba(0,0,0,.55)' );
		fond.addColorStop( 0.45, 'rgba(255,255,255,.04)' );
		fond.addColorStop( 1, 'rgba(0,0,0,.5)' );
		px.fillStyle = fond;
		px.fillRect( 0, 0, s.w, s.h );

		/* Les clous. Une ombre portée en bas à droite et un reflet en haut
		   à gauche suffisent à les faire sortir de la planche — c'est la
		   même lumière que partout ailleurs dans le thème. */
		var PEG = 3.2;
		for ( var row = 0; row < PK_ROWS; row++ ) {
			var count = row + 2,
				y     = top + ( row + 0.5 ) * gapY;
			for ( var i = 0; i < count; i++ ) {
				var x = s.w / 2 + ( i - ( count - 1 ) / 2 ) * gapX;

				px.beginPath();
				px.arc( x + 0.9, y + 1.2, PEG, 0, Math.PI * 2 );
				px.fillStyle = 'rgba(0,0,0,.55)';
				px.fill();

				var bille = px.createRadialGradient(
					x - PEG * 0.4, y - PEG * 0.45, PEG * 0.12,
					x, y, PEG
				);
				bille.addColorStop( 0, '#EFE6FF' );
				bille.addColorStop( 0.45, '#8E79AE' );
				bille.addColorStop( 1, '#3B2F4C' );
				px.beginPath();
				px.arc( x, y, PEG, 0, Math.PI * 2 );
				px.fillStyle = bille;
				px.fill();
			}
		}

		// Cases du bas, une par lot.
		var n = LOTS.length, cw = s.w / n;
		LOTS.forEach( function ( lot, i ) {
			var out = null !== lot.cap && lot.awarded >= lot.cap;
			px.globalAlpha = out ? 0.3 : 1;
			px.fillStyle   = lotColor( lot );
			roundPath( px, i * cw + 2, s.h - slotH, cw - 4, slotH - 2, 6 );
			px.fill();

			/* La case est une poche, pas un rectangle peint : le haut reçoit
			   la lumière, le fond garde l'ombre. */
			var creux = px.createLinearGradient( 0, s.h - slotH, 0, s.h );
			creux.addColorStop( 0, 'rgba(0,0,0,.42)' );
			creux.addColorStop( 0.34, 'rgba(255,255,255,.16)' );
			creux.addColorStop( 1, 'rgba(0,0,0,.34)' );
			px.fillStyle = creux;
			roundPath( px, i * cw + 2, s.h - slotH, cw - 4, slotH - 2, 6 );
			px.fill();
			px.globalAlpha = 1;

			px.fillStyle    = ( null === lot.hue || lot.losing ) ? '#9C90AC' : '#150C1D';
			px.font         = '600 10px "JetBrains Mono", monospace';
			px.textAlign    = 'center';
			px.textBaseline = 'middle';
			var label = String( lot.label );
			if ( label.length > 9 ) { label = label.slice( 0, 8 ) + '…'; }
			px.fillText( label, i * cw + cw / 2, s.h - slotH / 2 - 1 );

			if ( pkLanded === i ) {
				px.strokeStyle = '#F5EFE6';
				px.lineWidth   = 2;
				roundPath( px, i * cw + 2, s.h - slotH, cw - 4, slotH - 2, 6 );
				px.stroke();
			}
		} );

		// Bille.
		if ( pkBall ) {
			px.fillStyle = '#F5EFE6';
			px.beginPath();
			px.arc( pkBall.x, pkBall.y, 6, 0, Math.PI * 2 );
			px.fill();
		}
	}

	/* Suite de déviations gauche/droite menant à la colonne voulue. */
	function pkPath( targetCol ) {
		var moves = [], right = Math.max( 0, Math.min( PK_ROWS, targetCol ) );
		for ( var i = 0; i < PK_ROWS; i++ ) { moves.push( i < right ? 1 : 0 ); }
		for ( var j = moves.length - 1; j > 0; j-- ) {
			var k = Math.floor( Math.random() * ( j + 1 ) );
			var t = moves[ j ]; moves[ j ] = moves[ k ]; moves[ k ] = t;
		}
		return moves;
	}

	function pkDrop( idx, after ) {
		var s = pkSize();
		if ( ! s ) { return after(); }
		var n      = LOTS.length,
			slotH  = 30,
			top    = 14,
			usable = s.h - slotH - top,
			gapY   = usable / PK_ROWS,
			gapX   = s.w / ( PK_ROWS + 2 ),
			// Colonne visée, ramenée à l'échelle du nombre de rangées.
			col    = Math.round( ( idx + 0.5 ) * PK_ROWS / n ),
			moves  = pkPath( col ),
			targetX = ( idx + 0.5 ) * ( s.w / n );

		var pts = [ { x: s.w / 2, y: top - 6 } ], cx = s.w / 2;
		moves.forEach( function ( m, r ) {
			cx += ( m ? 1 : -1 ) * gapX / 2;
			pts.push( { x: cx, y: top + ( r + 0.5 ) * gapY } );
		} );
		pts.push( { x: targetX, y: s.h - slotH - 6 } );

		if ( reduced ) {
			pkBall = pts[ pts.length - 1 ];
			pkDraw();
			return after();
		}

		var seg = 0, t0 = performance.now(), per = 170;
		( function step( now ) {
			var p = Math.min( ( now - t0 ) / per, 1 ),
				a = pts[ seg ],
				b = pts[ seg + 1 ];
			// Léger rebond vertical entre deux clous.
			pkBall = {
				x: a.x + ( b.x - a.x ) * p,
				y: a.y + ( b.y - a.y ) * p - Math.sin( p * Math.PI ) * 7
			};
			pkDraw();
			if ( p < 1 ) {
				requestAnimationFrame( step );
			} else if ( seg < pts.length - 2 ) {
				seg++;
				t0 = now;
				requestAnimationFrame( step );
			} else {
				after();
			}
		} )( t0 );
	}

	if ( pk && $( '#plinkoBtn' ) ) {
		pkDraw();
		window.addEventListener( 'resize', function () { if ( ! pkBusy ) { pkDraw(); } } );
		$( '#plinkoBtn' ).addEventListener( 'click', function () {
			if ( pkBusy ) { return; }
			pkBusy = true;
			var btn = this;
			btn.disabled = true;
			pkLanded = -1;
			$( '#plinkoMsg' ).textContent = '';

			play( function ( lot, code ) {
				var idx = Math.max( 0, LOTS.indexOf( lot ) );
				pkDrop( idx, function () {
					pkLanded = idx;
					pkBusy   = false;
					btn.disabled = false;
					pkDraw();
					$( '#plinkoMsg' ).textContent = code ? T.win + ' ' + lot.label : T.lose;
					if ( code ) {
						renderLots();
						drawWheel();
						if ( ! reduced ) { burst(); }
					}
				} );
			} );
		} );
	}

	/* ══════════ QUIZ ══════════ */
	var qi = 0, qOk = 0, qRun = 0;
	function renderQuiz() {
		var host = $( '#quizBox' );
		if ( ! host || ! QUIZ.length ) { return; }
		if ( qi >= QUIZ.length ) {
			host.innerHTML =
				'<p class="small jmk-mb-10">' + T.score.replace( '%1$s', qOk ).replace( '%2$s', QUIZ.length ) + '</p>' +
				'<p class="quiz-final"></p>';
			var run = qRun;
			play( function ( w, code ) {
				// La partie a été relancée entre-temps : ne rien réécrire.
				if ( run !== qRun ) { return; }
				var out = host.querySelector( '.quiz-final' );
				if ( ! out ) { return; }
				out.style.color   = code ? 'var(--accent)' : 'var(--muted)';
				out.textContent   = code ? T.win + ' ' + w.label : T.lose;
				if ( code ) {
					renderLots();
					drawWheel();
					if ( ! reduced ) { burst(); }
				}
			} );
			return;
		}
		var q = QUIZ[ qi ];
		var cnt = $( '#quizCount' );
		if ( cnt ) { cnt.textContent = ( qi + 1 ) + ' / ' + QUIZ.length; }
		host.innerHTML = '<p class="jmk-q">' + esc( q.q ) + '</p>' +
			q.o.map( function ( o, i ) { return '<button class="qz-opt" data-i="' + i + '">' + esc( o ) + '</button>'; } ).join( '' );
		host.querySelectorAll( '.qz-opt' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				var i = Number( b.dataset.i );
				if ( i === q.a ) { qOk++; }
				host.querySelectorAll( '.qz-opt' ).forEach( function ( xb, j ) {
					xb.disabled = true;
					if ( j === q.a ) { xb.classList.add( 'ok' ); } else if ( j === i ) { xb.classList.add( 'no' ); }
				} );
				setTimeout( function () { qi++; renderQuiz(); }, reduced ? 10 : 750 );
			} );
		} );
	}
	if ( $( '#quizReset' ) ) {
		$( '#quizReset' ).addEventListener( 'click', function () { qi = 0; qOk = 0; qRun++; renderQuiz(); } );
	}

	/* ══════════ CAPTURE ══════════ */
	if ( $( '#sendLead' ) ) {
		$( '#sendLead' ).addEventListener( 'click', function () {
			var btn  = this,
				name = $( '#fname' ).value.trim(),
				mail = $( '#femail' ).value.trim().toLowerCase(),
				ok   = $( '#fconsent' ).checked,
				err  = $( '#leadErr' );
			err.style.color = 'var(--rose)';
			err.textContent = '';

			if ( ! name ) { err.textContent = T.errName; $( '#fname' ).focus(); return; }
			if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( mail ) ) { err.textContent = T.errMail; $( '#femail' ).focus(); return; }
			if ( ! ok ) { err.textContent = T.errCons; return; }

			var body = new URLSearchParams();
			body.append( 'action', 'jmk_lead' );
			body.append( 'nonce', C.nonce );
			body.append( 'name', name );
			body.append( 'email', mail );
			body.append( 'token', drawToken );

			btn.disabled = true;
			fetch( C.ajax, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( j ) {
					btn.disabled = false;
					if ( ! j || ! j.success ) {
						err.textContent = ( j && j.data && j.data.message ) ? j.data.message : T.errDupe;
						return;
					}
					addRow( name, mail, j.data.lot, j.data.code );
					drawToken = '';
					$( '#fname' ).value = '';
					$( '#femail' ).value = '';
					$( '#fconsent' ).checked = false;
					err.style.color = 'var(--mint)';
					err.textContent = T.saved;
					setTimeout( function () { err.textContent = ''; err.style.color = 'var(--rose)'; }, 3200 );
				} )
				.catch( function () {
					btn.disabled = false;
					err.textContent = T.errMail;
				} );
		} );
	}

	function addRow( name, mail, lotLabel, code ) {
		var body = $( '#sheetBody' );
		if ( ! body ) { return; }
		if ( body.querySelector( '.sheet-empty' ) ) { body.innerHTML = ''; }
		var tr = document.createElement( 'tr' );
		tr.className = 'fresh';
		var n = new Date();
		tr.innerHTML =
			'<td>' + n.toLocaleDateString() + ' ' + n.toLocaleTimeString( [], { hour: '2-digit', minute: '2-digit' } ) + '</td>' +
			'<td>' + esc( name ) + '</td><td>' + esc( mail ) + '</td>' +
			'<td>' + esc( lotLabel || '—' ) + '</td><td>' + esc( code || '—' ) + '</td>';
		body.insertBefore( tr, body.firstChild );
	}

	/* ══════════ CALCULATEUR ══════════ */
	function animNum( el, to, suffix ) {
		if ( ! el ) { return; }
		var from = Number( el.dataset.v || 0 ), d = reduced ? 0 : 600, t0 = performance.now();
		el.dataset.v = to;
		( function f( now ) {
			var p = d ? Math.min( ( now - t0 ) / d, 1 ) : 1,
				e = 1 - Math.pow( 1 - p, 3 ),
				v = from + ( to - from ) * e;
			el.textContent = Math.round( v ).toLocaleString() + ( suffix || '' );
			if ( p < 1 ) { requestAnimationFrame( f ); }
		} )( t0 );
	}
	function calc() {
		if ( ! $( '#cVisit' ) ) { return; }
		var v  = Number( $( '#cVisit' ).value ) || 0,
			pa = ( Number( $( '#cPart' ).value ) || 0 ) / 100,
			co = ( Number( $( '#cConv' ).value ) || 0 ) / 100,
			ca = Number( $( '#cCart' ).value ) || 0;
		var leads = v * pa, sales = leads * co, rev = sales * ca;
		animNum( $( '#kLeads' ), leads );
		animNum( $( '#kSales' ), sales );
		animNum( $( '#kRev' ), rev, ' ' + C.currency );
		var price = quoteTotal();
		if ( $( '#kPay' ) ) {
			$( '#kPay' ).textContent = rev > 0
				? ( rev >= price ? Math.max( 1, Math.ceil( ( price / rev ) * 30 ) ) + ' j' : '> 1 mois' )
				: '—';
		}
	}
	[ 'cVisit', 'cPart', 'cConv', 'cCart' ].forEach( function ( id ) {
		var el = $( '#' + id );
		if ( el ) { el.addEventListener( 'input', calc ); }
	} );

	/* ══════════ DEVIS ══════════ */
	function quoteTotal() {
		return OPTS.reduce( function ( s, o ) { return o.on ? s + o.price : s; }, 0 );
	}
	function renderOpts() {
		var host = $( '#opts' );
		if ( ! host ) { return; }
		host.innerHTML = '';
		OPTS.forEach( function ( o, i ) {
			var l = document.createElement( 'label' );
			l.className = 'opt';
			l.innerHTML =
				'<input type="checkbox" ' + ( o.on ? 'checked' : '' ) + ' ' + ( o.fixed ? 'disabled' : '' ) + ' data-i="' + i + '">' +
				'<span class="t">' + esc( o.label ) + '</span>' +
				'<span class="p">+' + o.price + ' ' + esc( C.currency ) + '</span>';
			host.appendChild( l );
		} );
		host.querySelectorAll( 'input' ).forEach( function ( c ) {
			c.addEventListener( 'change', function ( e ) {
				OPTS[ Number( e.target.dataset.i ) ].on = e.target.checked;
				updateQuote();
			} );
		} );
	}
	function updateQuote() {
		var total = quoteTotal(),
			days  = OPTS.reduce( function ( s, o ) { return o.on ? s + o.days : s; }, 0 );
		days = Math.max( 1, days );
		if ( $( '#qTotal' ) ) { $( '#qTotal' ).textContent = total + ' ' + C.currency; }
		if ( $( '#qDelay' ) ) {
			$( '#qDelay' ).textContent = T.delay.replace( '%s', days + ' ' + ( days > 1 ? T.days : T.day ) );
		}
		buildEmbed();
		calc();
		renderCtas();
	}
	function briefText() {
		var chosen = OPTS.filter( function ( o ) { return o.on; } )
			.map( function ( o ) { return '• ' + o.label; } ).join( '\n' );
		var langs = { fr: 'Français', en: 'Anglais', ar: 'Arabe (RTL)' };
		return T.briefHi + '\n\n' +
			T.briefBrand + ' : ' + ( brandName || T.noLot ) + '\n' +
			T.briefColor + ' : ' + brandColor.toUpperCase() + '\n' +
			T.briefLang + ' : ' + ( langs[ lang ] || lang ) + '\n\n' +
			T.briefConf + '\n' + chosen + '\n\n' +
			T.briefTotal + ' : ' + quoteTotal() + ' ' + C.currency + '\n\n' +
			T.briefEnd;
	}
	function waUrl() {
		return 'https://wa.me/' + C.whatsapp + '?text=' + encodeURIComponent( briefText() );
	}
	function renderCtas() {
		var direct = ( 'direct' === C.mode ),
			// Sans numéro en mode direct, ou sans lien en mode Fiverr, il n'y
			// a rien vers quoi pointer : on n'affiche pas de bouton mort.
			link   = direct ? ( C.whatsapp ? waUrl() : '' ) : C.fiverr,
			primary = '';

		if ( link ) {
			primary = direct
				? '<a class="btn btn-wa" href="' + link + '" target="_blank" rel="noopener">' + T.waSend + '</a>'
				: '<a class="btn" href="' + link + '" target="_blank" rel="noopener">' + T.order + '</a>';
		}

		var qc = $( '#quoteCta' );
		if ( qc ) {
			qc.innerHTML = primary + '<button class="btn btn-ghost" id="copyBrief">' + T.copy + '</button>';
			var cb = $( '#copyBrief' );
			cb.addEventListener( 'click', function () {
				navigator.clipboard.writeText( briefText() ).then( function () {
					cb.textContent = T.copied;
					setTimeout( function () { cb.textContent = T.copy; }, 1800 );
				} );
			} );
		}
		var fc = $( '#finalCta' );
		if ( fc ) { fc.innerHTML = primary; }

		var tc = $( '#topCta' );
		if ( tc ) {
			tc.hidden = ! link;
			if ( link ) {
				tc.textContent = direct ? 'WhatsApp' : T.order;
				tc.onclick = function () { window.open( link, '_blank', 'noopener' ); };
			}
		}

		var old = document.querySelector( '.wa-float' );
		if ( old ) { old.parentNode.removeChild( old ); }
		if ( direct && C.whatsapp ) {
			var a = document.createElement( 'a' );
			a.className = 'wa-float';
			a.href = link; a.target = '_blank'; a.rel = 'noopener';
			a.innerHTML = '<svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
				'<path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm5.8 14.2c-.2.7-1.4 1.4-2 1.4-.5 0-1.1.2-3.6-.8-3-1.3-4.9-4.4-5.1-4.6-.1-.2-1.2-1.5-1.2-2.9s.7-2 1-2.3c.2-.3.5-.4.7-.4h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.5c-.1.2-.3.3-.1.6.4.7 1 1.5 1.7 2.1.9.8 1.6 1 1.9 1.2.2.1.4.1.5-.1l.8-.9c.2-.2.3-.2.6-.1l2 1c.3.1.5.2.5.4v1.7z"/></svg> ' +
				T.waChat;
			document.body.appendChild( a );
		}
	}

	/**
	 * Le code d'intégration montré sous le devis.
	 *
	 * Ce n'est pas une illustration : l'adresse pointe le moteur autonome
	 * réellement livré avec le thème, et les attributs sont ceux qu'il lit.
	 * Copié tel quel dans une page, le jeu tourne.
	 */
	function buildEmbed() {
		var box = $( '#embedCode' );
		if ( ! box ) { return; }
		var jeu = 'roue';
		OPTS.forEach( function ( o ) {
			if ( o.on && /suppl/i.test( o.label ) ) { jeu = 'grattage'; }
		} );
		var crm  = OPTS.some( function ( o ) { return o.on && /(crm|mailchimp|brevo|sheets)/i.test( o.label ); } );
		var slug = ( brandName || 'ma-marque' ).toLowerCase().replace( /\s+/g, '-' ).replace( /[^a-z0-9-]/g, '' );
		var src  = C.engine || '/wp-content/themes/jeux-marketing/assets/js/jmk-embed.js';

		box.innerHTML =
			'<span class="k">&lt;script</span> <span class="a">src</span>=<span class="s">"' + esc( src ) + '"</span>\n' +
			'  <span class="a">data-jeu</span>=<span class="s">"' + jeu + '"</span>\n' +
			'  <span class="a">data-marque</span>=<span class="s">"' + esc( slug ) + '"</span>\n' +
			'  <span class="a">data-couleur</span>=<span class="s">"' + esc( brandColor.toUpperCase() ) + '"</span>\n' +
			'  <span class="a">data-style</span>=<span class="s">"' + esc( C.skin || 'elegant' ) + '"</span>\n' +
			'  <span class="a">data-langue</span>=<span class="s">"' + esc( lang ) + '"</span>' +
			( crm ? '\n  <span class="a">data-webhook</span>=<span class="s">"https://votre-crm.exemple/lead"</span>' : '' ) +
			'\n  <span class="a">data-rgpd</span>=<span class="s">"/politique-confidentialite"</span><span class="k">&gt;&lt;/script&gt;</span>';
	}
	if ( $( '#copyCode' ) ) {
		$( '#copyCode' ).addEventListener( 'click', function () {
			var b = this;
			navigator.clipboard.writeText( $( '#embedCode' ).innerText ).then( function () {
				b.textContent = T.copied;
				setTimeout( function () { b.textContent = T.copyCode; }, 1800 );
			} );
		} );
	}

	/* ══════════ BANNIÈRES ══════════ */
	/* La section vitrine. Les bannières sont montées à leur taille réelle en
	   pixels — un 728×90 mesure 728×90 — puis mises à l'échelle par transform
	   quand la colonne est plus étroite. Les redimensionner en CSS les
	   déformerait, et une bannière déformée ne prouve plus rien à un client
	   qui vient précisément vérifier qu'on tient les formats. */
	var bannerMounts = [];

	function fitBanner( slot ) {
		var w     = Number( slot.dataset.w ),
			h     = Number( slot.dataset.h ),
			stage = slot.querySelector( '.banner-stage' );
		if ( ! stage || ! w ) { return; }

		var room  = stage.clientWidth,
			scale = Math.min( 1, room / w );

		var mount = stage.querySelector( '.banner-mount' );
		mount.style.transform       = 'scale(' + scale + ')';
		mount.style.transformOrigin = ( 'rtl' === document.dir ) ? 'top right' : 'top left';
		// La scène ne connaît pas la hauteur d'un contenu mis à l'échelle :
		// transform ne change pas la place réservée dans le flux.
		stage.style.height = Math.ceil( h * scale ) + 'px';
	}

	/* Un mur sans moteur ne produit rien et ne dit rien : c'est ainsi que la
	   section est partie en ligne avec quatre cadres vides et aucune erreur en
	   console. On le signale plutôt que de renoncer en silence. */
	function bannerEngineReady( wall ) {
		if ( ! wall ) { return false; }
		if ( 'undefined' === typeof window.JMKBanner ) {
			if ( window.console && console.warn ) {
				console.warn( 'Jeux Marketing : jmk-banner.js n’est pas chargé, ou il l’est après app.js. ' +
					'Les emplacements de bannières resteront vides.' );
			}
			return false;
		}
		return true;
	}

	function buildBanners() {
		var wall = $( '#bannerWall' );
		if ( ! bannerEngineReady( wall ) || ! C.banners ) { return; }

		bannerMounts.forEach( function ( b ) { b.destroy(); } );
		bannerMounts = [];

		$$( '#bannerWall .banner-slot' ).forEach( function ( slot ) {
			var mount = slot.querySelector( '.banner-mount' );
			mount.innerHTML = '';

			var cfg = Object.assign( {}, C.banners, {
				w:      Number( slot.dataset.w ),
				h:      Number( slot.dataset.h ),
				accent: brandColor
			} );

			var made = window.JMKBanner.mount( mount, cfg );
			if ( made ) { bannerMounts.push( made ); }
			fitBanner( slot );
		} );
	}

	/* ── portfolio de bannières ──
	   Quatre campagnes, un jeu de formats. Changer d'onglet démonte les
	   bannières en place et les remonte : elles ne partagent ni habillage ni
	   palette, il n'y a rien à recycler. Et démonter compte — chaque bannière
	   porte un minuteur, en laisser quatre séries tourner en arrière-plan
	   ferait battre seize horloges pour rien. */
	var bworkMounts = [];
	var bworkAt     = 0;

	function buildBwork() {
		var wall = $( '#bworkWall' );
		if ( ! bannerEngineReady( wall ) || ! C.bwork || ! C.bwork.length ) { return; }

		bworkMounts.forEach( function ( b ) { b.destroy(); } );
		bworkMounts = [];

		var camp = C.bwork[ bworkAt ] || C.bwork[ 0 ];

		$$( '#bworkWall .banner-slot' ).forEach( function ( slot ) {
			var mount = slot.querySelector( '.banner-mount' );
			mount.innerHTML = '';

			var made = window.JMKBanner.mount( mount, Object.assign( {}, camp, {
				w: Number( slot.dataset.w ),
				h: Number( slot.dataset.h )
			} ) );
			if ( made ) { bworkMounts.push( made ); }
			fitBanner( slot );
		} );
	}

	if ( $( '#bworkWall' ) ) {
		buildBwork();

		$$( '#bworkTabs .bwork-tab' ).forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				bworkAt = Number( tab.dataset.i ) || 0;
				$$( '#bworkTabs .bwork-tab' ).forEach( function ( t ) {
					t.classList.toggle( 'active', t === tab );
					t.setAttribute( 'aria-selected', t === tab ? 'true' : 'false' );
				} );
				var note = $( '#bworkNote' );
				if ( note && C.bworkNotes ) { note.textContent = C.bworkNotes[ bworkAt ] || ''; }
				buildBwork();
			} );
		} );

		if ( window.ResizeObserver ) {
			var wo = new ResizeObserver( function ( entries ) {
				entries.forEach( function ( e ) {
					var slot = e.target.closest ? e.target.closest( '.banner-slot' ) : null;
					if ( slot ) { fitBanner( slot ); }
				} );
			} );
			$$( '#bworkWall .banner-stage' ).forEach( function ( st ) { wo.observe( st ); } );
		}
		window.addEventListener( 'load', function () {
			$$( '#bworkWall .banner-slot' ).forEach( fitBanner );
		} );
	}

	function fitAllBanners() {
		$$( '#bannerWall .banner-slot' ).forEach( fitBanner );
	}

	if ( $( '#bannerWall' ) ) {
		buildBanners();

		/* Même leçon que pour les jeux, et elle se paie deux fois : la
		   colonne d'une grille n'a pas sa largeur définitive au moment où le
		   script s'exécute. Mesurée trop tôt, elle rend une échelle de 1, et
		   la bannière est simplement rognée par le conteneur — un 728×90
		   dont on ne voit que le tiers gauche. On observe donc la colonne
		   plutôt que de choisir un instant où la mesurer. */
		if ( window.ResizeObserver ) {
			var bo = new ResizeObserver( function ( entries ) {
				entries.forEach( function ( e ) {
					var slot = e.target.closest ? e.target.closest( '.banner-slot' ) : null;
					if ( slot ) { fitBanner( slot ); }
				} );
			} );
			$$( '#bannerWall .banner-stage' ).forEach( function ( st ) { bo.observe( st ); } );
		} else {
			window.addEventListener( 'resize', fitAllBanners );
		}

		window.addEventListener( 'load', fitAllBanners );
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( fitAllBanners );
		}
	}

	/* ══════════ VIGNETTES DU CARREFOUR ══════════
	   Les cartes de l'accueil portent la démonstration en miniature plutôt
	   qu'une capture : une bannière qui défile pour le métier bannière, la
	   roue pour le métier jeu. Même parti pris que partout ailleurs ici —
	   ce qui se montre n'a pas à se raconter. */
	function buildHub() {
		$$( '.hub-demo' ).forEach( function ( host ) {
			var kind = host.dataset.demo;

			if ( 'banner' === kind && 'undefined' !== typeof window.JMKBanner && C.bwork && C.bwork.length ) {
				var box = host.getBoundingClientRect();
				if ( box.width < 40 ) { return; }

				var mount = document.createElement( 'div' );
				mount.style.transformOrigin = 'top left';
				host.appendChild( mount );

				// La vignette montre un 300×250 mis à l'échelle de la carte.
				window.JMKBanner.mount( mount, Object.assign( {}, C.bwork[ 0 ], { w: 300, h: 250 } ) );
				var scale = Math.min( box.width / 300, box.height / 250 );
				mount.style.transform = 'scale(' + scale + ')';
				mount.style.position  = 'absolute';
				mount.style.top       = ( ( box.height - 250 * scale ) / 2 ) + 'px';
				mount.style.left      = ( ( box.width  - 300 * scale ) / 2 ) + 'px';
				return;
			}

			if ( 'game' === kind ) {
				// La roue de l'accueil : dessinée, jamais jouable. Une partie
				// se joue sur la page du métier, où le formulaire existe.
				var cv = document.createElement( 'canvas' );
				cv.width = cv.height = 320;
				cv.style.maxWidth = '76%';
				cv.style.height = 'auto';
				host.appendChild( cv );
				drawMiniWheel( cv );
			}
		} );
	}

	function drawMiniWheel( cv ) {
		var x = cv.getContext( '2d' );
		if ( ! x || ! LOTS.length ) { return; }
		var R = cv.width / 2, n = LOTS.length, seg = ( Math.PI * 2 ) / n, rot = -0.24;

		x.clearRect( 0, 0, cv.width, cv.height );
		x.save();
		x.translate( R, R );
		x.rotate( rot );
		LOTS.forEach( function ( lot, i ) {
			var a0 = -Math.PI / 2 + i * seg;
			x.beginPath();
			x.moveTo( 0, 0 );
			x.arc( 0, 0, R - 10, a0, a0 + seg );
			x.closePath();
			x.fillStyle = lotColor( lot );
			x.fill();
			x.lineWidth = 2;
			x.strokeStyle = 'rgba(0,0,0,.35)';
			x.stroke();
		} );
		x.restore();

		x.beginPath();
		x.arc( R, R, R * 0.2, 0, Math.PI * 2 );
		x.fillStyle = '#150C1D';
		x.fill();
		x.lineWidth = 3;
		x.strokeStyle = brandColor;
		x.stroke();
	}

	if ( $( '.hub-demo' ) ) {
		buildHub();
		window.addEventListener( 'load', function () {
			// Une vignette construite alors que la carte mesure zéro reste
			// vide : on ne recommence que si rien n'a été posé.
			$$( '.hub-demo' ).forEach( function ( h ) {
				if ( ! h.children.length ) { buildHub(); }
			} );
		} );
	}

	/* ══════════ PERSONNALISATION ══════════ */
	function applyBrand() {
		var c = hexToHsl( brandColor ), root = document.documentElement.style;
		root.setProperty( '--accent', brandColor );
		root.setProperty( '--accent-2', hsl( c[ 0 ], c[ 1 ], Math.min( 78, c[ 2 ] + 13 ) ) );
		root.setProperty( '--accent-ink', c[ 2 ] > 58 ? '#221503' : '#FFF6E4' );
		if ( $( '#bHex' ) ) { $( '#bHex' ).textContent = brandColor.toUpperCase(); }
		if ( $( '#brandLabel' ) && brandName ) { $( '#brandLabel' ).textContent = brandName; }
		if ( $( '#hubText' ) ) { $( '#hubText' ).textContent = brandName ? brandName.slice( 0, 10 ).toUpperCase() : T.spin; }
		renderLots();
		drawWheel();
		buildReels();
		pkDraw();
		buildEmbed();
		buildBanners();
		if ( ! scratchDone ) { newCard(); }
	}
	var PRESETS = [ '#D9A441', '#F2506B', '#3E9BF5', '#48C9A9', '#B45CF0', '#FF7A3D' ];
	if ( $( '#swatches' ) ) {
		var host = $( '#swatches' );
		PRESETS.forEach( function ( col, i ) {
			var b = document.createElement( 'button' );
			b.className = 'sw'; b.type = 'button';
			b.style.background = col;
			b.setAttribute( 'aria-label', col );
			b.setAttribute( 'aria-pressed', ( 0 === i ) ? 'true' : 'false' );
			b.addEventListener( 'click', function () {
				brandColor = col;
				if ( $( '#bColor' ) ) { $( '#bColor' ).value = col; }
				host.querySelectorAll( '.sw' ).forEach( function ( x ) { x.setAttribute( 'aria-pressed', 'false' ); } );
				b.setAttribute( 'aria-pressed', 'true' );
				applyBrand(); renderCtas();
			} );
			host.appendChild( b );
		} );
	}
	if ( $( '#bColor' ) ) {
		$( '#bColor' ).addEventListener( 'input', function ( e ) {
			brandColor = e.target.value;
			$$( '.sw' ).forEach( function ( x ) { x.setAttribute( 'aria-pressed', 'false' ); } );
			applyBrand(); renderCtas();
		} );
	}
	if ( $( '#bName' ) ) {
		$( '#bName' ).addEventListener( 'input', function ( e ) {
			brandName = e.target.value.trim();
			applyBrand(); renderCtas();
		} );
	}
	if ( $( '#bLang' ) ) {
		$( '#bLang' ).value = lang;
		$( '#bLang' ).addEventListener( 'change', function ( e ) {
			lang = e.target.value;
			buildEmbed(); renderCtas();
		} );
	}

	/* ══════════ DIVERS ══════════ */
	$$( '[data-scroll]' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			var t = document.getElementById( b.dataset.scroll );
			if ( t ) { t.scrollIntoView( { behavior: reduced ? 'auto' : 'smooth' } ); }
		} );
	} );

	var io = new IntersectionObserver( function ( es ) {
		es.forEach( function ( e ) {
			if ( e.isIntersecting ) { e.target.classList.add( 'in' ); io.unobserve( e.target ); }
		} );
	}, { threshold: 0.12 } );
	$$( '.rv' ).forEach( function ( el ) { io.observe( el ); } );

	window.addEventListener( 'scroll', function () {
		var p = $( '#prog' );
		if ( ! p ) { return; }
		var h = document.documentElement.scrollHeight - window.innerHeight;
		p.style.width = ( h > 0 ? ( window.scrollY / h ) * 100 : 0 ) + '%';
	}, { passive: true } );

	/* ══════════ CARROUSEL DES JEUX ══════════ */
	var track = $( '#gamesTrack' );
	if ( track ) {
		var slides = $$( '#gamesTrack .carousel-slide' ),
			dots   = $$( '#gamesDots .carousel-dot' ),
			prevB  = $( '#gamesPrev' ),
			nextB  = $( '#gamesNext' ),
			atSlide = 0;

		function slideAt() {
			// La vue la plus proche du centre du cadre.
			var mid = track.scrollLeft + track.clientWidth / 2, best = 0, dist = Infinity;
			slides.forEach( function ( s, i ) {
				var c = s.offsetLeft + s.offsetWidth / 2, d = Math.abs( c - mid );
				if ( d < dist ) { dist = d; best = i; }
			} );
			return best;
		}

		function syncNav() {
			atSlide = slideAt();
			dots.forEach( function ( d, i ) {
				var on = ( i === atSlide );
				d.classList.toggle( 'active', on );
				d.setAttribute( 'aria-current', on ? 'true' : 'false' );
			} );
			// En lecture de droite à gauche, scrollLeft devient négatif.
			var max = track.scrollWidth - track.clientWidth - 1;
			var pos = Math.abs( track.scrollLeft );
			if ( prevB ) { prevB.disabled = pos <= 1; }
			if ( nextB ) { nextB.disabled = pos >= max; }
		}

		function goTo( i ) {
			var s = slides[ Math.max( 0, Math.min( slides.length - 1, i ) ) ];
			if ( ! s ) { return; }
			track.scrollTo( {
				left: s.offsetLeft - ( track.clientWidth - s.offsetWidth ) / 2,
				behavior: reduced ? 'auto' : 'smooth'
			} );
		}

		if ( prevB ) { prevB.addEventListener( 'click', function () { goTo( atSlide - 1 ); } ); }
		if ( nextB ) { nextB.addEventListener( 'click', function () { goTo( atSlide + 1 ); } ); }
		dots.forEach( function ( d ) {
			d.addEventListener( 'click', function () { goTo( Number( d.dataset.go ) ); } );
		} );

		track.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowRight' === e.key ) { e.preventDefault(); goTo( atSlide + 1 ); }
			if ( 'ArrowLeft' === e.key )  { e.preventDefault(); goTo( atSlide - 1 ); }
		} );

		var scrollTick = null;
		track.addEventListener( 'scroll', function () {
			clearTimeout( scrollTick );
			// Une vue qui arrive peut n'avoir jamais eu de taille : on la
			// redessine une fois immobile.
			scrollTick = setTimeout( function () { syncNav(); refreshGames(); }, 90 );
		}, { passive: true } );

		syncNav();
	}

	/**
	 * Redessine ce qui dépend d'une taille à l'écran.
	 *
	 * Un canevas construit alors que son conteneur mesurait zéro — vue de
	 * carrousel encore hors champ, feuille de style pas encore appliquée,
	 * onglet ouvert en arrière-plan — reste vide pour toujours. Tout ce qui
	 * se mesure est donc reconstruit dès que la taille devient réelle,
	 * plutôt qu'une seule fois au chargement.
	 */
	function refreshGames() {
		var reels = $( '#reels' );
		if ( reels && reels.clientWidth > 0 && ! reels.querySelector( '.cell' ) ) {
			buildReels();
		}
		if ( pk && pk.clientWidth > 0 && ! pkBusy ) {
			pkDraw();
		}
		if ( sc && sc.clientWidth > 0 && ! scratchDone && 0 === mTick ) {
			sizeScratch();
		}
	}

	// Chaque jeu se (re)construit dès qu'il entre réellement à l'écran.
	if ( 'IntersectionObserver' in window ) {
		var gio = new IntersectionObserver( function ( es ) {
			es.forEach( function ( e ) { if ( e.isIntersecting ) { refreshGames(); } } );
		}, { threshold: 0.05 } );
		$$( '.game-card' ).forEach( function ( el ) { gio.observe( el ); } );
	}

	// Et à chaque changement de largeur, sans marteler.
	var sizeTick = null;
	window.addEventListener( 'resize', function () {
		clearTimeout( sizeTick );
		sizeTick = setTimeout( refreshGames, 140 );
	} );

	// Filet de sécurité : polices et feuilles de style arrivent après nous.
	window.addEventListener( 'load', refreshGames );
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( refreshGames );
	}

	/* ══════════ ANIMATIONS DE LA PAGE ══════════ */

	// Titre du haut : un span par mot, pour une entrée décalée.
	var h1 = $( '.hero h1' );
	if ( h1 && ! reduced ) {
		var wIndex = 0;
		var walk = function ( node ) {
			Array.prototype.slice.call( node.childNodes ).forEach( function ( n ) {
				// Le texte en dégradé se peint via background-clip sur son
				// propre élément : le découper en mots le rend invisible.
				// On l'anime donc d'un bloc, sans y descendre.
				if ( 1 === n.nodeType && n.classList && n.classList.contains( 'accent-text' ) ) {
					n.classList.add( 'w' );
					n.style.animationDelay = ( 0.12 + ( wIndex++ ) * 0.075 ) + 's';
					return;
				}
				if ( 3 === n.nodeType ) {
					var frag = document.createDocumentFragment();
					String( n.textContent ).split( /(\s+)/ ).forEach( function ( part ) {
						if ( ! part.trim() ) { frag.appendChild( document.createTextNode( part ) ); return; }
						var sp = document.createElement( 'span' );
						sp.className = 'w';
						sp.textContent = part;
						sp.style.animationDelay = ( 0.12 + ( wIndex++ ) * 0.075 ) + 's';
						frag.appendChild( sp );
					} );
					n.parentNode.replaceChild( frag, n );
				} else if ( 1 === n.nodeType && 'BR' !== n.tagName ) {
					walk( n );
				}
			} );
		};
		walk( h1 );
	}

	// Révélation décalée pour les grilles.
	$$( '.packs, .works, .skills, .steps, .spec, .reviews, .kpis, .about-stats' )
		.forEach( function ( el ) { el.classList.add( 'stagger' ); } );

	var sio = new IntersectionObserver( function ( es ) {
		es.forEach( function ( e ) {
			if ( e.isIntersecting ) { e.target.classList.add( 'in' ); sio.unobserve( e.target ); }
		} );
	}, { threshold: 0.08 } );
	$$( '.stagger' ).forEach( function ( el ) { sio.observe( el ); } );

	// Les cartes s'inclinent légèrement vers le curseur.
	if ( ! reduced && window.matchMedia( '(hover: hover)' ).matches ) {
		$$( '.pack, .work' ).forEach( function ( card ) {
			card.classList.add( 'tilt' );
			card.addEventListener( 'mousemove', function ( e ) {
				var r = card.getBoundingClientRect(),
					x = ( e.clientX - r.left ) / r.width - 0.5,
					y = ( e.clientY - r.top ) / r.height - 0.5;
				card.style.transform =
					'perspective(900px) rotateX(' + ( -y * 4 ).toFixed( 2 ) + 'deg) rotateY(' +
					( x * 5 ).toFixed( 2 ) + 'deg) translateY(-3px)';
			} );
			card.addEventListener( 'mouseleave', function () { card.style.transform = ''; } );
		} );
	}

	/* démarrage */
	renderLots();
	drawWheel();
	newCard();
	renderQuiz();
	renderOpts();
	updateQuote();
	refreshGames();
} )();
