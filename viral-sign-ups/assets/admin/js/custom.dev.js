jQuery(document).ready(function(){

    var $admin_wrap = jQuery('.vsu-admin-wrap'),
        $loader = jQuery('<div class="vsu-admin-loader"/>').hide()
                .appendTo($admin_wrap)
                .text('saving...')
                .wrapInner('<div class="vsu-admin-loader-text"/>'),
        nav_position = jQuery('.vsu-admin-position', $admin_wrap).val(),
        signup_page_loaded = false;
        
    function show_loader(text){
        text = text || 'saving...';
        $loader.find('.vsu-admin-loader-text').text(text);
        $loader.fadeIn(300);
    }
    
    function hide_loader(){
        $loader.hide();
    }
    
    function update_export_button(){
        (function($){
            var $export_button = $('[data-export-base]', $admin_wrap),
                base = $export_button.data('exportBase'),
                api_key = $('#vsu_admin-settings-license_key', $admin_wrap).val(),
                filter_action = $('[name="vsu-filter[action]"]', $admin_wrap).val(),
                filter_number = $('[name="vsu-filter[number]"]', $admin_wrap).val();
                
            var new_url = base + '&' + decodeURIComponent( $.param({
                key: api_key,
                filter_action: filter_action,
                filter_number: filter_number
            }));
            
            $export_button.attr('href', new_url);
        }(jQuery));
    }
    
    // ----- Verify Fields ----- //
    (function($){
        'use strict';
        $('.vsu-admin-field-verify', $admin_wrap).after('<div class="vsu-admin-status"/>');
    }(jQuery));
    // ----- /Verify Fields ----- //
    
    // ----- See Variables ----- //
    (function($){
        'use strict';
        $('.vsu-admin-see-variables a', $admin_wrap).on('click', function(e){
            e.preventDefault();
            $(this).next('table').slideToggle('fast');
        });
    }(jQuery));
    // ----- /See Variables ----- //
    
    // ----- ColorPicker Fields ----- //
    (function($){
        'use strict';
        $('.vsu-admin-color-picker', $admin_wrap).wpColorPicker();
    }(jQuery));
    // ----- /ColorPicker Fields ----- //
    
    // ----- Copy Fields ----- //
    (function($){
        'use strict';
        $('.vsu-admin-input-copy', $admin_wrap).on('click', function(){
            this.select();
        });
    }(jQuery));
    // ----- /Copy Fields ----- //
    
    // ----- Navigation ----- //
    function nav_next_step(){
        'use strict';
        jQuery('.vsu-admin-navigation li.vsu-admin-active', $admin_wrap )
                .next().find('a').trigger('click');
        
    }
    (function($){
        'use strict';
        $('.vsu-admin-navigation li:not(.vsu-admin-active) > a[href^="#"]', $admin_wrap).on('click', function(e){
            e.preventDefault();
            var $nav_item = $(this),
                $target = $($nav_item.attr('href'));
                
            $('.vsu-admin-navigation li.vsu-admin-active', $admin_wrap).removeClass('vsu-admin-active');
            $('.vsu-admin-section', $admin_wrap).hide();
            $nav_item.parent().addClass('vsu-admin-active');
            $target.fadeIn(300, function(){
                $target.trigger('vsu.after_show');
            });
            nav_position = $nav_item.parent().index();
        });
        $('.vsu-admin-navigation li:eq('+nav_position+') > a', $admin_wrap).trigger('click');
    }(jQuery));
    // ----- /Navigation ----- //
   
    // ----- Settings Form ----- //
    (function($){
        'use strict';
        
        function set_account_data( data ) {
            $( '#vsu_admin-settings-first_name', $admin_wrap )
                .val( data['first_name'] );
            $( '#vsu_admin-settings-last_name', $admin_wrap )
                    .val( data['last_name'] );
            $( '#vsu_admin-settings-domain', $admin_wrap )
                    .val( data['domain'] );
            $( '#vsu_admin-settings-email', $admin_wrap )
                    .val( data['email'] );
        }
        
        function empty_account_data(){
            $( '#vsu-admin-form-settings-account', $admin_wrap ).trigger('reset');
        }
        
        // license form
        var $license_key_field = $('#vsu-admin-form-settings-license #vsu_admin-settings-license_key');
        $('#vsu-admin-form-settings-license', $admin_wrap).on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            show_loader();
            
            $.post(ajaxurl, {
                action: 'vsu_save_license',
                data: $form.serialize(),
                position: nav_position
            }, function(response){
                if(response.state === 'error'){
                    alert(response.message);
                }
                if(response.state === 'success'){
                    signup_page_loaded = false;
                    if($license_key_field.length > 0){
                        if($license_key_field.val() === ''){
                            $license_key_field.removeAttr('data-vsu-admin-state');
                        }
                        else{
                            if( response.data['api_verified'] === 0 ) {
                                var state = 'limit_reached';
                            }
                            else{
                                var state = response.data['api_verified'] ? 'verified' : 'unverified';
                            }
                            $license_key_field.attr('data-vsu-admin-state', state);
                            if( state === 'verified' ) {
                                nav_next_step();
                                update_export_button();
                            }
                        }
                    } // license field exists and has a value
                    
                    if( response.data['account_details'] === false ) {
                        empty_account_data();
                    }
                    else{
                        set_account_data( response.data['account_details'] );
                    }
                }
                hide_loader();
            }, 'json');
        });
            
            // account form
        $('#vsu-admin-form-settings-account', $admin_wrap).on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            show_loader();

            $.post(ajaxurl, {
                    action: 'vsu_save_account',
                    data: $form.serialize(),
                    position: nav_position
                }, function(response){
                    if(response.state === 'error'){
                        alert(response.message);
                    }
                    if(response.state === 'success'){
                        if( response.data !== false ) {
                            var data = response.data;
                            if( 'error' in data ) {
                                alert( data[ 'error' ] );
                            }
                            else{
                                if( data['api_key'] ) {
                                    $license_key_field.val( data['api_key'] );
                                }
                                $license_key_field.attr('data-vsu-admin-state', data['api_key_status'] );
                            }
                        }
                    }
                    hide_loader();
            }, 'json');
        });
    }(jQuery));
    // ----- /Settings Form ----- //
    
    // ----- Sign Up Form Preview ----- //
    (function($){
        'use strict';
        function antispam_activate( $form ) {
            var $antispam_field = $('<p class="vsu-antispam-field"/>'),
                $label = $('<label>').text(VSU_Admin.antispam).appendTo($antispam_field),
                $checkbox = $('<input/>', {
                    name: 'vsu_antispam',
                    'class': 'vsu-antispam-check',
                    type: 'checkbox',
                    value: 'yes'
                }).appendTo($label);
            
            $('<div class="vsu-tickbox"/>').insertAfter($checkbox);
            $form.append($antispam_field);
            return $antispam_field;
        }
        
        var $preview_container = $('#vsu-admin-signup-form-preview'),
            $signup_button = $('.vsu-signup-button', $preview_container),
            $button_color_picker = $('#vsu_admin-email_form-button_color'),
            $button_text_input = $('#vsu_admin-email_form-button_text');
        
        // AntiSpam
        var $antispam_field = antispam_activate($preview_container.find('.vsu-signup-form')),
            $antispam_enable = $( '#vsu_admin-email_form-antispam_enabled');
        
        function show_hide_antispam(){
            $antispam_field.toggle( $antispam_enable.is(':checked') );
        }
        
        $antispam_enable.on('click', function(){
            show_hide_antispam();
        });
        show_hide_antispam();
        
        // Button Color
        if($signup_button.length > 0){
            $button_color_picker.wpColorPicker({
                change: function(event, ui){
                    $signup_button.css('background-color', $button_color_picker.wpColorPicker('color'));
                }
            });
        }
        
        // Button Text
        if($button_text_input.length > 0){
            $button_text_input.on('input change', function(){
                $signup_button.val($button_text_input.val());
            });
        }
        
    }(jQuery));
    // ----- /Sign Up Form Preview ----- //
    
    // ----- AutoResponders Page Preview ----- //
    (function($){
        'use strict';
        var $full_text_textarea = $('#vsu_admin-autoresponders-signup_full_text');
        if( $full_text_textarea.length > 0 ) {
            var $preview_container = $('#' + $full_text_textarea.data('previewContainer'));
            $full_text_textarea.on('input change', function(){
                $preview_container.text($full_text_textarea.val());
            });
        }
        
    }(jQuery));
    // ----- /AutoResponders Page Page ----- //
    
    // ----- Popup Content Form Save ----- //
    (function($){
        'use strict';
        $('#vsu-admin-form-popup_content', $admin_wrap).on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            show_loader();
            $.post(ajaxurl, {
                action: 'vsu_save_popup_content',
                data: $form.serialize(),
                position: nav_position,
                section_ID: 'popup_content'
            }, function(response){
                if(response.state === 'error'){
                    alert(response.message);
                }
                if(response.state === 'success'){
                    $.each(response.data, function(id, val){
                        var $target = $('[data-vsu-live="'+id+'"]', $admin_wrap );
                       
                        if( val === false ){
                            val = '';
                        }
                        if( $target.length > 0 ) {
                            if( id === 'ref_number' ){
                                val = parseInt(val) || 0;
                            }
                            
                            $target.text(val);
                        
                            if( id === 'promo_page' ){
                                $target.attr('href', id);
                            }
                        }
                    });
                    nav_next_step();
                }
                hide_loader();
            }, 'json');
        });
        
        function update_preview(){
            var $promo_text = $('#vsu_admin-popup_content-promo_text', $admin_wrap),
                $promo_text_trigger = $('[data-vsu-live="promo_text"]', $admin_wrap),
                $refer_number = $('#vsu_admin-popup_content-ref_number', $admin_wrap),
                $refer_number_trigger = $('[data-vsu-live="ref_number"]', $admin_wrap),
                $credits_on = $('#vsu_admin-popup_content-credit_line_on', $admin_wrap),
                $credits_container = $('.vsu-powered-by', $admin_wrap);
            $promo_text.on('change input', function(){
                $promo_text_trigger.text($promo_text.val());
            });
            $refer_number.on('change input', function(){
                $refer_number_trigger.text(parseInt($refer_number.val()) || 0);
            });
            function show_hide_credits(){
                $credits_container.toggle($credits_on.is(':checked'));
            }
            show_hide_credits();
            $credits_on.on('change', function(){
                show_hide_credits();
            });
        };
        
        update_preview();
    }(jQuery));
    // ----- /General Form Save ----- //
    
    // ----- General Form Save ----- //
    (function($){
        'use strict';
        $('.vsu-admin-form[data-vsu-section-id]', $admin_wrap).on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            show_loader();

            $.post(ajaxurl, {
                action: 'vsu_save_general',
                data: $form.serialize(),
                position: nav_position,
                section_ID: $form.data( 'vsuSectionId' )
            }, function(response){
                if(response.state === 'error'){
                    alert(response.message);
                }
                if(response.state === 'success'){
                    nav_next_step();
                }
                hide_loader();
            }, 'json');
        });
    }(jQuery));
    // ----- /General Form Save ----- //
    
    // ----- Signup Page ----- //
    (function($){
        'use strict';
        var $signup_page = $('#vsu-admin-section-signups', $admin_wrap);
        $signup_page.addClass('vsu-state-loading vsu-state-total-loading');
        
        function signups_create_table(){
            var $table = $('<table class="vsu-admin-signups-table"/>');
            $('<th>').text(VSU_Admin.signup_page_email_label).appendTo($table);
            $('<th>').appendTo($table);
            $('<th>').text(VSU_Admin.signup_page_refer_label).appendTo($table);
            $table.wrapInner('<thead/>');
            $('<tbody/>').appendTo($table);
            return $table;
        }
        
        function add_new_row(signup_data){
            if( signup_data === 'no_results' ){
                var $column = $('<td colspan="3"/>').text( VSU_Admin.no_results_text );
                $signups_table.find('tbody').html($column)
                        .wrapInner('<tr class="vsu-admin-no-results-row"/>');
                return;
            }
            $signups_table.find('.vsu-admin-no-results-row').remove();
            var signup_ID = signup_data.id;
            var $row = $('<tr id="vsu-admin-signup-'+signup_ID+'"/>')
                    .data('signupID', signup_ID);
            
            // email
            $('<a/>', {
                'href': 'mailto:' + signup_data.email
            }).text(signup_data.email).appendTo($row).wrap('<td width="35%"/>');
            
            // refer number
            if(parseInt(signup_data.refer_n) > 0){
                $('<span class="vsu-admin-referred-count"/>')
                    .text(signup_data.refer_n).appendTo($row).wrap('<td width="5%"/>');
            }
            else{
                $('<td width="5%"/>').appendTo($row);
            }
                
            // refer address
            var refer_addr = ( signup_data.refer_addr !== '' )
                    ? signup_data.refer_addr : VSU_Admin.signup_page_empty_referrer_site;
            $('<a/>', {
                'href': signup_data.refer_addr
            }).text(refer_addr).appendTo($row).wrap('<td width="60%"/>');
            $row.hide().appendTo($signups_table.find('tbody')).fadeIn(300);
            row_number ++;
        }
        
        var $signups_table = signups_create_table();   
        $signups_table.insertAfter('.vsu-admin-signups-header', $signup_page);
        
        var $load_more_button = $('.vsu-admin-load-more-button', $signup_page),
            default_load_button_text = $load_more_button.val(),
            $total_signups_number = $('.vsu-admin-total-signups-number', $signup_page),
            $total_shared_number = $('.vsu-admin-total-shared-number', $signup_page),
            $filter_form = $('#vsu-admin-signup-filter-form', $signup_page),
            row_number = 0;
        
        $signup_page
            .on('vsu.after_show', function(){
                if( ! signup_page_loaded ) {
                    $signup_page.trigger('vsu.load_signup_data');
                }
            })
            .on('vsu.load_signup_data', function( e, block_update_total ){
                block_update_total = block_update_total || false;
                var page = $load_more_button.data('vsuPage') || 1,
                    get_total = ! signup_page_loaded && ! block_update_total;
                    
                if( ! signup_page_loaded ) {
                    $signups_table.find('tbody').empty();
                    $signup_page.addClass('vsu-state-loading');
                    row_number = 0;
                }
                
                if( get_total ) {
                    $signup_page.addClass('vsu-state-total-loading');
                }

                $load_more_button.attr('disabled', 'disabled');
                $.post(ajaxurl, {
                    action: 'vsu_get_signups',
                    page: page,
                    get_total: get_total,
                    filter_action: $filter_form.find('[name="vsu-filter[action]"]').val(),
                    filter_number: $filter_form.find('[name="vsu-filter[number]"]').val()
                }, function(response){
                    if( response.state === 'error' ){
                        $signup_page.removeClass('vsu-state-loading').removeClass('vsu-state-total-loading');
                        $load_more_button.hide();
                        alert(response.message);
                        return;
                    }
                    if('total' in response.data){
                        $total_signups_number.text(response.data.total.signups);
                        $total_shared_number.text(response.data.total.refers);
                    }
                    $load_more_button.data('vsuPage', ++ page );
                    $signup_page.removeClass('vsu-state-loading').removeClass('vsu-state-total-loading');
                    
                    if( ! $.isEmptyObject(response.data.data) ){
                        $.each(response.data.data, function(i, signup_data){
                            add_new_row(signup_data);
                        });
                    } // append new data
                    else{
                        add_new_row( 'no_results' );
                    }
                    
                    $load_more_button.toggle(response.data.load_more);
                    
                    $load_more_button.val(default_load_button_text).removeAttr('disabled');
                    $filter_form.find(':input').removeAttr('disabled');
                    signup_page_loaded = true;
                    hide_loader();
                }, 'json');
            })
            .on('click', '.vsu-admin-load-more-button', function(e){
                e.preventDefault();
                $load_more_button.val(VSU_Admin.loading_text);
                $signup_page.trigger('vsu.load_signup_data');
            });
            $filter_form.on('submit', function(e){
                e.preventDefault();
                $filter_form.find(':input').attr('disabled', 'disabled');
                signup_page_loaded = false;
                $load_more_button.data('vsuPage', 1).hide();
                $signup_page.trigger('vsu.load_signup_data', [true]);
            }).on('change', function(){
                update_export_button();
            });
        
    }(jQuery));
    // ----- /Signup Page ----- //
});