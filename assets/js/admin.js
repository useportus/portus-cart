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

		var previewDevice = preview.dataset.previewDevice || 'desktop';
		var previewSection = preview.closest('.portus-cart-for-woocommerce-admin__preview-section');
		var deviceSwitcher = previewSection ? previewSection.querySelector('[data-preview-device-switcher]') : null;

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

		function previewSetting(key, fallback) {
			var fieldValue = value(key);
			var attributeValue;

			if (null !== fieldValue) {
				return fieldValue;
			}

			attributeValue = preview.getAttribute('data-preview-setting-' + key.replace(/_/g, '-'));

			return null === attributeValue ? fallback : attributeValue;
		}

		function clamp(number, min, max) {
			return Math.max(min, Math.min(max, number));
		}

		function hexToRgb(valueToConvert) {
			var hex = String(valueToConvert || '').replace('#', '');

			if (3 === hex.length) {
				hex = hex.split('').map(function (character) {
					return character + character;
				}).join('');
			}

			if (!/^[0-9a-f]{6}$/i.test(hex)) {
				return '255,255,255';
			}

			return [
				parseInt(hex.slice(0, 2), 16),
				parseInt(hex.slice(2, 4), 16),
				parseInt(hex.slice(4, 6), 16)
			].join(',');
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
				'overlay_opacity',
				'floating_button_size',
				'floating_icon_size'
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

		function updateFloatingButtonPreview() {
			var button = preview.querySelector('[data-preview-floating-button]');
			var icon = button ? button.querySelector('.portus-cart-for-woocommerce-preview__floating-icon') : null;
			var buttonSize = parseInt(previewSetting('floating_button_size', '50'), 10);
			var iconSize = parseInt(previewSetting('floating_icon_size', '28'), 10);
			var iconKey = previewSetting('floating_icon', 'bag-fill');
			var shape = previewSetting('floating_shape', 'circle');
			var counterPosition = previewSetting('floating_counter_position', 'center');
			var backgroundColor = previewSetting('floating_background_color', '#FFFFFF');
			var primaryColor = previewSetting('primary_color', '#00053A');
			var iconColor = 'custom' === previewSetting('floating_icon_color_mode', 'primary') ? previewSetting('floating_icon_color', '#00053A') : primaryColor;
			var counterBackground = previewSetting('floating_counter_background', '#C0A821');
			var counterColor = previewSetting('floating_counter_text_color', '#FFFFFF');
			var enabled = 'yes' === previewSetting('enabled_floating_button', 'yes');
			var deviceEnabled = 'mobile' === previewDevice ? 'yes' === previewSetting('show_floating_mobile', 'yes') : 'yes' === previewSetting('show_floating_desktop', 'yes');

			if (!button || !icon) {
				return;
			}

			buttonSize = isNaN(buttonSize) ? 50 : clamp(buttonSize, 44, 80);
			iconSize = isNaN(iconSize) ? 28 : clamp(iconSize, 16, Math.min(40, buttonSize - 8));

			preview.style.setProperty('--aurea-preview-floating-button-size', buttonSize + 'px');
			preview.style.setProperty('--aurea-preview-floating-icon-size', iconSize + 'px');
			preview.style.setProperty('--aurea-preview-floating-background-rgb', hexToRgb(backgroundColor));
			preview.style.setProperty('--aurea-preview-floating-icon-color', iconColor || primaryColor);
			preview.style.setProperty('--aurea-preview-floating-counter-background', counterBackground);
			preview.style.setProperty('--aurea-preview-floating-counter-color', counterColor);

			['bag-fill', 'cart', 'basket', 'bag'].forEach(function (candidate) {
				icon.classList.toggle('portus-cart-for-woocommerce-preview__floating-icon--' + candidate, candidate === iconKey);
			});

			['circle', 'rounded'].forEach(function (candidate) {
				button.classList.toggle('portus-cart-for-woocommerce-preview__floating--' + candidate, candidate === shape);
			});

			['center', 'top-right', 'top-left'].forEach(function (candidate) {
				button.classList.toggle('portus-cart-for-woocommerce-preview__floating--counter-' + candidate, candidate === counterPosition);
			});

			button.classList.toggle('portus-cart-for-woocommerce-preview__floating--counter-filled', 'yes' === previewSetting('floating_counter_background_enabled', 'no'));
			button.hidden = !enabled || !deviceEnabled;
		}

		function setPreviewDevice(nextDevice) {
			previewDevice = 'mobile' === nextDevice ? 'mobile' : 'desktop';
			preview.dataset.previewDevice = previewDevice;
			preview.classList.toggle('portus-cart-for-woocommerce-preview--device-mobile', 'mobile' === previewDevice);
			preview.classList.toggle('portus-cart-for-woocommerce-preview--device-desktop', 'desktop' === previewDevice);

			if (deviceSwitcher) {
				deviceSwitcher.querySelectorAll('[data-preview-device]').forEach(function (button) {
					button.setAttribute('aria-pressed', String(button.dataset.previewDevice === previewDevice));
				});
			}

			updateFloatingButtonPreview();
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
			updateFloatingButtonPreview();

			[
				'cart_title',
				'checkout_button_text',
				'cart_button_text'
			].forEach(setText);

			[
				'show_low_stock_alerts',
				'show_cart_button'
			].forEach(setToggle);

			updateStockText();
		}

		form.addEventListener('input', updatePreview);
		form.addEventListener('change', updatePreview);

		if (deviceSwitcher) {
			deviceSwitcher.addEventListener('click', function (event) {
				var button = event.target.closest('[data-preview-device]');

				if (button) {
					setPreviewDevice(button.dataset.previewDevice);
				}
			});
		}

		setPreviewDevice(previewDevice);
		updatePreview();
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
}());
