( function( editor, components, i18n, element ) {
	var el = element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var BlockControls = wp.editor.BlockControls;
	var MediaUpload = wp.editor.MediaUpload;
	var InspectorControls = wp.editor.InspectorControls;
	var TextControl = wp.components.TextControl;
  var ToggleControl = wp.components.ToggleControl;
  var ServerSideRender = wp.components.ServerSideRender;

	registerBlockType( 'doliconnect/product-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
		title: i18n.__( 'Product'), // The title of our block.
		description: i18n.__( 'A block for displaying dolibarr product.' ), // The description of our block.
		icon: 'store', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
		category: 'common', // The category of the block.
		attributes: { // Necessary for saving block content.
			mediaID: {
				type: 'number',
			},
			mediaURL: {
				type: 'string',
				source: 'attribute',
				selector: 'img',
				attribute: 'src',
			},
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
			var onSelectImage = function( media ) {
				return props.setAttributes( {
					mediaURL: media.url,
					mediaID: media.id,
				} );
			};

			return [
				el( BlockControls, { key: 'controls' }, // Display controls when the block is clicked on.
					el( 'div', { className: 'components-toolbar' },
						el( MediaUpload, {
							onSelect: onSelectImage,
							type: 'image',
							render: function( obj ) {
								return el( components.Button, {
									className: 'components-icon-button components-toolbar__control',
									onClick: obj.open
									},
									el( 'svg', { className: 'dashicon dashicons-edit', width: '20', height: '20' },
										el( 'path', { d: "M2.25 1h15.5c.69 0 1.25.56 1.25 1.25v15.5c0 .69-.56 1.25-1.25 1.25H2.25C1.56 19 1 18.44 1 17.75V2.25C1 1.56 1.56 1 2.25 1zM17 17V3H3v14h14zM10 6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zm3 5s0-6 3-6v10c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V8c2 0 3 4 3 4s1-3 3-3 3 2 3 2z" } )
									)
								);
							}
						} )
					),
				),
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
				el( 'div', { className: props.className },
					el( 'div', {
						className: attributes.mediaID ? 'doliconnect-product-image image-active' : 'doliconnect-product-image image-inactive',
						style: attributes.mediaID ? { backgroundImage: 'url('+attributes.mediaURL+')' } : {}
					},
						el( MediaUpload, {
							onSelect: onSelectImage,
							type: 'image',
							value: attributes.mediaID,
							render: function( obj ) {
								return el( components.Button, {
									className: attributes.mediaID ? 'image-button' : 'button button-large',
									onClick: obj.open
									},
									! attributes.mediaID ? i18n.__( 'Upload Image' ) : el( 'img', { src: attributes.mediaURL } )
								);
							}
						} )
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
