( function () {
	'use strict';

	var data = window.MeuSideCartData || {};
	var i18n = data.i18n || {};
	var compatibility = data.compatibility || {};
	var favoritesKey = 'meu_side_cart_favorites';
	var root = null;
	var panel = null;
	var overlay = null;
	var busy = false;
	var lastFocus = null;
	var quantityTimers = {};

	function selectRoot() {
		root = document.querySelector( '[data-msc-root]' );

		if ( ! root ) {
			return false;
		}

		panel = root.querySelector( '.msc-panel' );
		overlay = root.querySelector( '.msc-overlay' );
		moveFloatingButton();
		updateFloatingOffset();

		return !! panel;
	}

	function closest( element, selector ) {
		return element && element.closest ? element.closest( selector ) : null;
	}

	function updateFloatingOffset() {
		var floatingButton = document.querySelector( '.msc-floating-button' );

		if ( ! root || ! floatingButton ) {
			return;
		}

		var selectors = [
			'.elementor-scroll-to-top',
			'.elementor-widget-scroll-to-top',
			'.scrollToTop',
			'.scroll-to-top',
			'.back-to-top',
			'.go-top',
			'.to-top',
			'#scroll-top',
			'#back-to-top'
		];
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
		var defaultOffset = parseInt( floatingButton.dataset.mscFloatingOffset || '116', 10 );
		var offset = isNaN( defaultOffset ) ? 116 : defaultOffset;

		document.querySelectorAll( selectors.join( ',' ) ).forEach( function ( element ) {
			var rect = element.getBoundingClientRect();
			var style = window.getComputedStyle( element );
			var isVisible = 'none' !== style.display && 'hidden' !== style.visibility && parseFloat( style.opacity || '1' ) > 0;
			var isBottomRight = rect.width > 0 && rect.height > 0 && rect.right > viewportWidth - 180 && rect.bottom > viewportHeight - 180;

			if ( isVisible && isBottomRight ) {
				offset = Math.max( offset, Math.ceil( viewportHeight - rect.top + 34 ) );
			}
		} );

		floatingButton.style.setProperty( '--msc-floating-offset', offset + 'px' );
	}

	function moveFloatingButton() {
		var floatingButton = root ? root.querySelector( '.msc-floating-button' ) : null;

		if ( floatingButton && floatingButton.parentNode !== document.body ) {
			document.body.appendChild( floatingButton );
		}
	}

	function setBusy( value ) {
		if ( ! root ) {
			return;
		}

		busy = value;
		root.classList.toggle( 'msc-is-busy', value );
		root.setAttribute( 'aria-busy', value ? 'true' : 'false' );
	}

	function setOpenState( value ) {
		if ( ! root || ! panel ) {
			return;
		}

		root.classList.toggle( 'msc-is-open', value );
		document.documentElement.classList.toggle( 'msc-lock-scroll', value );
		document.body.classList.toggle( 'msc-lock-scroll', value );
		panel.setAttribute( 'aria-hidden', value ? 'false' : 'true' );

		root.querySelectorAll( '[data-msc-open]' ).forEach( function ( button ) {
			button.setAttribute( 'aria-expanded', value ? 'true' : 'false' );
		} );

		if ( overlay ) {
			overlay.hidden = false;
			window.setTimeout( function () {
				if ( overlay && ! value ) {
					overlay.hidden = true;
				}
			}, value ? 0 : 240 );
		}
	}

	function openCart() {
		if ( ! selectRoot() ) {
			return;
		}

		lastFocus = document.activeElement;
		setOpenState( true );

		window.setTimeout( function () {
			if ( panel ) {
				panel.focus();
			}
		}, 30 );
	}

	function closeCart() {
		setOpenState( false );

		if ( lastFocus && lastFocus.focus ) {
			lastFocus.focus();
		}
	}

	function request( action, payload, trigger, options ) {
		var body = new window.FormData();
		var soft = options && options.soft;
		payload = payload || {};

		body.append( 'action', 'meu_side_cart_' + action );
		body.append( 'nonce', data.nonce || '' );

		Object.keys( payload ).forEach( function ( key ) {
			body.append( key, payload[ key ] );
		} );

		if ( ! soft ) {
			setBusy( true );
		}

		return window.fetch( data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( response ) {
				var payloadData = response && response.data ? response.data : {};

				if ( payloadData.html ) {
					updateCart( payloadData );
				}

				if ( ! response || ! response.success ) {
					throw payloadData;
				}

				return payloadData;
			} )
			.catch( function ( error ) {
				showNotices( error && error.notices ? error.notices : '<ul class="woocommerce-error"><li>' + escapeHtml( error && error.message ? error.message : i18n.genericError ) + '</li></ul>' );
			} )
			.finally( function () {
				if ( ! soft ) {
					setBusy( false );
				}
			} );
	}

	function updateCart( payload ) {
		if ( ! selectRoot() || ! payload ) {
			return;
		}

		var content = root.querySelector( '[data-msc-content]' );

		if ( content && payload.html ) {
			content.innerHTML = payload.html;
		}

		if ( typeof payload.count !== 'undefined' ) {
			root.dataset.count = payload.count;
			document.querySelectorAll( '[data-msc-count]' ).forEach( function ( counter ) {
				counter.textContent = payload.count;
			} );
		}

		if ( payload.notices ) {
			showNotices( payload.notices );
		}

		syncFavoriteButtons();

		if ( window.jQuery ) {
			window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		}
	}

	function showNotices( html ) {
		if ( ! selectRoot() ) {
			return;
		}

		var target = root.querySelector( '[data-msc-notices]' );

		if ( target ) {
			target.innerHTML = html || '';
		}
	}

	function escapeHtml( value ) {
		var div = document.createElement( 'div' );
		div.textContent = value || '';
		return div.innerHTML;
	}

	function readFavorites() {
		try {
			return JSON.parse( window.localStorage.getItem( favoritesKey ) || '[]' ).map( String );
		} catch ( error ) {
			return [];
		}
	}

	function writeFavorites( ids ) {
		try {
			window.localStorage.setItem( favoritesKey, JSON.stringify( ids ) );
		} catch ( error ) {}
	}

	function setFavoriteButtonState( button, active ) {
		button.classList.toggle( 'msc-is-favorite', active );
		button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
	}

	function syncFavoriteButtons() {
		if ( ! selectRoot() ) {
			return;
		}

		var favorites = readFavorites();
		root.querySelectorAll( '[data-msc-favorite]' ).forEach( function ( button ) {
			setFavoriteButtonState( button, favorites.indexOf( String( button.dataset.productId ) ) !== -1 );
		} );
	}

	function showMessageNotice( message ) {
		showNotices( '<div class="woocommerce-message">' + escapeHtml( message ) + '</div>' );
	}

	function toggleFavorite( button ) {
		var productId = String( button.dataset.productId || '' );
		var favorites = readFavorites();
		var index = favorites.indexOf( productId );
		var active = -1 === index;

		if ( ! productId ) {
			return;
		}

		if ( active ) {
			favorites.push( productId );
		} else {
			favorites.splice( index, 1 );
		}

		writeFavorites( favorites );
		syncFavoriteButtons();
		showMessageNotice( active ? i18n.favoriteAdded || 'Produto adicionado aos favoritos.' : i18n.favoriteRemoved || 'Produto removido dos favoritos.' );
	}

	function updateQuantity( input, value ) {
		var item = closest( input, '[data-msc-cart-item]' );

		if ( ! item ) {
			return;
		}

		var next = Math.max( 0, parseInt( value, 10 ) || 0 );
		var max = parseInt( input.getAttribute( 'max' ), 10 );

		if ( ! isNaN( max ) && max >= 0 ) {
			next = Math.min( next, max );
		}

		input.value = next;
		window.clearTimeout( quantityTimers[ item.dataset.cartKey ] );
		quantityTimers[ item.dataset.cartKey ] = window.setTimeout( function () {
			request( 'update_quantity', {
				cart_key: item.dataset.cartKey,
				quantity: next,
			}, input, {
				soft: true,
			} );
		}, 260 );
	}

	function refresh( shouldOpen ) {
		request( 'refresh', {}, null, {
			soft: true,
		} ).then( function () {
			if ( shouldOpen ) {
				openCart();
			}
		} );
	}

	function bindWooEvents() {
		if ( ! window.jQuery ) {
			return;
		}

		window.jQuery( document.body ).on( 'added_to_cart', function () {
			refresh( false !== compatibility.autoOpenOnAdd );
		} );

		window.jQuery( document.body ).on( 'removed_from_cart updated_cart_totals updated_wc_div', function () {
			refresh( false );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		if ( ! selectRoot() ) {
			return;
		}

		var target = event.target;
		var opener = closest( target, '[data-msc-open]' );
		var closer = closest( target, '[data-msc-close]' );
		var removeItem = closest( target, '[data-msc-remove-item]' );
		var removeCoupon = closest( target, '[data-msc-remove-coupon]' );
		var favorite = closest( target, '[data-msc-favorite]' );
		var minus = closest( target, '[data-msc-qty-minus]' );
		var plus = closest( target, '[data-msc-qty-plus]' );

		if ( opener ) {
			event.preventDefault();
			openCart();
			return;
		}

		if ( closer && root.contains( closer ) ) {
			event.preventDefault();
			closeCart();
			return;
		}

		if ( busy ) {
			return;
		}

		if ( removeItem && root.contains( removeItem ) ) {
			event.preventDefault();
			request( 'remove_item', {
				cart_key: removeItem.dataset.cartKey,
			}, removeItem );
			return;
		}

		if ( removeCoupon && root.contains( removeCoupon ) ) {
			event.preventDefault();
			request( 'remove_coupon', {
				coupon: removeCoupon.dataset.coupon,
			}, removeCoupon );
			return;
		}

		if ( favorite && root.contains( favorite ) ) {
			event.preventDefault();
			toggleFavorite( favorite );
			return;
		}

		if ( minus || plus ) {
			var item = closest( target, '[data-msc-cart-item]' );
			var input = item ? item.querySelector( '[data-msc-qty-input]' ) : null;
			var current = input ? parseInt( input.value, 10 ) || 0 : 0;
			var next = plus ? current + 1 : current - 1;

			event.preventDefault();

			if ( input ) {
				updateQuantity( input, next );
			}
		}
	} );

	document.addEventListener( 'submit', function ( event ) {
		if ( ! selectRoot() || busy ) {
			return;
		}

		var couponForm = closest( event.target, '[data-msc-coupon-form]' );

		if ( couponForm && root.contains( couponForm ) ) {
			event.preventDefault();
			request( 'apply_coupon', {
				coupon: couponForm.querySelector( '[name="coupon"]' ).value,
			}, couponForm.querySelector( 'button[type="submit"]' ) );
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( ! selectRoot() || busy ) {
			return;
		}

		var input = closest( event.target, '[data-msc-qty-input]' );

		if ( input && root.contains( input ) ) {
			updateQuantity( input, parseInt( input.value, 10 ) || 0 );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && root && root.classList.contains( 'msc-is-open' ) ) {
			closeCart();
		}
	} );

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			selectRoot();
			syncFavoriteButtons();
			bindWooEvents();
			updateFloatingOffset();
		} );
	} else {
		selectRoot();
		syncFavoriteButtons();
		bindWooEvents();
		updateFloatingOffset();
	}

	window.addEventListener( 'resize', updateFloatingOffset );
	window.addEventListener( 'scroll', updateFloatingOffset, { passive: true } );
}() );
