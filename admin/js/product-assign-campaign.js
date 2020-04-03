jQuery(document).ready(function ($) {
    //on load
    jQuery('.inside').find('.tab-pane').each(function () {
        checkAllInput(jQuery(this));
    });

    //tab
    jQuery('.inside').on('click', 'li.button-tab', function () {
        let tab = jQuery(this).data('tab');
        jQuery(this).closest('.variation-list').find('.tab-content .tab-pane').removeClass('show-tab');
        jQuery(this).closest('.variation-list').find('.' + tab).addClass('show-tab');
        jQuery(this).siblings().removeClass('active');
        jQuery(this).addClass('active');
    });

    jQuery('.inside').on('click', 'input.tab_checked', function () {
        let tab = jQuery(this).data('checked');
        let status = jQuery(this).prop("checked");
        let product_id = jQuery(this).closest('tr').data('id');
        jQuery(this).closest('table.product-assign').find('.choices-list tr[data-id=' + product_id + ']').each(function () {
            jQuery(this).find('input.tab_checked[data-checked=' + tab + ']').prop("checked", status);
            jQuery(this).find('.' + tab + ' input').each(function () {
                jQuery(this).prop("checked", status);
            })
        })

    });
    jQuery('.inside').on('click', '.tab-content input', function () {
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
jQuery('.inside').on('click', '.btn-add-product', function () {

    //save meta in input
    let addedId = $(this).attr('data-id');

    let parent_section = $(this).closest('.assign-products-meta-box');

    $(this).remove();

    let array_id = $(parent_section).find('.add_product').eq(0).val();

    if (!array_id) {
        array_id = '[]';
    }

    array_id = JSON.parse(array_id);

    array_id.push(addedId);

    $(parent_section).find('.add_product').val(JSON.stringify(array_id));

    let kit = jQuery(parent_section).attr('data-kit');
    let assigning_id = jQuery(parent_section).find('.kit-groups .column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).attr('data-group-id');
    });
    let assigning_names = jQuery(parent_section).find('.kit-groups .column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).val();
    });

    let required_id = jQuery(parent_section).find('.required-products .column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).attr('data-group-id');
    });
    let required_names = jQuery(parent_section).find('.required-products .column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).val();
    });

    let campaign = jQuery('#post_ID').val();

    assigning_id = jQuery.makeArray(assigning_id);
    assigning_names = jQuery.makeArray(assigning_names);

    required_id = jQuery.makeArray(required_id);
    required_names = jQuery.makeArray(required_names);

    let data = {
        'action': 'add_new_row',
        'kit': kit,
        'campaign': campaign,
        'product_id': addedId,
        'assigning_id': assigning_id,
        'assigning_names': assigning_names,
        'required_id': required_id,
        'required_names': required_names,
    };

    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.post(ajaxurl, data, function (response) {
        jQuery(parent_section).find('.product-assign > tbody').append(response);

        jQuery(document.body).css({ 'cursor': 'default' });
        paginationAssignProduct();
    });

});
jQuery('.inside').on('click', '.btn-remove-product', (function () {

    //save meta in input
    let addedId = $(this).attr('data-id');
    let parent_section = $(this).closest('.assign-products-meta-box');

    let array_id = $(parent_section).find('.add_product').val();

    array_id = JSON.parse(array_id);

    let index = jQuery.inArray(addedId, array_id);

    array_id.splice(index, 1);

    $(parent_section).find('.add_product').val(JSON.stringify(array_id));
    $(parent_section).find('.product-all tr[data-id="' + addedId + '"] .column-button').html('<a class="btn-add-product btn-simple" data-id="' + addedId + '">Add</a>');

    $(this).parent().parent().remove();

}));

//add and remove all product button
jQuery('.inside').on('click', '.btn-add-all-product', function () {
    let parent_section = $(this).closest('.assign-products-meta-box');
    let parent_section_id = $(parent_section).attr('id');
    let kit = $(parent_section).attr('data-kit');

    let assigning_id = jQuery(parent_section).find('.column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).attr('data-group-id');
    });
    let assigning_names = jQuery(parent_section).find('.column-group-name input').map(function (index, elem) {
        if (jQuery(elem).val() == '') return null;
        return jQuery(elem).val();
    });

    assigning_id = jQuery.makeArray(assigning_id);
    assigning_names = jQuery.makeArray(assigning_names);

    $(this).closest('table').find('tbody tr .btn-add-product').each(function () {
        let addedId = $(this).attr('data-id');
        $(this).remove();

        let data = {
            'action': 'add_new_row',
            'kit': kit,
            'product_id': addedId,
            'assigning_id': assigning_id,
            'assigning_names': assigning_names,
        };

        jQuery(document.body).css({ 'cursor': 'wait' });

        jQuery.post(ajaxurl, data, function (response) {
            $(parent_section).find('.product-assign > tbody').append(response);
            updateMeta(parent_section_id);
            jQuery(document.body).css({ 'cursor': 'default' });
        });
    });

});
jQuery('.inside').on('click', '.btn-remove-all-product', function () {
    let parent_section_id = $(this).closest('.assign-products-meta-box').attr('id');
    $(this).closest('table').find('tbody tr').each(function () {
        $(this).remove();
    });
    let parent_section = $(this).closest('.assign-products-meta-box');
    $(parent_section).find('.product-all tr[data-id] ').each(function () {
        let addedId = $(this).data('id');
        $(this).find('.column-button').html('<a class="btn-add-product btn-simple" data-id="' + addedId + '">Add</a>');
    });

    updateMeta(parent_section_id);
});

