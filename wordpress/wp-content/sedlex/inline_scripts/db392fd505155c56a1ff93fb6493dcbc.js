
/*====================================================*/
/* FILE /plugins/social-linkz/core/js/ajax_error_admin.js*/
/*====================================================*/
jQuery().ready(function(){
	jQuery.ajaxSetup({
		error:function(x,e) {
			if(x.status==0) {
				//alert('You are offline!!\n Please Check Your Network.');
			} else if(x.status==404) {
				alert('Requested URL not found.');
			} else if(x.status==500){
				alert('Internal Server Error.');
			} else if(e=='parsererror') {
				alert('Error.\nParsing JSON Request failed.');
			} else if(e=='timeout') { 
				alert('Request Time out.');
			} else {
				alert('Unknow Error.\n'+x.responseText);
			}
		}
	});
});

/*====================================================*/
/* FILE /plugins/social-linkz/core/js/core_admin.js*/
/*====================================================*/
/* =====================================================================================
*
*  Get the plugin Info
*
*/

function pluginInfo(id_div, url, plugin_name) {
	
	//POST the data and append the results to the results div
	rand = Math.floor(Math.random()*3000) ; 
	window.setTimeout(function() {
		var arguments = {
			action: 'pluginInfo', 
			plugin_name : plugin_name, 
			url : url
		} 
		
		jQuery.post(ajaxurl, arguments, function(response) {
			if (response!="-1") {
				jQuery('#'+id_div).html(response);
			} else {
				pluginInfo(id_div, url, plugin_name) ; 
			}
		});
	}, rand) ; 
}

/* =====================================================================================
*
*  Get the core Info
*
*/

function coreInfo(md5, url, plugin_name, current_core, current_finger, author, src_wait, msg_wait) {
	
	//POST the data and append the results to the results div
	rand = Math.floor(Math.random()*3000) ; 
	window.setTimeout(function() {
		var arguments = {
			action: 'coreInfo', 
			plugin_name : plugin_name, 
			current_finger : current_finger, 
			current_core : current_core, 
			author : author, 
			md5 : md5, 
			src_wait : src_wait, 
			msg_wait : msg_wait, 
			url : url
		} 
		
		waitImg = "<p>"+msg_wait+"<img id='corePluginWait_"+md5+"' src='"+src_wait+"'></p>" ;
		
		jQuery('#corePlugin_'+md5).html(waitImg);

		jQuery.post(ajaxurl, arguments, function(response) {
			if (response!="-1") {
				jQuery('#corePlugin_'+md5).html(response);
			} else {
				coreInfo(md5, url, plugin_name, current_core, current_finger, author); 
			}
		});
	}, rand) ; 
}

/* =====================================================================================
*
*  Update the core
*
*/

function coreUpdate(md5, url, plugin_name, current_core, current_finger, author, from, to, src_wait, msg_wait) {
	var arguments = {
		action: 'coreUpdate', 
		plugin_name : plugin_name, 
		current_finger : current_finger, 
		current_core : current_core, 
		author : author, 
		url : url,
		from : from, 
		to : to
	} 
	
	waitImg = "<p>"+msg_wait+"<img id='corePluginWait_"+md5+"' src='"+src_wait+"'></p>" ;
	jQuery('#corePlugin_'+md5).html(waitImg);
	
	jQuery.post(ajaxurl, arguments, function(response) {
		if (response!="-1") {
			jQuery('#corePlugin_'+md5).html(response);
		} else {
			coreUpdate(md5, url, plugin_name, current_core, current_finger, author, from, to) ; 
		}
	});
	return false ; 
}


/* =====================================================================================
*
*  Change the version of the plugin
*
*/

function changeVersionReadme(md5, plugin) {
	jQuery("#wait_changeVersionReadme_"+md5).show();
	var arguments = {
		action: 'changeVersionReadme', 
		plugin : plugin
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery('body').append(response);
		jQuery("#wait_changeVersionReadme_"+md5).hide();
	});
}


/* =====================================================================================
*
*  Save the version and the readme txt
*
*/

function saveVersionReadme(plugin) {
	jQuery("#wait_save").show();
	readmetext = jQuery("#ReadmeModify").val() ; 
	versiontext = jQuery("#versionNumberModify").val() ; 
	var arguments = {
		action: 'saveVersionReadme', 
		readme : readmetext, 
		plugin : plugin,
		version : versiontext
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery('#readmeVersion').html(response);
	});
}

/* =====================================================================================
*
*  Save todoList
*
*/

function saveTodo(md5, plugin) {
	jQuery("#wait_savetodo_"+md5).show();
	jQuery("#savedtodo_"+md5).hide();
	textTodo = jQuery("#txt_savetodo_"+md5).val() ; 
	
	var arguments = {
		action: 'saveTodo', 
		textTodo: textTodo, 
		plugin : plugin
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_savetodo_"+md5).hide();
		if (response!="ok") {
			jQuery("#errortodo_"+md5).html(response);
		} else {
			jQuery("#savedtodo_"+md5).show();
			jQuery("#errortodo_"+md5).html("");
		}
	});
}




