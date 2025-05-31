( function( editor, components, i18n, element ) {
const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;

registerBlockType('doliconnect/agenda-block', {
    title: 'My Custom Block',
    icon: 'smiley',
    category: 'widgets',
    edit: () => createElement('p', null, 'Hello from the editor!'),
    save: () => createElement('p', null, 'Hello from the frontend!'),
});

} )(
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element,
);