//update customer active campaign
jQuery('.inside').on('change', '#acf-field_5c8778d42c013', function () {
    let customer = jQuery(this).val();
    let campaign = jQuery('#post_ID').val();

    let data = {
        'action': 'is_active_campaign',
        'customer': customer,
        'campaign': campaign,
    };

    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.post(ajaxurl, data, function (responsive) {
        jQuery('.kit-active-campaign').replaceWith(responsive);
        jQuery(document.body).css({ 'cursor': 'default' });
    });

});
//synchronized the same input - variations
jQuery('.inside').on('change', '.column-name input', function () {

    let checked = $(this).prop("checked");
    let changedId = $(this).closest('tr').attr('data-id');
    let dataOption = $(this).attr('data-variation');

    jQuery(this).closest('table').find('tr[data-id=' + changedId + ']').each(function (index, elem) {
        jQuery(this).find('.column-name  *[data-variation=' + dataOption + ']').prop("checked", checked);
    })

});
//synchronized the same input - price
jQuery('.inside').on('change', '.column-price input', function () {
    $(this).priceField();

    let changedId = $(this).parent().parent().attr('data-id');
    let changedPrice = $(this).val();

    jQuery('tr[data-id=' + changedId + '] .column-price input').val(changedPrice);

});
//synchronized the same input - option
jQuery('.inside').on('change', '.column-option > *[data-option]', function () {

    let changedId = $(this).parent().parent().attr('data-id');
    let changedOption = $(this).val();
    let dataOption = $(this).attr('data-option');

    jQuery(this).closest('table').find('tr[data-id=' + changedId + '] .column-option > *[data-option=' + dataOption + ']').val(changedOption);

});

//ajax filter
jQuery('.inside').on('click', '.product-filter', function () {
    let category = $(this).siblings('select[name="category"]').val();
    let product_type = $(this).siblings('select[name="product_type"]').val();
    let stock_status = $(this).siblings('select[name="stock_status"]').val();
    let assign = $(this).closest('.assign-products-meta-box').find('.add_product').val();
    let thisis = $(this);

    if ((category == '') & (product_type == '') & (stock_status == '')) {
        $(thisis).closest('.table-wrapper').find(' tbody tr').each(function () {
            $(this).removeClass('hidden');
        });
        return;
    }

    let data = {
        'action': 'filter_product',
        'category': category,
        'product_type': product_type,
        'stock_status': stock_status,
        'table-assign': thisis.attr('table-assign'),
        'assign': assign,
    };
    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.post(ajaxurl, data, function (search_array) {

        search_array = $.parseJSON(search_array);

        $(thisis).closest('.table-wrapper').find('tbody tr').each(function () {
            let row = $(this);
            let row_id = $(this).attr('data-id');
            $(row).addClass('hidden');

            $(search_array).each(function (index, elem) {

                if (elem * 1 == row_id) {
                    $(row).removeClass('hidden');
                    return false;
                }
            })
        })
        jQuery(document.body).css({ 'cursor': 'default' });
    });
});

//ajax search
jQuery('.inside').on('click', '.button-search', function () {
    let searchText = $(this).siblings('.search-input').val();
    let assign = $(this).closest('.assign-products-meta-box').find('.add_product').val();
    let thisis = $(this);

    if (searchText == '') {
        $(thisis).closest('.table-wrapper').find(' tbody tr').each(function () {
            $(this).removeClass('hidden');
        });
        return;
    }
    let data = {
        'action': 'search_product',
        'searchText': searchText,
        'table-assign': thisis.attr('table-assign'),
        'assign': assign,
    };

    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.post(ajaxurl, data, function (search_array) {

        search_array = $.parseJSON(search_array);
        $(thisis).closest('.table-wrapper').find('tbody tr').each(function () {
            let row = $(this);
            let row_id = $(this).attr('data-id');
            $(row).addClass('hidden');

            $(search_array).each(function (index, elem) {

                if (elem * 1 == row_id) {
                    $(row).removeClass('hidden');
                    return true;
                }
            })
        })
        jQuery(document.body).css({ 'cursor': 'default' });
    });
});

