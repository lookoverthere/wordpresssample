(function ($) {
	'use strict';
	function setPreview($wrap, attachment) {
		const url = attachment.sizes?.medium?.url || attachment.url;
		$wrap.find('.partner-logo-preview').html(
			$('<img>', { src: url, alt: '', css: { maxWidth: '150px', height: 'auto' } })
		);
		$wrap.find('#partner_logo_id').val(attachment.id);
		$wrap.find('.partner-logo-remove').prop('disabled', false);
	}
	$(document).on('click', '.partner-logo-upload', function (e) {
		e.preventDefault();
		const $wrap = $(this).closest('td');
		const frame = wp.media({
			title: 'Select Partner Logo',
			button: { text: 'Use this logo' },
			multiple: false,
			library: { type: 'image' },
		});
		frame.on('select', function () {
			setPreview($wrap, frame.state().get('selection').first().toJSON());
		});
		frame.open();
	});
	$(document).on('click', '.partner-logo-remove', function (e) {
		e.preventDefault();
		const $wrap = $(this).closest('td');
		$wrap.find('.partner-logo-preview').empty();
		$wrap.find('#partner_logo_id').val('');
		$(this).prop('disabled', true);
	});
})(jQuery);