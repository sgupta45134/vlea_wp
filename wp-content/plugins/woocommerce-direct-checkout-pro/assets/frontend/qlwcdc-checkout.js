(function ($) {

  $('#qlwcdc_review_offer').on('change', 'input[name=qlwcdc_review_offer_add]', function (e) {

//    wcf_order_bump_clicked = true;
    var $form = $(e.delegateTarget),
            product_id = $form.find('input[name=product_id]').val();

    $.ajax({
      type: 'POST',
      url: woocommerce_params.ajax_url,
      data: {
        action: 'qlwcdc_add_to_cart_action',
        'add-to-cart': product_id,
      },
      beforeSend: function (response) {
        //$thisbutton.removeClass('added').addClass('loading');
      },
      complete: function (response) {
        //$thisbutton.addClass('added').removeClass('loading');
      },
      success: function (response) {
        //$form.trigger('updated_checkout');
        $('body').trigger('update_checkout');
        //console.log(response);
//        if (response.error & response.product_url) {
//          window.location = response.product_url;
//          return;
//        }
//        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
//        $(document.body).trigger('added_to_cart_message', [$form.serialize()]);
      },
    });

  });
})(jQuery);