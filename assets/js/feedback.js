( function () {
	'use strict';

	var data = window.MeuSideCartData || {};
	var compatibility = data.compatibility || {};
	var i18n = data.i18n || {};
	var toastTimer = null;

	function getToast() {
		return document.querySelector( '[data-msc-add-toast]' );
	}

	function hideToast() {
		var toast = getToast();

		window.clearTimeout( toastTimer );

		if ( ! toast ) {
			return;
		}

		toast.classList.remove( 'msc-is-visible' );
		window.setTimeout( function () {
			if ( toast && ! toast.classList.contains( 'msc-is-visible' ) ) {
				toast.hidden = true;
			}
		}, 190 );
	}

	function showToast() {
		var toast = getToast();
		var message;

		if ( ! compatibility.showAddNotice || ! toast ) {
			return;
		}

		message = toast.querySelector( '[data-msc-add-toast-message]' );

		if ( message ) {
			message.textContent = i18n.productAdded || 'Produto adicionado ao carrinho.';
		}

		window.clearTimeout( toastTimer );
		toast.hidden = false;
		window.requestAnimationFrame( function () {
			toast.classList.add( 'msc-is-visible' );
		} );
		toastTimer = window.setTimeout( hideToast, 4200 );
	}

	function animateTriggers() {
		var style = [ 'pulse', 'bounce' ].indexOf( compatibility.addAnimation ) !== -1 ? compatibility.addAnimation : 'none';
		var reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( 'none' === style || reducedMotion ) {
			return;
		}

		document.querySelectorAll( '[data-msc-open]' ).forEach( function ( trigger ) {
			var className = 'msc-trigger-feedback-' + style;

			trigger.classList.remove( 'msc-trigger-feedback-pulse', 'msc-trigger-feedback-bounce' );
			void trigger.offsetWidth;
			trigger.classList.add( className );
			window.setTimeout( function () {
				trigger.classList.remove( className );
			}, 620 );
		} );
	}

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart', function () {
			animateTriggers();

			if ( false === compatibility.autoOpenOnAdd ) {
				showToast();
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		if ( event.target.closest && event.target.closest( '[data-msc-open]' ) ) {
			hideToast();
		}
	} );
}() );
