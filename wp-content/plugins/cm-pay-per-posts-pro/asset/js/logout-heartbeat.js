jQuery(function ($) {

    if (wp && wp.heartbeat) {
        wp.heartbeat.interval('slow');
//		wp.heartbeat.interval( 'fast' );
    }

    $(document).on('heartbeat-send', function (e, data) {
        if (typeof CMPPP_Logout_Hearbeat !== "undefined") {
            data['cmppp_check_post'] = CMPPP_Logout_Hearbeat.postId;
        }
    });

    $(document).on('heartbeat-tick', function (e, data) {
        if (data['cmppp_check_post'] !== true) {
            if (!$('body').hasClass('wp-admin')) {
                var cmpppContentContainer = $('.cmppp_content_container');
                if (cmpppContentContainer.length && !cmpppContentContainer.hasClass('cmppp-not-allowed')) {
                    location.reload();
                }
            }
        }
    });

});
