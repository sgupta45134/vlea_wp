jQuery(document).ready(function ($) {

	$(document).ready( () => { $('.wallet_transactions_body table').tablesorter(); } );

    $('.create-wallet-button-ajax').on('click', function (e) {
        var createButton = this;
        e.preventDefault();
		e.stopImmediatePropagation();

        $.ajax({
            url: window.cmmp_data.ajaxurl,
            type: 'post',
            data: {
                action: 'create_wallet_id'
            },
            success: function (response) {
                var parsedResponse = JSON.parse(response);

                if (parsedResponse.success) {
					let wallet_id = '<span class="createdWalletID">'+parsedResponse.wallet_name+'</span>';
                    $('.new_wallet_id_bar').append( wallet_id);
                    $('.new_wallet_id_bar').show();
                } else if (parsedResponse.error) {
					alert(parsedResponse.error);
				}
                if (window.cmmp_data.hideCreateButtonAfterAction === "1") {
                    $(createButton).hide();
                }
            }
        });
    });

    $('#provide-wallet-id').submit(function (e) {
        var wallet_id = $('#wallet_id').val();
        var form = this;
		e.preventDefault();
		e.stopImmediatePropagation();

        if (wallet_id == '') {
            alert(cmmp_data.l18n.missing_wallet_id);
        }

        $.ajax({
            url: cmmp_data.ajaxurl,
            type: 'post',
            data: {
                action: 'get_wallet_info',
                wallet_id: $('#wallet_id').val()
            },
            success: function (response) {
                var parsedResponse = JSON.parse(response);
                if (parsedResponse.success) {

                    if ($('.wallet_info_container').length > 0) {
                        $('.wallet_info_container').remove();
                    }

                    $(form).after(parsedResponse.content);
                    $('.wallet_transactions_body table').tablesorter();
                } else {
                    alert(parsedResponse.error);
                }
            }
        });
        return true;
    });

    $('#cm-micropayments-checkout-form-submit').click(function (e) {
        var walletId = $('#checkout_wallet_id').val();
        var form = $('#cm-micropayments-checkout-form');
        e.preventDefault();
		e.stopImmediatePropagation();

        if (walletId != '') {
            $.ajax({
                url: cmmp_data.ajaxurl,
                type: 'post',
                data: {
                    action: 'check_wallet_id',
                    wallet_id: walletId
                },
                success: function (response) {
                    var resp = JSON.parse(response);
                    if (resp.success) {
                        $(form).submit();
                    } else {
                        $('.entry-content .error').html('<div class="cm-checkout-error">'+resp.message+'</div>');
                    }
                }
            });
        }
    });
	$("#sel_wallet_id_to").select2({
		dropdownParent: $('.wallet_to_user_section'),
	});

	$("#points_1").on('change', function(){
		var points = $(this).val();
		$("#cmmp_points_total_price").text($("#cmmp_points_total_price").data()['cost'] * points);
	});
});