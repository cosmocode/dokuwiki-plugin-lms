jQuery(function (){

    /**
     * hide all sections and make them togglable
     *
     * This has to be done before shuffling, because it's easier to address nav
     * sections here without affecting list divs
     */
    jQuery('nav > div.level1, nav > div.level2, nav > div.level3, nav > div.level4, nav > div.level5').hide();
    jQuery('nav > h1, nav > h2, nav > h3, nav > h4, nav > h5').click(function (){
        const $hl = jQuery(this);
        $hl.next('div').toggle();
    });

    /**
     * Section shuffle
     *
     * Move lower sections into their preceeding higher section
     */
    for(let level=5; level>1; level--) {
        jQuery(`nav > h${level}, nav > div.level${level}`).each(function (idx, elem){
            const $elem = jQuery(elem);
            const $parent = $elem.prev('div');

            if($parent.hasClass(`level${level - 1}`)) {
                $elem.detach().appendTo($parent);
            }
        });
    }

    /**
     * Open the sections above the current link
     *
     * FIXME this may need adjustment for the mobile menu later
     */
    jQuery(`a[title="${JSINFO.id}"]`).parents('div').show();



});
