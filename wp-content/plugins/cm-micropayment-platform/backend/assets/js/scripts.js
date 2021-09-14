jQuery(document).ready(function($){

    var tooltips = jQuery( "[title]" ).tooltip({
        position: {
            my: "left top",
            at: "right+5 top-5",
            collision: "none"
        }
    });

    if(typeof jQuery('.field_help').tooltip === 'function') {
        jQuery('.field_help').tooltip({
            content: function (callback) {
                callback($(this).prop('title').split('#br#').join('<br />'));
            }
        });
    }
    $('.inlineEditButton').on('click', function() {
        var td = $(this).parent().parent().parent();
        $(td).hide().siblings().hide();

        $(td).parent().append('<td  class="edit-form-wallet-points" colspan="7">' +
            '<form class="form-wallet">' +
            '<input type="hidden" name="wallet_id" id="wallet_id"/>' +
                '<table class="form-table">' +
                    '<tbody>'+
                        '<tr class="form-field">' +
                            '<th scope="row" valign="top">' +
                                '<label for="points">' + cmmp_data.l18n.label + '</label>' +
                            '</th>' +
                            '<td>' +
                                '<label>Points:<input type="text" name="points" id="points" style="width:200px"></label>' +
                                '<label>Reason:<input type="text" name="reason" id="reason" style="width:400px"></label>' +
                            '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>' +
                '<p class="submit"> ' +
                    '<a class="button-primary save alignleft">' + cmmp_data.l18n.save + '</a>' +
                    '<a class="button-secondary cancel alignleft" accesskey="c">' + cmmp_data.l18n.cancel + '</a>' +
                '</p>' +
            '</form>' +
            '</td>');

        $('input#wallet_id').val(parseInt($(td).find('a.wallet_id_holder').data('wallet-id')));
        $('input#points').val(parseInt($(td).parent().find('td.points').html()));
        $('form.form-wallet').submit(function(e) {
            e.preventDefault();
            submitChangePointsForm();
        });

        $('.edit-form-wallet-points .cancel').on('click', function() {
            $('.edit-form-wallet-points').remove();
            $(td).show().siblings().show();
        });

        submitChangePointsForm();

        function submitChangePointsForm() {
            $('.edit-form-wallet-points .save').on('click', function(){
                $.ajax({
                    url: cmmp_data.ajaxurl,
                    data : $('.form-wallet').serialize(),
                    success : function(response) {
                        var resp = JSON.parse(response);
                        if(resp.error == undefined) {
                            $(td).show().siblings().show();
                            $('.edit-form-wallet-points').remove();
                            (td).parent().find('td.points').html(resp.points);
                        } else {
                            alert(resp.error);
                        }
                    }
                });
            });
        }
    });

    $('.inlinePayoutButton').on('click', function() {
        var td = $(this).parent().parent().parent();
        var containerId = 'edit-form-paypal-payout';
        var formId = 'form-wallet-payout';
        $(td).hide().siblings().hide();

        $(td).parent().append('<td class="'+containerId+'" colspan="7">' +
            '<form class="'+formId+'">' +
            '<input type="hidden" name="_cmmp_paypal_payout" value="'+cmmp_data.paypal_payout_nonce+'" />' +
            '<input type="hidden" name="wallet_id" id="wallet_id"/>' +
                '<table class="form-table">' +
                    '<tbody>'+
                        '<tr class="form-field">' +
                            '<th scope="row" valign="top">' +
                                '<label for="points">' + cmmp_data.l18n.payout_label + '</label>' +
                            '</th>' +
                            '<td>' +
                                '<input type="text" name="points" id="points" style="width:100px">' +
                            '</td>' +
                        '</tr>' +
                        '<tr class="form-field">' +
                            '<th scope="row" valign="top">' +
                                '<label for="points">' + cmmp_data.l18n.payout_email_label + '</label>' +
                            '</th>' +
                            '<td>' +
                                '<input type="text" name="email" id="email" style="width:100px">' +
                            '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>' +
                '<p class="submit"> ' +
                    '<a class="button-primary save alignleft">' + cmmp_data.l18n.save + '</a>' +
                    '<a class="button-secondary cancel alignleft" accesskey="c">' + cmmp_data.l18n.cancel + '</a>' +
                '</p>' +
            '</form>' +
            '</td>');

        $('input#wallet_id').val(parseInt($(td).find('a.wallet_id_holder').data('wallet-id')));
        $('input#points').val(parseInt($(td).parent().find('td.points').html()));

        $('form.form-wallet').submit(function(e) {
            e.preventDefault();
            submitPayoutPointsForm();
        });

        $('.edit-form-paypal-payout .cancel').on('click', function() {
            $('.edit-form-paypal-payout').remove();
            $(td).show().siblings().show();
        });

        submitPayoutPointsForm();

        function submitPayoutPointsForm() {
            var containerId = 'edit-form-paypal-payout';
            var formId = 'form-wallet-payout';

            $('.'+containerId+' .save').on('click', function(){
                $.ajax({
                    url: cmmp_data.paypal_payout_ajaxurl,
                    method: 'post',
                    data : $('.'+formId).serialize(),
                    success : function(response) {
                        var resp = JSON.parse(response);
                        if(resp.error === undefined && resp.points.length) {
                            $(td).show().siblings().show();
                            $('.'+containerId).remove();
                            (td).parent().find('td.points').html(resp.points);
                        } else {
                            alert(resp.error);
                        }
                    }
                });
            });
        }
    });

    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid #fdd",
        padding: "2px",
        "background-color": "#fee",
        opacity: 0.80
    }).appendTo("body");

    $("#cm-micropayment-report-placeholder").bind("plothover", function (event, pos, item) {

        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        $("#hoverdata").text(str);

        if (item) {
            var x = item.datapoint[0].toFixed(2),
                y = item.datapoint[1].toFixed(2);

            $("#tooltip").html(y)
                .css({top: item.pageY+5, left: item.pageX+5})
                .fadeIn(200);
        } else {
            $("#tooltip").hide();
        }
    });

    $("#cm-micropayment-report-placeholder").bind("plotclick", function (event, pos, item) {
        if (item) {
            $("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
            plot.highlight(item.series, item.datapoint);
        }
    });

    $('#only_successful').bind('change', function(){
        $('#cm-filter-form').submit();
    });
	
	$('#cm_micropayment_grant_points_to_admin_or_seller0').change(function(){
		$('.cm_micropayment_grant_points_to_admin').slideUp();
    });

	$('#cm_micropayment_grant_points_to_admin_or_seller1').change(function(){
		$('.cm_micropayment_grant_points_to_admin').slideDown();
    });

	$('#cm_micropayment_grant_points_to_admin_or_seller2').change(function(){
		$('.cm_micropayment_grant_points_to_admin').slideUp();
    });

    $('#cm_micropayment_assign_wallet_to_customer').change(function(){
        if($(this).is(':checked')) {
            $('.assign-wallet-per-customer-button-container').slideDown();
			$('#cm_micropayment_number_of_wallets').prop("disabled", true);
        } else {
            $('.assign-wallet-per-customer-button-container').slideUp();
			$('#cm_micropayment_number_of_wallets').prop("disabled", false);
        }
    });
	
	$('body').on('click', '.accept_payment_cls', function() {

		var r = confirm("Are you sure you received payment and manually accept?");
		if(r) {
			$.ajax({
				url: cmmp_data.ajaxurlforacceptpayment,
				type: 'post',
				data: {
					action: 'cm_micropayment_platform_accept_payment',
					t_id: $(this).attr('t_id')
				},
				success : function(response) {
					location.reload();
				}
			});
		}
	});
    $('body').on('click', '.exw-check-status', function() {
        $('.exw-statuses').fadeOut('slow')
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'exw_check_status',
            },
            success: function (html) {
                $('.exw-statuses').html(html).fadeIn('slow')
            }
        });
    });
    $('body').on('click', '.exw-refresh-status', function() {
        $('.cms-loader-ref').fadeIn('slow');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'exw_update_external_wallets',
            },
            success: function (html) {
//                console.log(html);
                $('.cms-loader-ref').fadeOut('slow');

            }
        });
    });

	if($('#cm_micropayment_assign_wallet_to_customer').is(':checked')) {
		$('#cm_micropayment_number_of_wallets').prop("disabled", true);
	} else {
		$('#cm_micropayment_number_of_wallets').prop("disabled", false);
	}

	$('#cmmp_change_user_wallet_show').click(function (e) {
	    $('.cmmp_change_user_wallet_wrap').slideToggle();
    });

});