//add kit to campaign
jQuery('#add-kit').on('click', function () {
    let newDepartmentId = jQuery(this).siblings('select').val();
    let dep_name = jQuery(this).parent().find('select option:selected').eq(0).text();

    let thisis = $(this);

    let department_array = jQuery('#kits').val();

    if (!department_array) {
        department_array = '[]';
    }
    department_array = JSON.parse(department_array);
    let number = department_array.length;

    department_array.push(newDepartmentId);
    jQuery('#kits').val(JSON.stringify(department_array));

    let data = {
        'action': 'add_kit',
        'newDepartmentId': newDepartmentId,
        'key': number,
        'post_id': jQuery('#post_ID').val(),
    };
    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.post(ajaxurl, data, function (response) {

        $(thisis).parent().parent().append(response);

        jQuery('section.assign-products-meta-box').each(function () {
            let dep = jQuery(this).data('kit');

            if (dep != newDepartmentId) {
                jQuery(this).find('select.select-kit').append(jQuery("<option></option>", { value: newDepartmentId, text: dep_name }));
            }
        })

        jQuery(document.body).css({ 'cursor': 'default' });

    });
});

//delete kit
jQuery('.inside').on('click', '.delete-kit', function () {
    let deletedId = $(this).closest('.assign-products-meta-box').attr('data-kit');
    $(this).closest('.assign-products-meta-box').remove();

    let department_array = jQuery('#kits').val();
    department_array = JSON.parse(department_array);

    let index = jQuery.inArray(deletedId, department_array);
    department_array.splice(index, 1);

    if (department_array.length != 0) {
        jQuery('#kits').val(JSON.stringify(department_array));
    } else {
        jQuery('#kits').val('');
    }
    jQuery('.select-kit option[value="' + deletedId + '"]').remove();
});

//add group button
jQuery('.inside').on('click', '.btn-add-option', function () {
    let random_ID = Math.random() * 100000000000000000;
    let kit = jQuery(this).closest('section').attr('data-kit');
    let group = jQuery(this).closest('tr').find('input').eq(0);
    let amount = jQuery(this).closest('tr').find('input').eq(1);
    let option = jQuery(this).closest('table').data('option');

    var thisis = jQuery(this);

    jQuery(document.body).css({ 'cursor': 'wait' });

    jQuery.ajax({
        type: "POST",
        url: '/wp-admin/admin-ajax.php',
        data: {
            'action': 'get_option_row',
            'random_id': random_ID,
            'group': jQuery(group).val(),
            'kit': kit,
            'amount': jQuery(amount).val(),
            'option': option,
        },
        success: function (response) {
            var response = jQuery.parseJSON(response);
            if (response.alert) {
                alert(response.alert);
            } else {
                jQuery(thisis).closest('tbody').append(response.row);

                jQuery(thisis).closest('section').find('select[data-option="' + option + '"]').append(jQuery('<option value="' + random_ID + '">' + jQuery(group).val() + '</option>'));

                let row = jQuery(thisis).parent().parent();
                row.find('input').each(function (index, elem) {
                    jQuery(elem).val('');
                });
                jQuery(thisis).closest('tbody').append(row);
            }

            jQuery(document.body).css({ 'cursor': 'default' });
        }
    });



});
//remove group button
jQuery('.inside').on('click', '.btn-remove-option', (function () {

    let del_group = jQuery(this).closest('tr').first('td').find('input').attr('data-group-id');
    jQuery('option[value="' + del_group + '"]').remove();

    jQuery(this).closest('tr').remove();

}));
//show variation button
jQuery('.inside').on('click', '.show-product-variation', (function () {
    jQuery(this).closest('tr').find('.column-name .variation-list').toggle();
}));

//copy
jQuery('.inside').on('click', '.copy-kit', (function () {
    jQuery(document.body).css({ 'cursor': 'wait' });

    let current_kit = jQuery(this).closest('section').data('kit');
    let copy_from_kit = jQuery(this).siblings('select').val();

    if (!copy_from_kit) {
        console.log(copy_from_kit);
        return;
    }

    var form = jQuery('form').serialize();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "html",
        data: {
            action: 'copy_kit',
            form: form,
            donor: copy_from_kit,
            recipient: current_kit,
        },
        success: function (response) {
            location.reload();
        },
    });
}));
//check string on empty
function isEmpty(str) {
    if (str.trim() == '')
        return true;

    return false;
}
function updateMeta(parent_section_id) {
    let array_id = [];

    jQuery('#' + parent_section_id + ' .product-assign-wrapper .choices-list tr').each(function () {
        let item = $(this).attr("data-id");

        array_id.push(item);
    });

    jQuery('#' + parent_section_id + ' .add_product').val(JSON.stringify(array_id));

}


// validate price
$.fn.getCaret = function () { // adapted from http://blog.vishalon.net/index.php/javascript-getting-and-setting-caret-position-in-textarea
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
$.fn.priceField = function () {
    $(this).keydown(function (e) {
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

        if (!(properKey && properFormatting)) {
            return false;
        }
    });

    $(this).blur(function () {
        var val = $(this).val();
        if (val === '') {
            $(this).val('0');
        } else if (val.indexOf('.') == -1) {
            $(this).val(val + '');
        } else if (val.length - val.indexOf('.') == 1) {
            $(this).val(val + '00');
        } else if (val.length - val.indexOf('.') == 2) {
            $(this).val(val + '0');
        }
    });

    return $(this);
};
