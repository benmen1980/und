jQuery(document).ready(function($){

    jQuery('.inside').on('click', 'li.button-tab', function(){
        let tab = jQuery(this).data('tab');
        jQuery(this).closest('.variation-list').find('.tab-content .tab-pane').removeClass('show-tab');
        jQuery(this).closest('.variation-list').find('.' + tab).addClass('show-tab');
        jQuery(this).siblings().removeClass('active');
        jQuery(this).addClass('active');
    });

    jQuery('.inside').on('click', 'input.tab_checked', function(){
        let tab = jQuery(this).data('checked');
        let status = jQuery(this).prop("checked");
        let product_id =  jQuery(this).closest('tr').data('id');
        jQuery(this).closest('table.product-assign').find('.choices-list tr[data-id=' + product_id + ']').each(function () {
            jQuery(this).find('input.tab_checked[data-checked=' + tab + ']').prop("checked", status);
            jQuery(this).find('.' + tab + ' input').each(function () {
                jQuery(this).prop("checked", status);
            })
        })

    });
    jQuery('.inside').on('click', '.tab-content input', function(){
        let input = jQuery(this).attr('data-variation');
        let status = jQuery(this).prop("checked");

        jQuery('.inside').find('.tab-content input[data-variation="' + input + '"]').each(function () {
            jQuery(this).prop("checked", status);
            checkAllInput(jQuery(this).closest('.tab-pane'));
        })

    });
});

function checkAllInput(tabs) {
    var allChecked = true;

    $(tabs).find('input').each(function () {

        if (!$(this).prop("checked")) {

            allChecked = false;
            return false;
        }

    })

    let tab = $(tabs).data('tab');
    $(tabs).closest('.variation-list').find('.navigation-tabs input[data-checked="' + tab + '"] ').prop("checked", allChecked);

}
//add and remove product button
jQuery('.inside').on('click', '.btn-add-product', function(){
    //save meta in input
    let addedId = $(this).attr('data-id');
    let parent_section = $(this).closest('.assign-products-meta-box');

    //update add row
    let already_have_row = $(parent_section).find('.product-assign tr[data-id='+addedId+']').first().clone();
    if (already_have_row.length > 0) {
        $(parent_section).find('.product-assign > tbody').append(already_have_row);
    } else {

        let graphic_id = jQuery('.inside #acf-field_5d0214dd8f9ed .values li span').map(function (index, elem) {
            return jQuery(elem).attr('data-id');
        });
        graphic_id = jQuery.makeArray(graphic_id);

        let data = {
            'action'            : 'add_new_row',
            'product_id'        :  addedId,
            'graphic_id'        :  graphic_id,
            'kit'               :  0,

        };
        jQuery.post(ajaxurl, data, function(response) {
            $(parent_section).find('.product-assign > tbody').append(response);
        });
    }

});
jQuery('.inside').on('click', '.btn-remove-product', (function(){

    //update tables
    $(this).parent().parent().remove();

}));
//add and remove all product button
jQuery('.inside').on('click', '.btn-add-all-product', function(){
    let parent_section = $(this).closest('.assign-products-meta-box');
    let parent_section_id = $(this).closest('.assign-products-meta-box').attr('id');

    $(this).parent().parent().parent().siblings().find('tr:not(.disable-row)').each(function () {
        let addedId = $(this).attr('data-id');

        let already_have_row = $(parent_section).find('.product-assign tr[data-id='+addedId+']').first().clone();
        if (already_have_row.length > 0) {
            $(parent_section).find('.product-assign > tbody').append(already_have_row);
            updateMeta(parent_section_id);
        } else {
            let data = {
                'action'            : 'add_new_row',
                'product_id'        :  addedId,
            };
            jQuery.post(ajaxurl, data, function(response) {
                $(parent_section).find('.product-assign > tbody').append(response);
                updateMeta(parent_section_id);
            });
        }
    });

});
jQuery('.inside').on('click', '.btn-remove-all-product', function(){
    let parent_section_id = $(this).closest('.assign-products-meta-box').attr('id');
    $(this).parent().parent().parent().siblings().find('tr').each(function () {
        $(this).remove();
    });
    updateMeta(parent_section_id);
});
//add    graphics to product option list
jQuery('.inside #acf-field_5d0214dd8f9ed').on('click', '.choices li span', function(){
    var graphic_id = jQuery(this).attr('data-id');
    var graphic_name = jQuery(this).clone().children().remove().end().text();

    jQuery('table.product-assign > tbody > tr').each(function () {
        let product_id = jQuery(this).attr('data-id');
        let html = '<tr data-option="graphic-' + graphic_id + '" class="variation-row">' +
            '           <td><input type="checkbox" class="input-checkbox" name="product_option[0]['+ product_id +'][graphics][]" value="' + graphic_id + '"></td>' +
            '           <td>' + graphic_name + '</td>' +
            '       </tr>';
        jQuery(this).find('.graphic-list tbody').append(html);
    });
});
//remove graphics to product option list
jQuery('.inside #acf-field_5d0214dd8f9ed').on('click', '.values li a[data-name="remove_item"]', function(){
    let graphic_id = jQuery(this).parent('span').attr('data-id');
    jQuery('table.product-assign .column-option').find('tr[data-option="graphic-'+ graphic_id +'"]').remove();
});

