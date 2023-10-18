jQuery(function (){
    if(!JSINFO['plugins']) return;
    if(!JSINFO.plugins['lms']) return;

    if (JSINFO.ACT !== 'admin') {
        jQuery('a.wikilink1, a.wikilink2').each(function (idx, elem){

            if(elem['title'] && JSINFO.plugins.lms.seen.includes(elem.title)) {
                jQuery(elem).addClass('lms-seen');
            }

        });
    }

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

    /**
     * admin interface: autocomplete users
     */
    const $form = jQuery('.dokuwiki.mode_admin form#lms__admin-autocomplete');
    if (!$form.length) return;

    $form.find('input')
        .autocomplete({
            source: function (request, response) {
                jQuery.getJSON(DOKU_BASE + 'lib/exe/ajax.php?call=plugin_lms_autocomplete', {
                    user: request.term,
                    sectok: $form.find('input[name="sectok"]').val()
                }, response);
            },
            minLength: 0
        });
});
