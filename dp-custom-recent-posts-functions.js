	function addPost() {
	
		var select_value = jQuery("#dp_input_container select").val();
		var number_lines = jQuery("#dp_input_container #inputs_id input").length;

		
		var idlines = [];
		var idnewlines = [];
		var titlelines = [];
		var titlenewlines = []; 
		idnewlines[0] = select_value;
		titlenewlines[0] = "Klicke speichern...";
		var str;

		for (var i=1; i<=number_lines; i++){
		
			idstr = "#input_id" + i;
			idstrlast = "#input_id" + (i+1);
			titlestr = "#dp_input_container #inputs_title #input_title" + i;
			
			//save current values in array
			idlines[i-1] = jQuery(idstr).val();
			titlelines[i-1] = jQuery(titlestr).text();
			
			//move values one index back
			idnewlines[i] = idlines[i-1];
			titlenewlines[i] = titlelines[i-1];
			
			//write new values
			jQuery(idstr).val(idnewlines[i-1]);
			jQuery(titlestr).text(titlenewlines[i-1]);
		}
	}