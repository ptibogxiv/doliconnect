( function( editor, components, i18n, element ) {
	var el = element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var BlockControls = wp.editor.BlockControls;
	var InspectorControls = wp.editor.InspectorControls;
	var TextControl = wp.components.TextControl;
  var ToggleControl = wp.components.ToggleControl;
  var ServerSideRender = wp.components.ServerSideRender;

	registerBlockType( 'doliconnect/product-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
		title: i18n.__( 'Product'), // The title of our block.
		description: i18n.__( 'A block for displaying dolibarr product.' ), // The description of our block.
		icon: 'store', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
		category: 'widgets', // The category of the block.
		attributes: { // Necessary for saving block content.
			productID: {
				type: 'text',
			},
      showButtonToCart: {
        type: 'boolean',
        default: false,
      }, 
      hideDuration: {
        type: 'boolean',
        default: false,
      },
		},

		edit: function( props ) {

			var attributes = props.attributes;
			var productID = props.attributes.productID;
			var showButtonToCart = props.attributes.showButtonToCart;
      var hideDuration = props.attributes.hideDuration;

			return [
				el( InspectorControls, { key: 'inspector' }, // Display the block options in the inspector panel.
					el( components.PanelBody, {
						title: i18n.__( 'Social Media Links' ),
						className: 'block-social-links',
						initialOpen: true,
					},
						el( 'p', {}, i18n.__( 'Add links to your social media profiles.' ) ),
						el( TextControl, {
							type: 'text',
							label: i18n.__( 'Product ID' ),
							value: productID,
							onChange: function( newProduct ) {
								props.setAttributes( { productID: newProduct } );
							},
						} ),
            el( ToggleControl, {
              label: i18n.__( 'Add to cart' ),
              checked: showButtonToCart,
              onChange: function onChange() {
              props.setAttributes({ showButtonToCart: !showButtonToCart });
							},
						} ),
            el( ToggleControl, {
              label: i18n.__( 'Hide duration' ),
              checked: hideDuration,
              onChange: function onChange() {
              props.setAttributes({ hideDuration: !hideDuration });
							},
						} ),            
				 	),
				),
        el(ServerSideRender, {
                block: "doliconnect/product-block",
                attributes:  props.attributes
            })
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