//update graphic fields
jQuery('.inside').on('change', '#acf-field_5c87686374a66', function(){
    if (this.request) {
        // if a recent request has been made abort it
        this.request.abort();
    }
    let customer_id = jQuery(this).val();

    let data = {
        action: 'get_data_for_graphic_field',
        customer_id: customer_id,
    };

    jQuery.post(ajaxurl, data, function (response) {
        jQuery('#acf-field_5d0214dd8f9ed').find('ul.choices-list').html(response);
    });

    jQuery('#acf-field_5d0214dd8f9ed').find('ul.values-list li').each(function () {
        let graphic_id = jQuery(this).children('span').attr('data-id');
        jQuery('table.product-assign .column-option').find('tr[data-option="graphic-'+ graphic_id +'"]').remove();
        jQuery(this).remove();
    })

});

//update customer active campaign
jQuery('.inside').on('change', '#acf-field_5c87686374a66', function(){
    let customer = jQuery(this).val();
    let campaign = jQuery('#post_ID').val();

    let data = {
        'action'            : 'is_active_campaign',
        'customer'          :  customer,
        'campaign'          :  campaign,
    };

    jQuery.post(ajaxurl, data, function(responsive) {
        jQuery('.active-project').html(responsive);
    });

});
//synchronized the same input - variations
jQuery('.inside').on('change', '.column-name input', function(){
    $(this);

    let checked         = $(this).prop("checked");
    let changedId       = $(this).closest('tr').attr('data-id');
    let dataOption      = $(this).attr('data-variation');

    jQuery(this).closest('table').find('tr[data-id='+ changedId +']').each(function (index, elem) {
        jQuery(this).find('.column-name  *[data-variation='+ dataOption +']').prop("checked", checked);
    })

});

//synchronized the same input - options
jQuery('.inside').on('change', '.column-option input', function(){
    let checked         = $(this).prop("checked");
    let changedId       = $(this).closest('tr[data-id]').attr('data-id');
    let dataOption      = $(this).closest('tr[data-option]').attr('data-option');

    jQuery(this).closest('table').find('tr[data-id='+ changedId +']').each(function (index, elem) {
        jQuery(this).find('.column-option  *[data-option='+ dataOption +'] input').prop("checked", checked);
    })

});

//fill input for save meta-box data
jQuery('.inside').on('change', '.column-price input', function(){
    $(this).priceField();
});

//ajax filter
jQuery('.inside').on('click','.product-filter', function(){
    let category        = $(this).siblings('select[name="category"]').val();
    let product_type    = $(this).siblings('select[name="product_type"]').val();
    let stock_status    = $(this).siblings('select[name="stock_status"]').val();
    let assign = [];
        $(this).closest('.assign-products-meta-box').find('.add_product').each(function () {
            assign.push($(this).val());
        });
        console.log(assign);
    let thisis          = $(this);

    if ( (category == '') & (product_type == '') & (stock_status == '') ) {
        $(thisis).closest('.table-wrapper').find(' tbody tr').each( function () {
            $(this).removeClass('hidden');
        });
        return;
    }

    let data = {
        'action'            : 'filter_product',
        'category'          :  category,
        'product_type'      :  product_type,
        'stock_status'      :  stock_status,
        'table-assign'      :  thisis.attr('table-assign'),
        'assign'            :  assign,
    };
    jQuery.post(ajaxurl, data, function(search_array) {

        search_array = $.parseJSON(search_array);

        $(thisis).closest('.table-wrapper').find('tbody tr').each( function () {
            let row = $(this);
            let row_id = $(this).attr('data-id');
            $(row).addClass('hidden');

            $(search_array).each(function (index, elem) {

                if (elem*1 == row_id) {
                    $(row).removeClass('hidden');
                    return false;
                }
            })
        })
    });
});

