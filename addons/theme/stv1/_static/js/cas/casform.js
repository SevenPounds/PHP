jQuery.fn.setCasForm = function (cas_server, username_id, password_id) {
	this.attr('method', 'POST');
	this.attr('action', cas_server + '/login?service=' + this.attr('action') + '&loginurl=' + this.attr('action'));
	
	input1 = jQuery("<input type='hidden' name='lt' />");
	input1.attr('value', 'LT-f2760c5f9b0f4034e69f2fbae4f1323d');
		
	input2 = jQuery("<input type='hidden' name='execution' />");
	input2.attr('value', 'e1s1');
		
	input3 = jQuery("<input type='hidden' name='_eventId' />");
	input3.attr('value', 'submit');
	
	
	if (typeof username_id != 'undefined') {
		input4 = jQuery("<input type='hidden' name='username' id='username' />");
		this.append(input4);
		
		jQuery("#" + username_id).bind("change keyup input", function(){
			jQuery("#username").val(jQuery("#" + username_id).val());
		});	
	}
	
	if (typeof password_id != 'undefined') {
		input5 = jQuery("<input type='hidden' name='password' id='password' />");
		this.append(input5);
		
		jQuery("#" + password_id).bind("change keyup input", function(){
			jQuery("#password").val(jQuery("#" + password_id).val());
		});
	
	}
	
	this.append(input1);
	this.append(input2);
	this.append(input3);
}

