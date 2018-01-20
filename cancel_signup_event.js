function cancel_signup(event_id) {
	$.ajax({
		type: 'POST',
		url: 'cancel_signup_event.php',
		data: {
			'event_id': event_id
		},
		cache: false
	}).done(function() {
		$('#messages').prepend('<div class="ui success compact message"><i class="close icon"></i><div class="header">Event cancelled</div></div>');
        $('.message .close').off('click');
        $('.message .close').on('click', function() {
            $(this)
              .closest('.message')
              .transition('fade')
            ;
        });
	})
	.fail(function() {
        $('#messages').prepend('<div class="ui error compact message"><i class="close icon"></i><div class="header">Your request was not received by the server.</div><p>If the problem persists, contact a Network Adminstrator.</p></div>');
        $('.message .close').off('click');
        $('.message .close').on('click', function() {
            $(this)
              .closest('.message')
              .transition('fade')
            ;
        });
    });
}