//ajax search
jQuery('.inside').on('click','.button-search', function(){
    let searchText        = $(this).siblings('.search-input').val();
    let assign = $(this).closest('.assign-products-meta-box').find('.add_product').val();
    let thisis = $(this);

    if (searchText == '') {
        $(thisis).closest('.table-wrapper').find(' tbody tr').each( function () {
            $(this).removeClass('hidden');
        });
        return;
    }
    let data = {
        'action'            : 'search_product',
        'searchText'        :  searchText,
        'table-assign'      :  thisis.attr('table-assign'),
        'assign'            :  assign,
    };

    jQuery.post(ajaxurl, data, function(search_array) {

        search_array = $.parseJSON(search_array);
        $(thisis).closest('.table-wrapper').find('tbody tr').each( function () {
            let row = $(this);
            let row_id = $(this).attr('data-id');
            $(row).addClass('hidden');

            $(search_array).each(function (index, elem) {

                if (elem*1 == row_id) {
                    $(row).removeClass('hidden');
                    return false;
                }
            })
        })
    });
});
//show variation button
jQuery('.inside').on('click', '.show-product-variation', (function(){
    jQuery(this).closest('tr').find('.column-name .variation-list').toggle();
}));
jQuery('.inside').on('click', '.show-product-graphics', (function(){
    jQuery(this).closest('tr').find('.column-option .graphic-list').toggle();
}));

function updateMeta(parent_section_id) {
    let array_id = [];

    jQuery('#' + parent_section_id +' .product-assign-wrapper .choices-list tr').each( function (){
        let item = $(this).attr("data-id");

        array_id.push(item);
    });

    jQuery('#' + parent_section_id +' .add_product').val(JSON.stringify(array_id));

}

// validate price
$.fn.getCaret = function() { // adapted from http://blog.vishalon.net/index.php/javascript-getting-and-setting-caret-position-in-textarea
    var ctrl = this[0];
    var CaretPos = 0;	// IE Support
    if (document.selection) {
        ctrl.focus();
        var Sel = document.selection.createRange();
        Sel.moveStart('character', -ctrl.value.length);
        CaretPos = Sel.text.length;
    } else if (ctrl.selectionStart || ctrl.selectionStart == '0') { // Firefox support
        CaretPos = ctrl.selectionStart;
    }
    return (CaretPos);
};
$.fn.priceField = function() {
    $(this).keydown(function(e){
        var val = $(this).val();
        var code = (e.keyCode ? e.keyCode : e.which);
        var nums = ((code >= 96) && (code <= 105)) || ((code >= 48) && (code <= 57)); //keypad || regular
        var backspace = (code == 8);
        var specialkey = (e.metaKey || e.altKey || e.shiftKey);
        var arrowkey = ((code >= 37) && (code <= 40));
        var Fkey = ((code >= 112) && (code <= 123));
        var decimal = ((code == 110 || code == 190) && val.indexOf('.') == -1);

        // UGLY!!
        var misckey = (code == 9) || (code == 144) || (code == 145) || (code == 45) || (code == 46) || (code == 33) || (code == 34) || (code == 35) || (code == 36) || (code == 19) || (code == 20) || (code == 92) || (code == 93) || (code == 27);

        var properKey = (nums || decimal || backspace || specialkey || arrowkey || Fkey || misckey);
        var properFormatting = backspace || specialkey || arrowkey || Fkey || misckey || ((val.indexOf('.') == -1) || (val.length - val.indexOf('.') < 3) || ($(this).getCaret() < val.length - 2));

        if(!(properKey && properFormatting)) {
            return false;
        }
    });

    $(this).blur(function(){
        var val = $(this).val();
        if(val === '') {
            $(this).val('0');
        } else if(val.indexOf('.') == -1) {
            $(this).val(val + '');
        } else if(val.length - val.indexOf('.') == 1) {
            $(this).val(val + '00');
        } else if(val.length - val.indexOf('.') == 2) {
            $(this).val(val + '0');
        }
    });

    return $(this);
};




