
/*====================================================*/
/* FILE /plugins/social-linkz/js/js_front.js*/
/*====================================================*/
function openEmailSocialLinkz(md5) { 
        jQuery('#mask'+md5).fadeIn(1000);   
        jQuery('#mask'+md5).fadeTo("slow",0.8); 
        var winH = jQuery(window).height();
        var winW = jQuery(window).width();
        jQuery('#dialog'+md5).css('top',  winH/2-jQuery('#dialog'+md5).height()/2);
        jQuery('#dialog'+md5).css('left', winW/2-jQuery('#dialog'+md5).width()/2);
        jQuery('#dialog'+md5).fadeIn(2000);
}

function closeEmailSocialLinkz(md5) { 
        jQuery('#mask'+md5).hide();   
        jQuery('#dialog'+md5).hide();
}