/*====================================================*/
/* FILE /plugins/social-linkz/core/js/feedback_admin.js*/
/*====================================================*/



/* =====================================================================================
*
*  Send the modified translation
*
*/

function send_feedback(plug_param, plug_ID) {
	jQuery("#wait_feedback").show();
	jQuery("#feedback_submit").remove() ;
		
	var arguments = {
		action: 'send_feedback', 
		name : jQuery("#feedback_name").val(), 
		mail : jQuery("#feedback_mail").val(), 
		comment : jQuery("#feedback_comment").val(), 
		plugin : plug_param,
		pluginID : plug_ID
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_feedback").fadeOut();
		jQuery("#form_feedback_info").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#top_feedback";
	});    
}

function modifyFormContact() {
	name = jQuery("#feedback_name").val() ; 
	mail = jQuery("#feedback_mail").val() ;
	var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
	
	if ((name.length!=0)&&(mail.length!=0)&&(mail.search(emailRegEx)!=-1)) {
		jQuery("#feedback_submit_button").removeAttr('disabled');
	} else {
		jQuery("#feedback_submit_button").attr('disabled', 'disabled') ; 	
	}
	
}

/*====================================================*/
/* FILE /plugins/social-linkz/core/js/parameters_admin.js*/
/*====================================================*/
/* =====================================================================================
*
*  Toggle folder
*
*/

function activateDeactivate_Params(param, toChange) {
	
	isChecked = jQuery("#"+param).is(':checked');
	
	for (i=0; i<toChange.length; i++) {
		if (!isChecked) {
			if (toChange[i].substring(0, 1)!="!") {
				jQuery("label[for='"+toChange[i]+"']").parents("tr").eq(0).hide() ; 
				jQuery("#"+toChange[i]).attr('disabled', 'disabled') ; 
			} else {
				jQuery("label[for='"+toChange[i].substring(1)+"']").parents("tr").eq(0).show() ; 
				jQuery("#"+toChange[i].substring(1)).removeAttr('disabled') ;
			}
		} else {
			if (toChange[i].substring(0, 1)!="!") {
				jQuery("label[for='"+toChange[i]+"']").parents("tr").eq(0).show() ; 
				jQuery("#"+toChange[i]).removeAttr('disabled') ;
			} else {
				jQuery("label[for='"+toChange[i].substring(1)+"']").parents("tr").eq(0).hide() ; 
				jQuery("#"+toChange[i].substring(1)).attr('disabled', 'disabled') ; 		
			}
		}
	}
	return isChecked ; 
}



/*====================================================*/
/* FILE /plugins/social-linkz/core/js/progressbar_admin.js*/
/*====================================================*/
/* =====================================================================================
*
*  Modify the progression
*
*/

function progressBar_modifyProgression(newPercentage,id) {
	id = typeof(id) != 'undefined' ? id : "progressbar";
	jQuery("#"+id+"_image").animate({width: newPercentage+'%'}, 500, function() {  });
}

/* =====================================================================================
*
*  Modify the text
*
*/

function progressBar_modifyText(newText, id) {
	id = typeof(id) != 'undefined' ? id : "progressbar";
	jQuery("#"+id+"_text").html(newText);
}


/*====================================================*/
/* FILE /plugins/social-linkz/core/js/svn_admin.js*/
/*====================================================*/

/* =====================================================================================
*
*  Display a popup with all the information on which files has changed
*
*/

function showSvnPopup(md5, plugin) {
	jQuery("#wait_popup_"+md5).show();
	var arguments = {
		action: 'svn_show_popup', 
		plugin : plugin
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery('body').append(response);
		jQuery("#wait_popup_"+md5).hide();
	});
}

/* =====================================================================================
*
*  Launch the execution of either the update or the checkout of the SVN repository
*
*/

