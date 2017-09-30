/*
 This file is part of ITRO Popup Plugin. (email : support@itroteam.com)
 */

/* -------------- SCRIPTS FOR ADMIN PANNEL */

function itro_pos(e)
{
    e = e || window.event;
    x_pos = e.clientX;
}

function itro_mutual_check(checkbox_id_1, checkbox_id_2, box)
{
    if (!box)
    {
	if (checkbox_id_2 == '') {
	    document.getElementById(checkbox_id_1).checked = !document.getElementById(checkbox_id_1).checked;
	    return 1;
	}
	if (checkbox_id_1 == '') {
	    return 0;
	}
	if (checkbox_id_1 == checkbox_id_2) {
	    return 0;
	}
	document.getElementById(checkbox_id_1).checked = !document.getElementById(checkbox_id_1).checked;
	if (document.getElementById(checkbox_id_1).checked || document.getElementById(checkbox_id_2).checked)
	{
	    document.getElementById(checkbox_id_2).checked = !document.getElementById(checkbox_id_1).checked;
	}
    } else
    {
	if (document.getElementById(checkbox_id_1).checked || document.getElementById(checkbox_id_2).checked)
	{
	    document.getElementById(checkbox_id_2).checked = !document.getElementById(checkbox_id_1).checked;
	}
    }
}

jQuery(document).ready(function ()
{
    var orig_send_to_editor = window.send_to_editor;
    var uploadID = ''; /*setup the var in a global scope*/

    jQuery('.ipp_upload_button').click(function ()
    {
	upload_button = jQuery(this);
	uploadID = jQuery(this).prev('input'); /*set the uploadID variable to the value of the input before the upload button*/
	tb_show('', 'media-upload.php?type=image&amp;amp;amp;TB_iframe=true');

	/* restore send_to_editor() when tb closed */
	jQuery("#TB_window").bind('tb_unload', function ()
	{
	    window.send_to_editor = orig_send_to_editor;
	});

	/* temporarily redefine send_to_editor() */
	window.send_to_editor = function (html)
	{
	    if (jQuery('img', html).attr('src') === undefined) {
		imgurl = jQuery(html).attr('src');
	    } else {
		imgurl = jQuery('img', html).attr('src');
	    }
	    uploadID.val(imgurl); /*assign the value of the image src to the input*/
	   
	    //check the radio button if it is the background source
	    if (upload_button.attr('name') == 'bg_upload_button') {
		jQuery('#yes_bg').prop("checked", true);
	    }
	    tb_remove();
	};
	return false;
    });
});

var itro_option_state;
function itro_check_state(state_check_id)
{
    itro_option_state = state_check_id.selected;
}

function itro_select(target_id)
{
    target_id.selected = !itro_option_state;
    itro_option_state = !itro_option_state;
}

function itro_set_cookie(c_name, value, exhours)
{
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + (exhours * 3600 * 1000));
    var c_value = escape(value) + ((exhours == null) ? "" : "; expires=" + exdate.toUTCString());
    document.cookie = c_name + "=" + c_value + "; path=/";
}