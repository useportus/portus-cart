( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var createElement = element.createElement;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var ToggleControl = components.ToggleControl;
	var __ = i18n.__;

	blocks.registerBlockType( 'portus/cart-trigger', {
		title: __( 'Portus Cart', 'portus-cart-for-woocommerce' ),
		description: __( 'Botão para abrir o carrinho lateral no cabeçalho ou no conteúdo.', 'portus-cart-for-woocommerce' ),
		icon: 'cart',
		category: 'widgets',
		attributes: {
			label: { type: 'string', default: '' },
			display: { type: 'string', default: '' },
			icon: { type: 'string', default: '' },
			counter: { type: 'boolean' }
		},
		edit: function ( props ) {
			var attributes = props.attributes;
			var label = attributes.label || __( 'Carrinho', 'portus-cart-for-woocommerce' );
			var display = attributes.display || 'icon-text';

			return createElement(
				'div',
				{ className: 'portus-cart-trigger-block-editor' },
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __( 'Botão do carrinho', 'portus-cart-for-woocommerce' ), initialOpen: true },
						createElement( TextControl, {
							label: __( 'Texto', 'portus-cart-for-woocommerce' ),
							value: attributes.label,
							onChange: function ( value ) { props.setAttributes( { label: value } ); }
						} ),
						createElement( SelectControl, {
							label: __( 'Conteúdo', 'portus-cart-for-woocommerce' ),
							value: attributes.display,
							options: [
								{ label: __( 'Usar configuração global', 'portus-cart-for-woocommerce' ), value: '' },
								{ label: __( 'Ícone e texto', 'portus-cart-for-woocommerce' ), value: 'icon-text' },
								{ label: __( 'Somente ícone', 'portus-cart-for-woocommerce' ), value: 'icon' },
								{ label: __( 'Somente texto', 'portus-cart-for-woocommerce' ), value: 'text' }
							],
							onChange: function ( value ) { props.setAttributes( { display: value } ); }
						} ),
						createElement( SelectControl, {
							label: __( 'Ícone', 'portus-cart-for-woocommerce' ),
							value: attributes.icon,
							options: [
								{ label: __( 'Usar configuração global', 'portus-cart-for-woocommerce' ), value: '' },
								{ label: __( 'Sacola atual', 'portus-cart-for-woocommerce' ), value: 'bag-fill' },
								{ label: __( 'Carrinho clássico', 'portus-cart-for-woocommerce' ), value: 'cart' },
								{ label: __( 'Cesta', 'portus-cart-for-woocommerce' ), value: 'basket' },
								{ label: __( 'Sacola minimalista', 'portus-cart-for-woocommerce' ), value: 'bag' }
							],
							onChange: function ( value ) { props.setAttributes( { icon: value } ); }
						} ),
						createElement( ToggleControl, {
							label: __( 'Mostrar contador', 'portus-cart-for-woocommerce' ),
							checked: 'undefined' === typeof attributes.counter ? true : attributes.counter,
							onChange: function ( value ) { props.setAttributes( { counter: value } ); }
						} )
					)
				),
				createElement(
					'button',
					{ type: 'button', className: 'button button-secondary', disabled: true },
					'text' !== display ? createElement( 'span', { className: 'dashicons dashicons-cart', 'aria-hidden': true } ) : null,
					'icon' !== display ? createElement( 'span', null, label ) : null,
					false !== attributes.counter ? createElement( 'strong', null, '2' ) : null
				)
			);
		},
		save: function () {
			return null;
		}
	} );
}( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n ) );
