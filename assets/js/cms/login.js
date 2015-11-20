var LETV = LETV || {}; 
    
(function() {
	
	LoginController = function () {
		this._el = {
			login_form : '#login-form',
			email_input : '#email',
			password_input : '#password',
			submit_btn : '#submit-btn'
		}
	};
	
	LoginController.prototype.init = function () {
		var self = this;
		
		$(self._el.login_form).validate({
			rules: {
				email: {
					required: true,
					email: true
				},
				password: {
					required: true
				}
			},
			messages: {
				password: {
					required: "Please provide a password"
				},
				email: "Please enter a valid email address"
			},
			showErrors: function(errorMap, errorList) {
				jQuery.each( errorList, function(index, value) {
					$.notify({
						icon: 'glyphicon glyphicon-warning-sign',
						title: 'Error',
						message: value.message
					},{
						position: 'fixed',
						type: 'danger',
						allow_dismiss: true,
						newest_on_top: true,
						offset: 20,
						spacing: 10,
						z_index: 1000,
						delay: 2000,
						timer: 1000,
						animate: {
							enter: 'animated fadeInDown',
							exit: 'animated fadeOutUp'
						}
					});
				});
			},
			onkeyup: false,
			onfocusout: false
		});
		
		$(self._el.submit_btn).bind('click', function(event){
			event.preventDefault();
			$(self._el.login_form).submit();
		})
	};
	
	$( document ).ready(function() {
		var loginController = new LoginController();
		loginController.init();
	});
})(LETV);