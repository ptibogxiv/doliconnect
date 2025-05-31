( function( editor, components, i18n, element ) {
const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { TextControl } = wp.components;

registerBlockType('doliconnect/agenda-block', {
    title: 'My Custom Block',
    icon: 'smiley',
    category: 'widgets',
    attributes: {
        content: {
            type: 'string',
            default: ''
        }
    },
    edit: (props) => {
        return createElement(
            'div',
            {
                className: 'data-request-form-wrapper',
                style: {
                    fontStyle: 'italic',
                    color: '#333333',
                    backgroundColor: '#eaeaea',
                    paddingTop: '1em',
                    paddingBottom: '1.5em',
                    marginBottom: '0'
                }
            },
            createElement(
                TextControl,
                {
                    label: 'Texte à afficher',
                    value: props.attributes.content,
                    onChange: (value) => props.setAttributes({ content: value })
                }
            )
        );
    },
    save: (props) => createElement('p', null, props.attributes.content),
});

} )(
    window.wp.editor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element,
);