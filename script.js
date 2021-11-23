jQuery(function (){
    jQuery('a.wikilink1, a.wikilink2').each(function (idx, elem){

        if(elem[title] && JSINFO.plugins.lms.seen.includes(elem.title)) {
            jQuery(elem).addClass('lms-seen');
        }

    });
});
