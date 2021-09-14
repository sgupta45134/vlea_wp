(function($){
  $(document).ready(function() {
    $( '.wcmo_multiselect').select2();
    $(document).on('woocommerce_variations_loaded', function(event) {
      $( 'body' ).find('.wcmo_product_assign_roles_field .wcmo_multiselect').select2();
    });
    var form, table, settings_table, tax_table, password_table;
    var settings_form = $( '.woocommerce_page_wc-settings #wcmo_restriction_method' ).closest( 'form' );
    var settings_table = $('.woocommerce_page_wc-settings #wcmo_restriction_method').closest('table');

    var password_table = $( '.woocommerce_page_wc-settings #wcmo_passwords' ).closest( 'table' ).addClass( 'wcmo_show_if_password' );
    var password_heading = $( password_table ).prev( 'h2' ).addClass( 'wcmo_show_if_password' );

    var role_table = $( '.woocommerce_page_wc-settings #wcmo_user_roles' ).closest( 'table' ).addClass( 'wcmo_show_if_user-role' );
    var role_heading = $( role_table ).prev( 'h2' ).addClass( 'wcmo_show_if_user-role' );

    var content_table = $( '.woocommerce_page_wc-settings #wcmo_redirect_to' ).closest( 'table' ).addClass( 'wcmo_show_if_restricted' );
    var content_heading = $( content_table ).prev( 'h2' ).addClass( 'wcmo_show_if_restricted' );

    var archive_table = $( '.woocommerce_page_wc-settings #wcmo_hide_products' ).closest( 'table' ).addClass( 'wcmo_show_if_restricted' );
    var archive_heading = $( archive_table ).prev( 'h2' ).addClass( 'wcmo_show_if_restricted' );

    var widgets_table = $( '.woocommerce_page_wc-settings #wcmo_enable_widget_whitelist' ).closest( 'table' ).addClass( 'wcmo_show_if_restricted' );
    var widgets_heading = $( widgets_table ).prev( 'h2' ).addClass( 'wcmo_show_if_restricted' );

    var restriction_method = $('#wcmo_restriction_method').val();
    $( settings_form ).addClass( 'wcmo-restriction-method-' + restriction_method );

    var redirect = $('#wcmo_redirect_to').val();
    $( settings_form ).addClass( 'wcmo-redirect-' + redirect );

    var content = $('#wcmo_restricted_content').val();
    $( settings_form ).addClass( 'wcmo-content-' + content );

    var tax_table = $('#wcmo_override_global_restrictions').closest('table');
    var restriction_method = wcmo_vars.restriction_method;
    $(tax_table).addClass( 'wcmo-restriction-method-' + restriction_method );
    var tax_form = $('#wcmo_override_global_restrictions').closest('form');
    $(tax_form).addClass( 'wcmo-restriction-method-' + restriction_method );

    var table = $('#wcmo_user_roles').closest('table');

    $('.wcmo_show_if_password').each(function(){
      $(this).closest('tr').addClass('wcmo_show_if_password');
    });
    $('.wcmo_show_if_redirect_to_page').each(function(){
      $(this).closest('tr').addClass('wcmo_show_if_redirect_to_page');
    });
    $('.wcmo_show_if_categories').each(function(){
      $(this).closest('tr').addClass('wcmo_show_if_categories');
    });
    $('.wcmo_show_if_user-role').each(function(){
      $(this).closest('tr').addClass('wcmo_show_if_user-role');
    });

    $('body').on('change','#wcmo_restriction_method',function(){
      $( settings_form ).removeClass (function (index, className) {
  			return (className.match (/(^|\s)wcmo-restriction-method-\S+/g) || []).join(' ');
  		});
      $( settings_form ).addClass('wcmo-restriction-method-' + $('#wcmo_restriction_method').val());
    });
    $('body').on('change','#wcmo_redirect_to',function(){
      $( settings_form ).removeClass (function (index, className) {
  			return (className.match (/(^|\s)wcmo-redirect-\S+/g) || []).join(' ');
  		});
      $( settings_form ).addClass('wcmo-redirect-' + $('#wcmo_redirect_to').val());
    });
    $('body').on('change','#wcmo_restricted_content',function(){
      $( settings_form ).removeClass (function (index, className) {
  			return (className.match (/(^|\s)wcmo-content-\S+/g) || []).join(' ');
  		});
      $( settings_form ).addClass('wcmo-content-' + $('#wcmo_restricted_content').val());
    });
    // Product page
    var panel = $('#wcmo_options');
    var method = $('#wcmo_product_restriction_method').val();
    $(panel).addClass('wcmo_product_restriction_method_'+method);
    $('body').on('change','#wcmo_product_restriction_method',function(){
      $(panel).removeClass (function (index, className) {
  			return (className.match (/(^|\s)wcmo_product_restriction_method_\S+/g) || []).join(' ');
  		});
      $(panel).addClass('wcmo_product_restriction_method_' + $('#wcmo_product_restriction_method').val());
    });

    var edit_table = $( '.woocommerce_page_wc-settings #wcmo_edit_user_role' ).closest( 'table' );
    $( '#wcmo_edit_user_role' ).on( 'change', function() {
      if( $( this ).val() != '' ) {
        console.log( $( this ).val() );
        $( '.role-capabilities' ).hide();
        $( '.role-capabilities-' + $( this ).val() ).show();
        // $( '.wcmo_capability_checkbox' ).attr( 'checked', false );
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'wcmo_get_role_details',
            role: $( '#wcmo_edit_user_role' ).val(),
            security: $( '#wcmo_update_user_roles_nonce' ).val()
          },
          success: function( response ) {
            $( edit_table ).addClass( 'wcmo_role_selected' );
            $( '#wcmo_edit_role_name' ).val( response.data.name );
            $( '#wcmo_edit_role_slug' ).val( $( '#wcmo_edit_user_role' ).val() );
            var capabilities = response.data.capabilities;
            for( var prop in capabilities ) {
              $( '#' + prop ).attr( 'checked', true );
            }
          },
          error: function( response ) {
            console.log( response );
          }
        });
      } else {
        $( edit_table ).removeClass( 'wcmo_role_selected' );
      }
    });

    // Approve and reject users
    $( '.wcmo-update-user' ).on( 'click', function( e ) {
      e.preventDefault();
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'wcmo_approve_reject_user',
          status: $( this ).attr( 'data-status' ),
          security: $( '#wcmo_approve_reject_user_nonce' ).val(),
          user_id: $( '#user_id' ).val()
        },
        success: function( response ) {
          location.reload();
        }
      })
    });

    // Add new registration field
    $( '.wcmo-add-field' ).on( 'click', function( e ) {
      e.preventDefault();
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'wcmo_add_registration_field',
          label: $( '#wcmo_create_field_label' ).val(),
          type: $( '#wcmo_create_field_type' ).val(),
          priority: $( '#wcmo_create_field_priority' ).val(),
          enabled: $( '#wcmo_create_field_enabled' ).prop( 'checked' ),
          description: $( '#wcmo_create_field_description' ).val(),
          security: $( '#wcmo_registration_fields_nonce' ).val()
        },
        success: function( response ) {
          // Add the new row
          var field_id = response.data.field_id;
          var new_row = $( '#wcmo-registration-fields' ).find( 'tr' ).last().clone().appendTo( '#wcmo-registration-fields tbody' );
          $( new_row ).find( '.wcmo-field-label' ).text( $( '#wcmo_create_field_label' ).val() );
          $( new_row ).find( '.wcmo-field-type' ).text( $( '#wcmo_create_field_type' ).val() );
          $( new_row ).find( '.wcmo-field-enabled' ).attr( 'name', 'wcmo_registration_fields[fields][' + field_id + ']' ).attr( 'id', 'wcmo_registration_fields_fields_' + field_id ).prop( 'checked', $( '#wcmo_create_field_enabled' ).prop( 'checked' ) );
          $( new_row ).find( '.wcmo-field-required' ).attr( 'name', 'wcmo_registration_fields[required][' + field_id + ']' ).attr( 'id', 'wcmo_registration_fields_required_' + field_id ).prop( 'checked', $( '#wcmo_create_field_required' ).prop( 'checked' ) );
          $( new_row ).find( '.wcmo-field-admin-email' ).attr( 'name', 'wcmo_registration_fields[admin_email][' + field_id + ']' ).attr( 'id', 'wcmo_registration_fields_admin_email_' + field_id ).prop( 'checked', $( '#wcmo_create_field_admin_email' ).prop( 'checked' ) );
          $( new_row ).find( '.wcmo-field-add-to-profile' ).attr( 'name', 'wcmo_registration_fields[add_to_profile][' + field_id + ']' ).attr( 'id', 'wcmo_registration_fields_add_to_profile_' + field_id ).prop( 'checked', $( '#wcmo_create_field_add_to_profile' ).prop( 'checked' ) );
          $( new_row ).find( '.wcmo-field-priority' ).val( response.data.priority ).attr( 'name', 'wcmo_registration_fields[priority][' + field_id + ']' ).attr( 'id', 'wcmo_create_field_priority_' + field_id );
          $( new_row ).find( '.wcmo-field-description' ).val( response.data.description ).attr( 'name', 'wcmo_registration_fields[description][' + field_id + ']' ).attr( 'id', 'wcmo_create_field_description_' + field_id );
        }
      });
    });

    // Delete a registration field
    $( '.wcmo-remove-field' ).on( 'click', function( e ) {
      e.preventDefault();
      var row = $( this ).closest( 'tr' );
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'wcmo_delete_registration_field',
          field_id: $( this ).attr( 'data-field-id' ),
          security: $( '#wcmo_registration_fields_nonce' ).val()
        },
        success: function( response ) {
          // Add the new row
          var field_id = response.data.field_id;
          $( row ).remove();
        }
      });
    });

  });
})(jQuery);