function svnExecute(sens, plugin, random) {
	
	jQuery("#confirm_to_svn1").hide() ; 
	jQuery("#confirm_to_svn2").hide() ; 
	
	jQuery('.toModify'+random).attr('disabled', true);
	jQuery('.toPut'+random).attr('disabled', true);
	jQuery('.toDelete'+random).attr('disabled', true);
	jQuery('.toPutFolder'+random).attr('disabled', true);
	jQuery('.toDeleteFolder'+random).attr('disabled', true);
	
	list = new Array() ; 	
	
	tick = jQuery('.toModify'+random) ; 
	for (var i=0 ; i<tick.length ; i++) {
		if (tick.eq(i).attr('checked')=='checked') {
			list.push(new Array(tick.eq(i).val(), 'modify')) ; 
		}
	}
	tick = jQuery('.toPut'+random) ; 
	for (var i=0 ; i<tick.length ; i++) {
		if (tick.eq(i).attr('checked')=='checked') {
			list.push(new Array(tick.eq(i).val(), 'add')) ; 
		}
	}
	tick = jQuery('.toDelete'+random) ; 
	for (var i=0 ; i<tick.length ; i++) {
		if (tick.eq(i).attr('checked')=='checked') {
			list.push(new Array(tick.eq(i).val(), 'delete')) ; 
		}
	}
	tick = jQuery('.toPutFolder'+random) ; 
	for (var i=0 ; i<tick.length ; i++) {
		if (tick.eq(i).attr('checked')=='checked') {
			list.push(new Array(tick.eq(i).val(), 'add_folder')) ; 
		}
	}
	tick = jQuery('.toDeleteFolder'+random) ; 
	for (var i=0 ; i<tick.length ; i++) {
		if (tick.eq(i).attr('checked')=='checked') {
			list.push(new Array(tick.eq(i).val(), 'delete_folder')) ; 
		}
	}
	
	if (sens=="toRepo") {
		jQuery("#wait_svn1").show();
		
		arguments = {
			action: 'svn_to_repo', 
			plugin: plugin, 
			comment: jQuery("#svn_comment").val(), 
			files: list
		} 

		//POST the data and append the results to the results div
		jQuery.post(ajaxurl, arguments, function(response) {
			jQuery("#wait_svn").hide();
			jQuery("#console_svn").html(response);
		}); 
		
	} else if (sens=="toLocal") {
		
		jQuery("#wait_svn2").show();
		
		arguments = {
			action: 'svn_to_local', 
			plugin: plugin, 
			files: list
		} 

		//POST the data and append the results to the results div
		jQuery.post(ajaxurl, arguments, function(response) {
			jQuery("#wait_svn").hide();
			jQuery("#console_svn").html(response);
		});    	
	}
}




function repoToSvn(plugin) {
	jQuery("#wait_svn").show();
	jQuery("#svn_button").remove() ;
		
	var arguments = {
		action: 'repo_to_svn', 
		plugin : plugin, 
		comment : jQuery("#svn_comment").val()
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#svn_div").html(response);
	});    
}





/*====================================================*/
/* FILE /plugins/social-linkz/core/js/translation_admin.js*/
/*====================================================*/

/* =====================================================================================
*
*  Add a new translation
*
*/

function translate_add(plug_param,dom_param,is_framework) {
	if (is_framework!="false") {
		var num = jQuery("#new_translation_frame option:selected").val() ;
		jQuery("#wait_translation_add_frame").show();
	} else {
		var num = jQuery("#new_translation option:selected").val() ;
		jQuery("#wait_translation_add").show();
	}	
	var arguments = {
		action: 'translate_add', 
		idLink : num,
		isFramework : is_framework,
		plugin : plug_param, 
		domain : dom_param
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_add").fadeOut();
		jQuery("#wait_translation_add_frame").fadeOut();
		jQuery("#zone_edit").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#edit_translation";
	});    
}

/* =====================================================================================
*
*  Save the new translation
*
*/

function translate_create(plug_param,dom_param,is_framework, lang_param, nombre) {

	jQuery("#wait_translation_create").show();
	
	var result = new Array() ; 
	for (var i=0 ; i<nombre ; i++) {
		result[i] = jQuery("#trad"+i).val()  ;
	}
	
	var arguments = {
		action: 'translate_create', 
		idLink : result,
		isFramework : is_framework,
		name : jQuery("#nameAuthor").val(), 
		email : jQuery("#emailAuthor").val(), 
		lang : lang_param, 
		plugin : plug_param, 
		domain : dom_param
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_create").fadeOut();
		jQuery("#zone_edit").html("");
		jQuery("#summary_of_translations").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#info";
	});    
}

/* =====================================================================================
*
*  Modify a translation
*
*/

function modify_trans(plug_param,dom_param,is_framework,lang_param) {
	jQuery("#wait_translation_create").show();
	
	var arguments = {
		action: 'translate_modify', 
		isFramework : is_framework,
		lang : lang_param, 
		plugin : plug_param, 
		domain : dom_param
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_create").fadeOut();
		jQuery("#zone_edit").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#edit_translation";
	});    
}

/* =====================================================================================
*
*  Save the modification of the translation
*
*/

