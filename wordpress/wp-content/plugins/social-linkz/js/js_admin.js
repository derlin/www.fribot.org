jQuery(document).ready(function($) {

    
	/* =====================================================================================
	*
	*  Permet le reset d'une URL courte
	*
	*/
    
	jQuery(".resetLink").click( function() {
		var num = this.getAttribute("id").replace("reset","") ;
		jQuery("#wait"+num).show();
		jQuery("#lien"+num).html("Reset in progress...");
		//Supprime la ligne
		var arguments = {
			action: 'reset_link', 
			idLink : num
		} 
		//POST the data and append the results to the results div
		jQuery.post(ajaxurl, arguments, function(response) {
			jQuery("#wait"+num).fadeOut();
			jQuery("#lien"+num).html(response);
		});    
	})
    
    	/* =====================================================================================
	*
	*  Affiche le formulaire de changement de url force
	*
	*/
	
	jQuery(".forceLink").click( function() {
		var num = this.getAttribute("id").replace("force","") ;
		var response = "<label for='shorturl"+num+"'>"+site+"/</label><input name='tag-name' id='shorturl"+num+"' value='' size='10' type='text'><input type='submit' name='' id='valid"+num+"' class='button-primary validButton' value='Update' onclick='validButtonF(this);' /><input type='submit' name='' id='cancel"+num+"' class='button cancelButton' value='Cancel' onclick='cancelButtonF(this);' />" ; 
		jQuery("#lien"+num).html(response);
	})
});

/* =====================================================================================
*
*  Cancel du formulaire
*
*/

function cancelButtonF (element) {
	var num = element.getAttribute("id").replace("cancel","") ;
	jQuery("#wait"+num).show();
	
	var arguments = {
		action: 'cancel_link', 
		idLink : num
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait"+num).fadeOut();
		jQuery("#lien"+num).html(response);
	});    
}

/* =====================================================================================
*
*  Valid du formulaire
*
*/

function validButtonF (element) {
	var num = element.getAttribute("id").replace("valid","") ;
	jQuery("#wait"+num).show();
	var arguments = {
		action: 'valid_link', 
		idLink : num,
		link : document.getElementById("shorturl"+num).value
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait"+num).fadeOut();
		jQuery("#lien"+num).html(response);
	});    
}