function cmppp_paybox_submit(ev, callback) {
    ev.stopPropagation();
    ev.preventDefault();
    var $ = jQuery;
    var form = $(this);
    if (form.find('input[name=priceIndex]:checked').length != 0 || form.find('input[name=priceIndex][type=radio]').length == 0) {
        var data = form.serialize();
        data += "&user_time_offset=" + cmppp_getCurrentUserTimeOffset(); // save current user time offset

        $.post(CMPPPSettings.ajaxUrl, data, function (response) {
            if (response.success) {
                CMPPP.Utils.toast(response.msg, 'success');
            } else {
                CMPPP.Utils.toast(response.msg, 'error');
            }
            if (response.redirect) {
                location.href = response.redirect;
            } else {
                if (response.success) {
                    location.reload();
                }
            }
            if (typeof callback == 'function') {
                callback(response);
            }
        });
    }
}

function cmppp_getCurrentUserTimeOffset() {
    return (new Date().getTimezoneOffset()) * 60 / (-1); // in seconds
}


jQuery(function ($) {

    if (CMPPPSettings.userTimeOffset == 'undefined' || CMPPPSettings.userTimeOffset != cmppp_getCurrentUserTimeOffset()) {
        let data = {
            action: 'cmppp_init_user_time_offset',
            user_time_offset: cmppp_getCurrentUserTimeOffset(),
        }

        $.post(CMPPPSettings.ajaxUrl, data, function (r) {
            // location.reload();
        }, 'json');
    }

    $('.cmppp-micropayments-form, .cmppp-edd-form').submit(function (ev) {
        cmppp_paybox_submit.call(this, ev, function (response) {
            if (response.success) {
//				location.reload();
            }
        });
    });

    $('.cmppp-refund-btn').each(function (ev) {

        var btn = $(this);
        var wrapper = btn.parents('.cmppp-refund-btn-wrapper');
        var form = wrapper.find('form');
        var overlay = $('<div/>', {"class": "cmppp-overlay"});
        $('body').append(overlay);

        btn.click(function (ev) {
            ev.stopPropagation();
            ev.preventDefault();
            overlay.show();
            form.appendTo(overlay);
            form.show();
            form.css('top', ($(window).height() - form.height()) / 4 + "px");
        });

        $('*[data-cmppp-action=close]', form).click(function (ev) {
            ev.stopPropagation();
            ev.preventDefault();
            form.hide();
            form.appendTo(wrapper);
            overlay.hide();
        });

        form.submit(function (ev) {
            ev.stopPropagation();
            ev.preventDefault();
            if (form.find('input[name=reason]:checked').length == 0) return;
            form.hide();
            $.post(form.attr('action'), form.serialize(), function (response) {
                if (response.status == 'ok') {
                    CMPPP.Utils.toast(response.msg, 'success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    CMPPP.Utils.toast(response.msg, 'error');
                    form.appendTo(wrapper);
                    overlay.hide();
                }
            });
        });

    });

    $('.cmppp-refund-box input[name=reason]').change(function () {
        var form = $(this).parents('form');
        var textarea = form.find('textarea');
        if (this.value == 'other') {
            textarea.show();
        } else {
            textarea.hide();
        }
    });

    if (CMPPPSettings.restrictCopyingContent == '1') {
        $(".cmppp_content_inner_container").on("contextmenu", function (e) {
            return false;
        });
        $('.cmppp_content_inner_container').bind('cut copy paste', function (e) {
            e.preventDefault();
        });
    }

    if ($('#cmppp_backlink.empty').length) {
        $.get(location.href, function (html) {
            var backLinkEl = $(html).find('#cmppp_backlink');
            if (!backLinkEl.hasClass('empty')) {
                $('#cmppp_backlink.empty').html(backLinkEl.html());
            }
        }, 'html');
    }


});
