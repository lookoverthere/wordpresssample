(function (wp) {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, SelectControl, RangeControl, TextControl } = wp.components;
	const { createElement: el } = wp.element;
	const ServerSideRender = wp.serverSideRender;

	registerBlockType('partner-organizations/partner-list', {
		edit: function (props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();

			return el(
				'div',
				blockProps,
				el(InspectorControls, {},
					el(PanelBody, { title: 'Partner settings' },
						el(SelectControl, {
							label: 'Category',
							value: attributes.category,
							options: [
								{ label: 'All', value: '' },
								{ label: 'Education', value: 'education' },
								{ label: 'Nonprofit', value: 'nonprofit' },
								{ label: 'Corporate', value: 'corporate' },
							],
							onChange: (value) => setAttributes({ category: value }),
						}),
                        el(TextControl, {
                            label: 'Title',
                            value: attributes.title,
                            onChange: (value) => setAttributes({ title: value }),
                        }),
						el(RangeControl, {
							label: 'Columns',
							value: attributes.columns,
							onChange: (value) => setAttributes({ columns: value }),
							min: 1,
							max: 6,
						})
					)
				),
				el(ServerSideRender, {
					block: 'partner-organizations/partner-list',
					attributes: attributes,
				})
			);
		},
	});
})(window.wp);