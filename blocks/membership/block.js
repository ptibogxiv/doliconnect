( function( editor, components, i18n, element ) {
var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    ToggleControl = wp.components.ToggleControl,
    ServerSideRender = wp.components.ServerSideRender;


	registerBlockType( 'doliconnect/membership-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
		title: i18n.__( 'Membership'), // The title of our block.
		description: i18n.__( 'A block for displaying membership.' ), // The description of our block.
		icon: 'tickets-alt', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
		category: 'widgets', // The category of the block.

		edit: function( props ) {

			var attributes = props.attributes;

			return [
        el(ServerSideRender, {
                block: "doliconnect/membership-block",
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