(function($) {
	$(document).ready(function() {
		$("#send_order").submit(function(event) {
			var url = $(this).attr('action');
			var file_data = $('#fileupload').prop('files')[0];
			var form_data = new FormData();
			$(this).find('input, select').each(function(i, e) {
				if ($(e).attr('type') != 'file') {
					var name = $(e).attr('name');
					if ($(e).get(0).nodeName == 'SELECT') {
						value = $(e).find('option:selected').val();
					}else {
						value = $(e).val();
					}
					form_data.append(name, value);
				}
			});
			form_data.append('upload', file_data);
			form_data.append('action', 'send_order');
			$("#send_order").find('button[type=submit]').prop('disabled', true).css({background: '#eee', color: '#777'});
			$.ajax({
				url: url,
				type: 'post',
				contentType: false,
				processData: false,
				data: form_data,
				success: function(data) {
					$("#send_order").find('button[type=submit]').prop('disabled', false).removeAttr('style');
					if (data == 'success') {
						$("#form-success").html($("<span/>").addClass('fine').text('sent successfully'));
						$("input").val('');
					}else {
						$("#form-success").html($("<span/>").addClass('fail').text('an error occurred while sending the data'));
					}
				},
				error: function() {
					console.log('Error');
				}
			});
			event.preventDefault();
		});

		$("#proceed_order").click(function(event) {
			$(this).prop('disabled', true).css({background: '#eee', color: '#777'});
			$.ajax({
				url: $(this).attr('href'),
				type: 'post',
				data: {action: 'proceed_order'},
				success: function(data) {
					if (data != 'error') {
						$("#proceed-res").html($('<span/>').addClass('fine').text('Your order code is #' + data));
					}else {
						$("#proceed-res").html($('<span/>').addClass('fail').text('Does not contain products the cart'));
					}
				}
			});
			event.preventDefault();
		});
	});
})(jQuery);