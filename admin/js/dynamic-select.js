
    jQuery(document).ready(function($){
        if (typeof acf == 'undefined') { return; }

        // get post/user id
        screen = jQuery('#_acf_screen').val();

        if (screen == 'user') {

            let user_id     = jQuery('#_acf_post_id').val().replace('user_', '');
            let customer    = jQuery('#acf-field_5c875d16244f5').val();

            fill_branch_for_current_customer (user_id, customer);
            fill_kit_for_current_customer (user_id, customer);
            update_PCN_for_current_customer();

            $(document).on('change', '[data-key="field_5c875d16244f5"] .acf-input select', function(e) {
                update_branch_for_current_customer(e, $);
                update_kit_for_current_customer(e, $);
                update_PCN_for_current_customer();
            });
            $('[data-key="field_5c875d16244f5"] .acf-input select').trigger('ready');

        }

        if (screen == 'post') {

            let post_id = jQuery('#_acf_post_id').val();
            let post_type = jQuery('#post_type').val();

            if (post_type == 'customers') {

                $('[data-key=fieldUpdate] .acf-input select').trigger('ready');

            }
            if (post_type == 'campaign') {
                // update data for campaign and project fields
                let affecting_value = jQuery('#acf-field_5c8778d42c013').val();
                let depending_key = 'kit_customer';
                let updateTarget = jQuery('#choose_kit select');
                let alreadyChoosen = jQuery('#kits').val();

                get_data_for_select (post_id, affecting_value, depending_key, updateTarget, alreadyChoosen);

                $(document).on('change', '[data-key="field_5c8778d42c013"] .acf-input select', function(e) {
                    affecting_value = jQuery('#acf-field_5c8778d42c013').val();
                    let alreadyChoosen = jQuery('#kits').val();

                    get_data_for_select(post_id, affecting_value, depending_key, updateTarget, alreadyChoosen);
                });
                // update kit option
                $(document).on('click', '#add-kit', function(e) {
                    affecting_value = jQuery('#acf-field_5c8778d42c013').val();
                    let alreadyChoosen = jQuery('#kits').val();
                    get_data_for_select(post_id, affecting_value, depending_key, updateTarget, alreadyChoosen);
                });
                $(document).on('click', 'a.button.delete-kit', function(e) {
                    affecting_value = jQuery('#acf-field_5c8778d42c013').val();
                    let alreadyChoosen = jQuery('#kits').val();
                    get_data_for_select(post_id, affecting_value, depending_key, updateTarget, alreadyChoosen);
                });
                $('[data-key="field_5c8778d42c013"] .acf-input select').trigger('ready');
            }

        }


    });

    //function for users select
    function get_data_for_select(post_id, affecting_value, depending_key, updateTarget, alreadyChoosen) {
        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }
        let select = jQuery(updateTarget);
        select.empty();
        let arrayChoosen = jQuery.parseJSON(alreadyChoosen);

        let data = {
            action: 'get_data_for_select',
            post_id: post_id,
            affecting_value: affecting_value,
            depending_key: depending_key,
        };

        jQuery.post(ajaxurl, data, function (response) {

            var options = jQuery.parseJSON(response);

            if (options.length > 0) {

                for (i = 0; i < options.length; i++) {

                    //delete already added
                    if (spliceTrigger(arrayChoosen, options[i]['value']) ) continue;

                    if (options[i]['selected']) {
                        var customer_data_item = '<option selected="selected" value="' + options[i]['value'] + '">' + options[i]['label'] + '</option>';
                    } else {
                        var customer_data_item = '<option value="' + options[i]['value'] + '">' + options[i]['label'] + '</option>';
                    }

                    jQuery(updateTarget).removeAttr('disabled').append(customer_data_item);
                }
            } else {
                var customer_data_item = '<option value="">No value</option>';

                jQuery(updateTarget).append(customer_data_item).attr('disabled', 'disabled');
            }
        });
    }
    function spliceTrigger(arrayChoosen, item) {
        let bool = false;
        $(arrayChoosen).each(function (index, elem) {
            if (elem==item) {
                bool = true;
            }
        });
        if (bool != true) {
            return false;
        }
        return true;

    }

    function fill_branch_for_current_customer(user_id, customer, $) {
        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }

        if (!customer) {
            return;
        }
        var data = {
            action: 'fill_select_for_current_customer',
            user_id: user_id,
            user_data: 'user_branch',
            customer:customer,
            name: 'branch_name',
            data_name: 'branch_customer',
        };
        jQuery.post(ajaxurl, data, function(response) {

            let customer_data = jQuery.parseJSON(response);

            if (customer_data) {

                for(i=0; i<customer_data.length; i++) {

                    if (customer_data[i]['selected']) {
                        var customer_data_item = '<option selected="selected" value="'+customer_data[i]['value']+'">'+customer_data[i]['label']+'</option>';
                    } else {
                        var customer_data_item = '<option value="'+customer_data[i]['value']+'">'+customer_data[i]['label']+'</option>';
                    }

                    jQuery('#acf-field_5c875d48244f6').removeAttr('disabled').append(customer_data_item);
                }

            }

        });
    }
    function fill_kit_for_current_customer(user_id, customer, $) {
        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }
        if (!customer) {
            return;
        }
        var data = {
            action: 'fill_select_for_current_customer',
            user_id: user_id,
            user_data: 'user_kit',
            customer:customer,
            name: 'department_name',
            data_name: 'kit_customer',
        }

        jQuery.post(ajaxurl, data, function(response) {

            let customer_data = jQuery.parseJSON(response);

            if (customer_data) {

                for(i=0; i<customer_data.length; i++) {

                    if (customer_data[i]['selected']) {
                        var customer_data_item = '<option selected="selected" value="'+customer_data[i]['value']+'">'+customer_data[i]['label']+'</option>';
                    } else {
                        var customer_data_item = '<option value="'+customer_data[i]['value']+'">'+customer_data[i]['label']+'</option>';
                    }

                    jQuery('#acf-field_5c875d63244f7').removeAttr('disabled').append(customer_data_item);
                }

            } else {
                jQuery('#acf-field_5c875d63244f7').attr('disabled', 'disabled');
            }
        });
    }

    function update_branch_for_current_customer(e, $) {
        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }

        var branch_select = $('[data-key="field_5c875d48244f6"] select');
        branch_select.empty();

        var target = $(e.target);
        var customer = target.val();

        branch_select.removeAttr('disabled').append('<option>- Select -</option>');

        if (!customer) {
            return;
        }

        // set and prepare data for ajax
        var data = {
            action: 'load_branch_for_current_customer',
            customer: customer
        }

        // call the acf function that will fill in other values
        // like post_id and the acf nonce
        data = acf.prepareForAjax(data);

        // make ajax request
        // instead of going through the acf.ajax object to make requests like in <5.7
        // we need to do a lot of the work ourselves, but other than the method that's called
        // this has not changed much
        jQuery.post(ajaxurl, data, function(response) {
            let branch = $.parseJSON(response);

            if (branch) {

                for(i=0; i<branch.length; i++) {
                    var branch_item = '<option value="'+branch[i]['value']+'">'+branch[i]['label']+'</option>';
                    branch_select.removeAttr('disabled').append(branch_item);
                }

            } else {
                branch_select.attr('disabled', 'disabled');
            }
        });


    }
    function update_PCN_for_current_customer() {
        if (!jQuery('input').is('#priority_customer_number')) {
            console.log('Priority customer number not found. Install Priority API');
            return true;
        }

        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }

        let customer    = jQuery('#acf-field_5c875d16244f5').val();

        if (!customer) {
            return;
        }

        // set and prepare data for ajax
        var data = {
            action: 'load_PCN_for_current_customer',
            customer: customer
        }
        // call the acf function that will fill in other values
        // like post_id and the acf nonce
        data = acf.prepareForAjax(data);

        // make ajax request
        // instead of going through the acf.ajax object to make requests like in <5.7
        // we need to do a lot of the work ourselves, but other than the method that's called
        // this has not changed much
        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#priority_customer_number').val(response);
        });


    }
    function update_kit_for_current_customer(e, $) {
        if (this.request) {
            // if a recent request has been made abort it
            this.request.abort();
        }

        var kit_select = $('[data-key="field_5c875d63244f7"] select');
        kit_select.empty();

        var target = $(e.target);
        var customer = target.val();
        kit_select.removeAttr('disabled').append('<option>- Select -</option>');

        if (!customer) {
            return;
        }

        // set and prepare data for ajax
        var data = {
            action: 'load_kit_for_current_customer',
            customer: customer
        }
        // call the acf function that will fill in other values
        // like post_id and the acf nonce
        data = acf.prepareForAjax(data);

        // make ajax request
        // instead of going through the acf.ajax object to make requests like in <5.7
        // we need to do a lot of the work ourselves, but other than the method that's called
        // this has not changed much
        jQuery.post(ajaxurl, data, function(response) {
            let kit = $.parseJSON(response);
            if (kit) {

                for(i=0; i<kit.length; i++) {
                    var department_item = '<option value="'+kit[i]['value']+'">'+kit[i]['label']+'</option>';
                    kit_select.removeAttr('disabled').append(department_item);
                }

            } else {
                kit_select.attr('disabled', 'disabled');
            }
        });


    }