function translate_save_after_modification (plug_param,dom_param,is_framework,lang_param, nombre) {

	jQuery("#wait_translation_modify").show();
	
	var result = new Array() ; 
	for (var i=0 ; i<nombre ; i++) {
		result[i] = jQuery("#trad"+i).val()  ;
	}
		
	var arguments = {
		action: 'translate_create', 
		idLink : result,
		isFramework : is_framework,
		name : jQuery("#nameAuthor").val(), 
		email : jQuery("#emailAuthor").val(), 
		lang : lang_param, 
		plugin : plug_param, 
		domain : dom_param
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_modify").fadeOut();
		jQuery("#zone_edit").html("");
		jQuery("#summary_of_translations").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#info";
	});    
}

/* =====================================================================================
*
*  Send the modified translation
*
*/

function send_trans(plug_param,dom_param, is_framework, lang_param) {

	jQuery("#wait_translation_modify").show();
		
	var arguments = {
		action: 'send_translation', 
		lang : lang_param, 
		isFramework : is_framework,
		plugin : plug_param, 
		domain : dom_param
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_modify").fadeOut();
		jQuery("#zone_edit").html(response);
		window.location = String(window.location).replace(/\#.*$/, "") + "#edit_translation";
	});    
}

/* =====================================================================================
*
*  Download a WP translation
*
*/

function download_trans() {

	var num = jQuery("#download_translation option:selected").val() ;
	jQuery("#wait_translation_download").show();
		
	var arguments = {
		action: 'download_translation', 
		lang : num, 
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_download").fadeOut();
		jQuery("#download_zone").html(response);
	});    
}

/* =====================================================================================
*
*  Download a WP translation
*
*/

function download_trans_2(num) {
	jQuery("#wait_translation_download").show();
		
	var arguments = {
		action: 'download_translation', 
		lang : num, 
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#wait_translation_download").fadeOut();
		jQuery("#download_zone").html(response);
	});    
}

/* =====================================================================================
*
*  Download a WP translation
*
*/

function set_language() {
	jQuery("#wait_translation_set").show();
	var num = jQuery("#set_translation option:selected").val() ;

	var arguments = {
		action: 'set_translation', 
		lang : num, 
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		if (response=="") {
			location.reload();
		} else {
			jQuery("#wait_translation_set").fadeOut();
			jQuery("#set_trans_error").html(response);		
		}
		
	});    
}

/* =====================================================================================
*
*  Download a WP translation
*
*/

function get_languages() {
	jQuery("#wait_translation_get").show();

	var arguments = {
		action: 'update_languages_wp_init'
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
			jQuery("#info_get_trans").html(response);
			get_languages_2(0) ; 
	});    
}

/* =====================================================================================
*
*  Download a WP translation
*
*/

function get_languages_2(numero) {

	var arguments = {
		action: 'update_languages_wp_list', 
		num: numero
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
			res = response.split(",") ; 
			progressBar_modifyProgression(Math.floor(res[1]/res[2]*100));
			progressBar_modifyText(res[0]+ " ("+Math.floor(res[1]/res[2]*100)+"%)");
			if (res[1]==res[2]) {
				jQuery("#info_get_trans").html("");
				download_trans_2("") ; // To refresh
			} else {
				get_languages_2(res[1]) ;
			}
	});    
}

/* =====================================================================================
*
*  Import a translation
*
*/

function importTranslation(path1, path2) {
	var arguments = {
		action: 'importTranslation', 
		path1: path1, 
		path2: path2
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		window.location = String(window.location);
	});    
}


/* =====================================================================================
*
*  Delete a translation
*
*/

function deleteTranslation(path1) {
	var arguments = {
		action: 'deleteTranslation', 
		path1: path1
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		window.location = String(window.location);
	});    
}

/* =====================================================================================
*
*  See modification of a translation
*
*/

function seeTranslation(path1, path2) {
	var arguments = {
		action: 'seeTranslation', 
		path1: path1, 
		path2: path2
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#console_trans").html(response);
	});    
}

/* =====================================================================================
*
*  See modification of a translation
*
*/

function mergeTranslationDifferences(path1, path2) {
	var md5 = [] ; 
	jQuery('input:checkbox:checked').each(function(){
		if (this.name.indexOf("new_")==0) {
    		md5.push( this.name );
    	}
	});


	md5_to_replace = md5.join(',') ; 
	var arguments = {
		action: 'mergeTranslationDifferences', 
		md5: md5_to_replace, 
		path1: path1, 
		path2: path2
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		if (response=="ok") 
			window.location = String(window.location);
		else
			jQuery("#console_trans").html(response);
	});    
}

/* =====================================================================================
*
*  Merge translation files
*
*/

function mergeTranslation(path1, path2) {
	var md5_to_replace = "" ; 
	
	var arguments = {
		action: 'mergeTranslationDifferences', 
		md5: md5_to_replace, 
		path1: path1, 
		path2: path2
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		if (response=="ok") 
			window.location = String(window.location);
		else
			jQuery("#console_trans").html(response);
	});    
}

/*====================================================*/
/* FILE /plugins/social-linkz/js/js_admin.js*/
/*====================================================*/
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
