(function() {
	$.fn.bootstrapSwitch.defaults.size = 'small';
	$.fn.bootstrapSwitch.defaults.onColor = 'success';
	$.fn.bootstrapSwitch.defaults.offColor = 'danger';
	$.fn.bootstrapSwitch.defaults.onText = "<i class='icon-ok'></i>";
	$.fn.bootstrapSwitch.defaults.offText = "<i class='icon-remove'></i>";
	$( "input.bootstrap-switch-form[type=checkbox]" ).bootstrapSwitch();
}).call(this);