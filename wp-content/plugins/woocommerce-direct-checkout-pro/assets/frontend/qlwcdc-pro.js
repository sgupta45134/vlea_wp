(function ($) {
    "use strict";

    var timeout;
    var delay = 1000;
    var is_blocked = function ($node) {
        return $node.is('.processing') || $node.parents('.processing').length;
    };
    var block = function ($node) {
        if (!is_blocked($node)) {
            $node.addClass('processing').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    };

    var unblock = function ($node) {
        $node.removeClass('processing').unblock();
    };

    // Archives
    // ---------------------------------------------------------------------------

    $(document).on('click', '.qlwcdc_quick_view', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var product_id = $(this).data('product_id'),
            $modal = $('#qlwcdc_quick_view_modal');
        if (product_id && woocommerce_params.ajax_url) {

            $.ajax({
                type: 'post',
                url: woocommerce_params.ajax_url,
                data: {
                    action: 'qlwcdc_quick_view_modal',
                    nonce: qlwcdc.nonce,
                    product_id: product_id
                },
                complete: function (response) {

                    $modal.addClass('opening');
                    setTimeout(function () {
                        $modal.addClass('open');
                        $modal.removeClass('opening');
                    }, 50);
                },
                success: function (response) {

                    var $response = $(response);
                    $response.find('.woocommerce-product-gallery').each(function () {
                        $(this).wc_product_gallery();
                    });
                    $response.on('click', '.close', function (e) {
                        $modal.addClass('closing');
                        setTimeout(function () {
                            $modal.removeClass('open');
                            $modal.removeClass('closing');
                        }, 600);
                    });
                    $modal.find('.modal-content').replaceWith($response);
                    if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                        $modal.find('.variations_form').wc_variation_form();
                    }
                }
            });
        }

        return false;
    });

    $(document).on('click', '#qlwcdc_quick_view_modal', function (e) {

        var $context = $(e.target),
            $modal = $(e.delegateTarget);

        if ($context.hasClass('modal-dialog')) {
            $modal.addClass('closing');
            setTimeout(function () {
                $modal.removeClass('open');
                $modal.removeClass('closing');
            }, 600);
        }
    });

    // Product
    // ---------------------------------------------------------------------------

    $(document).on('added_to_cart_message', function (e, product_id) {

        if ($('body').hasClass('qlwcdc-product-ajax-alert') && product_id) {

            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url,
                data: $.param({
                    action: 'qlwcdc_add_product_cart_ajax_message',
                    'add-to-cart': product_id
                }),
                success: function (response) {
                    if (response.success) {
                        $('.woocommerce-notices-wrapper:first').hide().append($(response.data).html()).fadeIn('fast');
                    }
                },
            });

        }
    });

    $(document).on('click', '.single_add_to_cart_button:not(.qlwcdc_quick_purchase):not(.disabled)', function (e) {

        var $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            product_id = $form.find('input[name=variation_id]').val() || $form.find('[name=add-to-cart]').val() || false;

        if ($('body').hasClass('qlwcdc-product-ajax') && product_id) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url, // woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                data: $form.serialize() + '&' + $.param({
                    'add-to-cart': product_id,
                    action: 'qlwcdc_add_to_cart_action'
                }),
                beforeSend: function (response) {
                    $thisbutton.removeClass('added').addClass('loading');
                },
                complete: function (response) {
                    $thisbutton.addClass('added').removeClass('loading');
                },
                success: function (response) {
                    if (response.error & response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                    $(document.body).trigger('added_to_cart_message', product_id);
                },
            });

            return false;
        }

    });

    $(document).on('click', '.qlwcdc_quick_purchase', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this),
            $form = $button.closest('form.cart');

        if ($button.is('.disabled')) {
            return;
        }

        var product_id = $form.find('[name=add-to-cart]').val() || 0,
            variation_id = $form.find('input[name=variation_id]').val(),
            params = $form.serialize().replace(/\%5B%5D/g, '[]') || 0;

        $button.attr('data-href', function (i, h) {
            if (h.indexOf('?') != -1) {
                $button.attr('data-href', $button.attr('data-href') + '&add-to-cart=' + product_id);
            } else {
                $button.attr('data-href', $button.attr('data-href') + '?add-to-cart=' + product_id);
            }
        });

        if (variation_id != undefined && variation_id == 0) {
            return false;
        }

        if (params) {
            $button.attr('data-href', $button.attr('data-href') + '&' + params);
        }

        if ($button.attr('data-href') != 'undefined') {
            document.location.href = $button.attr('data-href');
        }

        return false;

    });

    // Checkout
    // ---------------------------------------------------------------------------

    $(document).on('keyup', '#qlwcdc_order_coupon_code', function (e) {

        var $form = $(this),
            $coupon = $(this).find('input[name="coupon_code"]'),
            coupon_code = $coupon.val();

        if (timeout) {
            clearTimeout(timeout);
        }

        if (!coupon_code) {
            return;
        }

        timeout = setTimeout(function () {

            if ($form.is('.processing')) {
                return false;
            }

            $form.addClass('processing').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var data = {
                security: wc_checkout_params.apply_coupon_nonce,
                coupon_code: coupon_code
            };

            $.ajax({
                type: 'POST',
                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                data: data,
                success: function (code) {
                    $form.removeClass('processing').unblock();
                    if (code) {
                        $coupon.before($(code).hide().fadeIn());
                        setTimeout(function () {
                            $(document.body).trigger('update_checkout', { update_shipping_method: false });
                        }, delay * 2);
                    }
                },
                dataType: 'html'
            });
            return false;
        }, delay);
    });

    $(document).on('change', '#order_review input.qty', function (e) {
        e.preventDefault();

        var $qty = $(this);

        $(this).css({ 'pointer-events': 'none', 'opacity': '0.5' });

        setTimeout(function () {

            var hash = $qty.attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1"),
                qty = parseFloat($qty.val());
            $.ajax({
                type: 'post',
                url: woocommerce_params.ajax_url,
                data: {
                    action: 'qlwcdc_update_cart',
                    nonce: qlwcdc.nonce,
                    hash: hash,
                    quantity: qty
                },
                beforeSend: function (response) {
                    block($('#order_review'));
                },
                complete: function (response) {
                    unblock($('#order_review'));
                },
                success: function (response) {
                    if (response) {
                        $('#order_review').html($(response).html()).trigger('updated_checkout');
                        $(document.body).trigger('added_to_cart');
                    }
                },
            });
        }, 400);
    });

    $(document).on('click', '#order_review a.remove', function (e) {
        e.preventDefault();

        $(this).css({ 'pointer-events': 'none', 'opacity': '0.5' });

        var hash = $(this).data('cart_item_key');
        $.ajax({
            type: 'post',
            url: woocommerce_params.ajax_url,
            data: {
                action: 'qlwcdc_update_cart',
                nonce: qlwcdc.nonce,
                quantity: 0,
                hash: hash
            },
            beforeSend: function (response) {
                block($('#order_review'));
            },
            complete: function (response) {
                unblock($('#order_review'));
            },
            success: function (response) {
                if (response) {
                    $('#order_review').html($(response).html()).trigger('updated_checkout');
                    $(document.body).trigger('added_to_cart');
                }
            },
        });
    });

})(jQuery);