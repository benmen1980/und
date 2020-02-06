
jQuery(function($){

    jQuery('.save-field').click(function(){
        data = {
            'action'            : 'update_acf_field',
            'field_name'        : $(this).attr("data-field"),
            'field_value'       : $(this).siblings().val(),
            'id'                : $(this).parent().parent().attr("id").replace('post-', ''),
        };
        updateAcfField(data);
    });
    jQuery('.price-list').change(function(){
        data = {
            'action'            : 'update_acf_field',
            'field_name'        : $(this).attr('data-field'),
            'field_value'       : $(this).val(),
            'id'                : $(this).parent().parent().attr("id").replace('post-', ''),
        };
        updateAcfField(data);
    });

    function updateAcfField (data) {
        jQuery.post(ajaxurl, data, function(response) {

        });
    }


    jQuery('.current.activated-submenu').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');


    
});
