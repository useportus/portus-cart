(function () {
	'use strict';

	var config = window.aureaCartAdmin || {};
	var optionName = config.optionName || 'meu_side_cart_settings';

	function boot() {
		var preview = document.querySelector('[data-portus-cart-for-woocommerce-preview]');
		var form = document.querySelector('.portus-cart-for-woocommerce-admin form[action="options.php"]');

		if (!preview || !form) {
			return;
		}

		function field(key) {
			var name = optionName + '[' + key + ']';

			if (form.elements && form.elements.namedItem(name)) {
				return form.elements.namedItem(name);
			}

			return form.querySelector('[name="' + name + '"]');
		}

		function value(key) {
			var element = field(key);

			if (!element) {
				return null;
			}

			if ('checkbox' === element.type) {
				return element.checked ? 'yes' : 'no';
			}

			return element.value;
		}

		function setText(key) {
			var nextValue = value(key);

			if (null === nextValue) {
				return;
			}

			preview.querySelectorAll('[data-preview-text="' + key + '"]').forEach(function (element) {
				element.textContent = nextValue;
			});
		}

		function setToggle(key) {
			var nextValue = value(key);

			if (null === nextValue) {
				return;
			}

			preview.querySelectorAll('[data-preview-toggle="' + key + '"]').forEach(function (element) {
				element.hidden = 'yes' !== nextValue;
			});
		}

		function updateColors() {
			var primary = value('primary_color');
			var accent = value('accent_color');

			if (primary) {
				preview.style.setProperty('--aurea-preview-primary', primary);
			}

			if (accent) {
				preview.style.setProperty('--aurea-preview-accent', accent);
			}
		}

		function updateStockText() {
			var threshold = value('low_stock_threshold');
			var target = preview.querySelector('[data-preview-stock]');

			if (null === threshold || !target) {
				return;
			}

			target.textContent = 'Estoque baixo: restam ' + threshold + ' unidades.';
		}

		function updateRangeValues() {
			[
				'cart_z_index',
				'panel_width',
				'overlay_opacity'
			].forEach(function (key) {
				var nextValue = value(key);

				if (null === nextValue) {
					return;
				}

				document.querySelectorAll('[data-aurea-range-value="' + key + '"]').forEach(function (element) {
					element.textContent = nextValue;
				});
			});
		}

		function updateCompatibilityPreview() {
			var panelWidth = parseInt(value('panel_width') || '440', 10);
			var overlayOpacity = parseInt(value('overlay_opacity') || '48', 10);
			var floatingSide = value('floating_side') || 'right';

			if (!isNaN(panelWidth)) {
				panelWidth = Math.max(320, Math.min(720, panelWidth));
				preview.style.setProperty('--aurea-preview-panel-width', panelWidth + 'px');
			}

			if (!isNaN(overlayOpacity)) {
				overlayOpacity = Math.max(0, Math.min(90, overlayOpacity)) / 100;
				preview.style.setProperty('--aurea-preview-overlay-opacity', String(overlayOpacity));
			}

			preview.classList.toggle('portus-cart-for-woocommerce-preview--floating-left', 'left' === floatingSide);
			preview.classList.toggle('portus-cart-for-woocommerce-preview--floating-right', 'left' !== floatingSide);
		}

		function updatePreview() {
			updateColors();
			updateRangeValues();
			updateCompatibilityPreview();

			[
				'cart_title',
				'checkout_button_text',
				'cart_button_text'
			].forEach(setText);

			[
				'show_low_stock_alerts',
				'show_cart_button',
				'enabled_floating_button'
			].forEach(setToggle);

			updateStockText();
		}

		form.addEventListener('input', updatePreview);
		form.addEventListener('change', updatePreview);
		updatePreview();
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
}());
