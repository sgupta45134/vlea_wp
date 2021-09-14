(function($) {

window.CMPPP = {};
window.CMPPP.Utils = {
		
	addSingleHandler: function(handlerName, selector, action, func) {
		var obj;
		if (typeof selector == 'string') obj = $(selector);
		else obj = selector;
		obj.each(function() {
			var obj = $(this);
			if (obj.data(handlerName) != '1') {
				obj.data(handlerName, '1');
				obj.on(action, func);
			}
		});
	},
	
	leftClick: function(func) {
		return function(e) {
			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			func.apply(this, [e]);
			return false;
		}
	},
	
	
	toast: function(msg, className, duration) {
		if (typeof className == 'undefined') className = 'info';
		if (typeof duration == 'undefined') duration = 5;
		var toast = $('<div/>', {"class":"cmppp-toast "+ className, "style":"display:none"});
		toast.html(msg);
		$('body').append(toast);
		toast.fadeIn(500, function() {
			setTimeout(function() {
				toast.fadeOut(500);
			}, duration*1000);
		});
	}
		
};

})(jQuery);
	

jQuery(function($) { // Placeholder support
	$('input[data-placeholder], textarea[data-placeholder]').each(function() {
		var obj = $(this);
		obj.focus(function() {
			if (obj.hasClass('placeholder')) {
				obj.val('');
				obj.removeClass('placeholder')
			}
		}).blur(function() {
			if (obj.val().length == 0) {
				obj.val(obj.data('placeholder'));
				obj.addClass('placeholder');
			}
		}).trigger('blur');
	});
});

