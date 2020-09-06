
jQuery(function ($) {

    jQuery('.save-field').click(function () {
        data = {
            'action': 'update_acf_field',
            'field_name': $(this).attr("data-field"),
            'field_value': $(this).siblings().val(),
            'id': $(this).parent().parent().attr("id").replace('post-', ''),
        };
        updateAcfField(data);
    });
    jQuery('.price-list').change(function () {
        data = {
            'action': 'update_acf_field',
            'field_name': $(this).attr('data-field'),
            'field_value': $(this).val(),
            'id': $(this).parent().parent().attr("id").replace('post-', ''),
        };
        updateAcfField(data);
    });

    function updateAcfField(data) {
        jQuery.post(ajaxurl, data, function (response) {

        });
    }


    jQuery('.current.activated-submenu').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');




    $('body').on('click', '.misha_upload_image_button', function (e) {
        e.preventDefault();

        var button = $(this),
            custom_uploader = wp.media({
                title: 'Insert image',
                library: {
                    // uncomment the next line if you want to attach image to the current post
                    // uploadedTo : wp.media.view.settings.post.id, 
                    type: 'image'
                },
                button: {
                    text: 'Use this image' // button label text
                },
                multiple: false // for multiple image selection set to true
            }).on('select', function () { // it also has "open" and "close" events 
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                jQuery(button).closest('.nipl_varible_wrp').find('.camp_varible_img').val(attachment.id);

                $default_thumb = jQuery(button).closest('.nipl_varible_wrp').attr('data-thumbid');
                var extra_cls = 'nipl_grn_border';

                if ($default_thumb !== String(attachment.id)) {
                    jQuery(button).addClass(extra_cls);
                } else {
                    jQuery(button).removeClass(extra_cls);
                }
                $(button).removeClass('button').html('<img class="true_pre_image " src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
                /* if you sen multiple to true, here is some code for getting the image IDs
                var attachments = frame.state().get('selection'),
                    attachment_ids = new Array(),
                    i = 0;
                attachments.each(function(attachment) {
                        attachment_ids[i] = attachment['id'];
                    console.log( attachment );
                    i++;
                });
                */
            })
                .open();
    });

    /*
     * Remove image event
     */
    $('body').on('click', '.misha_remove_image_button', function () {
        $(this).hide().prev().val('').prev().addClass('button').html('Upload image');
        return false;
    });
});
