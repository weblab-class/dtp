function signup(event_id) {
	$.ajax({
		type: 'POST',
		url: 'signup_event.php',
		data: {
			'event_id': event_id
		},
		cache: false
	}).done(function() {
		$('#messages').prepend('<div class="ui success compact message"><i class="close icon"></i><div class="header">Event added to schedule!</div></div>');
        $('.message .close').off('click');
        $('.message .close').on('click', function() {
            $(this)
              .closest('.message')
              .transition('fade')
            ;
        });
    eventButton(event_id,true);
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