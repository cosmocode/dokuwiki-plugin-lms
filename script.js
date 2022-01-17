jQuery(function (){
    if(!JSINFO['plugins']) return;
    if(!JSINFO.plugins['lms']) return;

    jQuery('a.wikilink1, a.wikilink2').each(function (idx, elem){

        if(elem['title'] && JSINFO.plugins.lms.seen.includes(elem.title)) {
            jQuery(elem).addClass('lms-seen');
        }

    });

    // mark whole sections seen
    const navheaders = jQuery('nav div.content h1, nav div.content h2, nav div.content h3, nav div.content h4, nav div.content h5');
    navheaders.each(function (idx, header) {
        const $list = jQuery(jQuery(header).next()[0]).find('ul');
        if ($list.length) {
            if ($list.find('a').length === $list.find('a.lms-seen').length) {
                jQuery(header).addClass('lms-seen');
            }
        }
    });

});
