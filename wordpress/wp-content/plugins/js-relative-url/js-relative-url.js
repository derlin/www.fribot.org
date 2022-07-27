jQuery('document').ready( function( $ ){ 

    /*
     transform relative url to absolute ones.
     Links with the class "add-site-prefix" will have their href modified from:
         /wp-content/uploads/pdfs/PT-aperitifs-cocktails.pdf 
     to
        http://localhost/perrin-test/wp-content/uploads/pdfs/PT-aperitifs-cocktails.pdf 
     for example
     */
    $('a.add-site-prefix').each(function(){
        // note: site_url is a global variable defined in the footer, it
        // corresponds to the wordpress function site_url()
        if($(this).attr('href') != undefined ){
            attr = 'href';
        }else if($(this).attr('src') != undefined ){
            attr = 'src';
        }else{
            return;
        }

        $(this).attr(attr, site_url + $(this).attr(attr) ); 
        console.log('modified ' + $(this).attr(attr));
        $(this).removeClass('add-site-prefix');
    });

    console.log("js rel");

});