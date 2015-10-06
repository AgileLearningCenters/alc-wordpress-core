/* Selecting valid menu item based on the current tab */


jQuery(document).ready(function() {
    if (ub_admin.current_menu_sub_item !== null) {
        jQuery('#adminmenu .wp-submenu li.current').removeClass("current");
        jQuery('a[href="admin.php?page=branding&tab=' + ub_admin.current_menu_sub_item + '"]').parent().addClass("current");
    }
});

/* Native WP media for custom login image module */

jQuery(document).ready(function($)
{
    var $main_fav_image = $("#ub_main_site_favicon"),
        $main_favicon = $('#wp_favicon'),
        $main_fav_id = $('#wp_favicon_id'),
        $main_fav_size = $('#wp_favicon_size');

    jQuery('#wp_login_image_button').click(function()
    {
        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery('#wp_login_image').val(attachment.url);
            jQuery('#wp_login_image_id').val(attachment.id);
            jQuery('#wp_login_image_size').val(props.size);
        };

        wp.media.string.props = function(props, attachment)
        {
            jQuery('#wp_login_image').val(props.url);
            jQuery('#wp_login_image_id').val("");
            jQuery('#wp_login_image_size').val("full");
            jQuery('#wp_login_image_width').val(props.width);
            jQuery('#wp_login_image_height').val(props.height);
            return props;
        };

        wp.media.editor.open();
        return false;
    });

    $('#wp_favicon_button').click(function(e)
    {
        e.preventDefault();

        /**
         * Sets favicon
         *
         * @param props
         * @param attachment
         */
        wp.media.editor.send.attachment = function(props, attachment)
        {
            $main_fav_image.prop("src", attachment.url);
            $main_favicon.val(attachment.url);
            $main_fav_id.val(attachment.id);
            $main_fav_size.val(props.size);
        };



        /**
         * Opens media browser
         */
        wp.media.editor.open();
    });

    /**
     * Update main favicon if url is changed via paste
     */
    $("#wp_favicon").on("change", function(e){
        $main_fav_image.prop("src", $(this).val());
        $main_favicon.val( $(this).val() );
        $main_fav_id.val("");
        $main_fav_size.val("full");
    });


    /**
     * Browses and sets the proper favicon for each sub-site
     *
     */
    $(document).on("click", '.ub_favicons_browse', function(e)
    {
        e.preventDefault();

        var $this = $(this),
            $tr = $this.closest("tr"),
            $url = $tr.find(".ub_favicons_fav_url"),
            $id = $tr.find(".ub_favicons_fav_id"),
            $size = $tr.find(".ub_favicons_fav_size"),
            $image = $tr.find(".ub_favicons_fav");


        /**
         * Sets favicon from image gallery
         *
         * @param props
         * @param attachment
         */
        wp.media.editor.send.attachment = function(props, attachment)
        {
            $image.prop("src", attachment.url);
            $url.val(attachment.url);
            $id.val(attachment.id);
            $size.val(props.size);
        };

        /**
         * Sets favicon from Url
         *
         * @param props
         * @param attachment
         * @returns {*}
         */
        wp.media.string.props = function(props, attachment){
            var $spinner = $(".spinner").first().clone(),
                temp_image = new Image(),
                $temp_image = $(temp_image);

            /**
             * Show loader until the image is fully loaded then place show the actual image
             */
            $temp_image.appendTo("body").hide();
            temp_image.src = props.url;

            if( !$image.find(".spinner").length )
                $image.before( $spinner.show() );

            $image.hide();

            $temp_image.on("load", function(){
                $spinner.remove();
                $temp_image.remove();
                $image.prop("src", props.url).show();
            });

            $url.val(props.url);
            return props;
        };

        // Opens media browser
        wp.media.editor.open();
    });

    $(".ub_favicons_fav_url").on("change", function(e){
        var $this = $(this),
            $tr = $this.closest("tr"),
            $id = $tr.find(".ub_favicons_fav_id"),
            $size = $tr.find(".ub_favicons_fav_size"),
            $image = $tr.find(".ub_favicons_fav"),
            val = $(this).val();

        if( val.length < 3 )
            val = $image.data("default");


        $image.prop("src", val);
        $id.val("");
        $size.val("full");
    });

    /**
     * Save blogs favicon
     */
    $(document).on("click",".ub_favicons_save", function(e) {
        var $this = $(this),
            $tr = $this.closest("tr"),
            $inputs = $tr.find("input"),
            $spinner = $tr.find(".spinner"),
            data = {action: "ub_save_favicon"};

        $inputs.each(function(){
           var $this = $(this);
           data[this.name] = $this.val();
        });

        e.preventDefault();
        $spinner.show();
        $.ajax({
            url : ajaxurl,
            type: "post",
            data: data,
            complete: function(){
                $spinner.hide();
            },
            success: function(){

            },
            error: function(){

            }
        });
    });

    /**
     * Reset blog's favicon
     */
    $(document).on("click", ".ub_favicons_reset", function(e){
        var $this = $(this),
            $tr = $this.closest("tr"),
            $image = $tr.find(".ub_favicons_fav"),
            $url = $tr.find(".ub_favicons_fav_url"),
            $spinner = $tr.find(".spinner"),
            id = $this.data("id"),
            nonce = $("#ub_favicons_" + id +  "_reset").val(),
            data = {action: "ub_reset_favicon", id: id, nonce: nonce };

        e.preventDefault();
        $spinner.show();
        $.ajax({
            url : ajaxurl,
            type: "post",
            data: data,
            complete: function(){
                $spinner.hide();
            },
            success: function(res){
                if( res.success ){
                    $image.prop("src", res.data.fav);
                    $url.val("");
                }
            },
            error: function(){

            }
        });
    })

});

/**
 * Color picker
 */
jQuery(document).ready(function($){
    $('.ub_color_picker').wpColorPicker();


    $(".ub_css_editor").each(function(){
        var editor = ace.edit(this.id);

        $(this).data("editor", editor);
        editor.setTheme("ace/theme/monokai");
        editor.getSession().setMode("ace/mode/css");
        editor.getSession().setUseWrapMode(true);
        editor.getSession().setUseWrapMode(false);

       // editor
    });

    $(".ub_css_editor").each(function(){
        var self = this,
            $input = $( $(this).data("input") );
        $(this).data("editor").getSession().on('change', function () {
            //console.log(this);
//            $input.val(editor.getSession().getValue());
            $input.val( $(self).data("editor").getSession().getValue()  );
            //console.log( $(self).data("editor").getSession().getValue() );
        });
    });


});
