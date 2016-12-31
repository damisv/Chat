$(document).ready(function(){
	
	$('.chat-header').click(function(){
		$('.chat-body').slideToggle('slow');
	});

	$('.msg-header').click(function(){
		$('.msg-wrap').slideToggle('slow');
	});
	
	$('.close').click(function(){
		$('.msg-box').hide();
	});
	
	$('.user').click(function(){
		$('.msg-wrap').show();
		$('.msg-box').show();
	});

});