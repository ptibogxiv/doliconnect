( function( editor, components, i18n, element ) {
	var el = element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var BlockControls = wp.blockEditor.BlockControls;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var TextControl = wp.components.TextControl;
  var ToggleControl = wp.components.ToggleControl;
  var ServerSideRender = wp.components.ServerSideRender;

	registerBlockType( 'doliconnect/discountproduct-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
		title: i18n.__( 'discountProduct', 'doliconnect'), // The title of our block.
		description: i18n.__( 'A block for displaying dolibarr product.', 'doliconnect'), // The description of our block.
		icon: 'store', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
		category: 'widgets', // The category of the block.

		edit: function( props ) {

			var attributes = props.attributes;
			var productID = props.attributes.productID;
			var hideButtonToCart= props.attributes.hideButtonToCart;
      var hideDuration = props.attributes.hideDuration;
      var hideStock = props.attributes.hideStock;

			return [
			el( 'div', { className: 'components-block-description' },
        el(ServerSideRender, {
          block: "doliconnect/discountproduct-block",
					attributes: props.attributes
				} ),
			),
			el( InspectorControls, { key: 'inspector' }, // Display the block options in the inspector panel.
					el( components.PanelBody, {
						title: i18n.__( 'Social Media Links', 'doliconnect'),
						className: 'block-social-links',
						initialOpen: true,
					},      
				)
			),
			];
		},

save: function() {
        // Rendering in PHP
        return null;
    },
	} );

} )(
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element,
);