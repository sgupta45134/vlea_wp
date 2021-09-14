<?php

use com\cminds\payperposts\App;

// $pluginReviewLink = '';
$twitterTweet = rawurlencode(__('Checkout the') .' '. App::getPluginName($full = true) . ' ('. App::PLUGIN_WEBSITE .') #WordPress #Plugin by @CMPLUGINS');
?>

<div class="cminds_call_to_action">
	<div id="fb-root"></div>
    <div class="inner_box">
    	<div class="group">
	        <h3><?php echo __('Share your Appreciation'); ?></h3>
	        <?php /*
	        <a target="_blank" href="<?php echo esc_attr($pluginReviewLink); ?>">
	            <div class="btn button">
	                <div class="dashicons dashicons-share-alt2"></div><span><?php echo __('Submit a review'); ?></span>
	            </div>
	        </a> */ ?>
	        <a target="_blank"  href="http://twitter.com/home/?status=<?php echo esc_attr(urlencode($twitterTweet)) ?>" class="btn button">
                <span class="dashicons dashicons-twitter"></span><span><?php echo __('Tweet'); ?></span>
	        </a>
	    </div>
	    <div class="group">
	        <h3><?php echo __('Stay Up-to-Date'); ?></h3>
	        <a href="https://twitter.com/CMPLUGINS" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true"><?php
	        	echo __('Follow'); ?> @CMPLUGINS</a>
	        <script>!function (d, s, id) {
	                var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
	                if (!d.getElementById(id)) {
	                    js = d.createElement(s);
	                    js.id = id;
	                    js.src = p + '://platform.twitter.com/widgets.js';
	                    fjs.parentNode.insertBefore(js, fjs);
	                }
	            }(document, 'script', 'twitter-wjs');
	        </script>
	
	        <div class="g-follow" data-annotation="none" data-height="24" data-href="https://plus.google.com/108513627228464018583" data-rel="publisher"></div>
	
	        <script type="text/javascript">
	            (function () {
	                var po = document.createElement('script');
	                po.type = 'text/javascript';
	                po.async = true;
	                po.src = 'https://apis.google.com/js/platform.js';
	                var s = document.getElementsByTagName('script')[0];
	                s.parentNode.insertBefore(po, s);
	            })();
	        </script>
	        
	        <script>(function (d, s, id) {
	                var js, fjs = d.getElementsByTagName(s)[0];
	                if (d.getElementById(id))
	                    return;
	                js = d.createElement(s);
	                js.id = id;
	                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
	                fjs.parentNode.insertBefore(js, fjs);
	            }(document, 'script', 'facebook-jssdk'));</script>
	
	        <div class="fb-like" data-href="https://www.facebook.com/cmplugins" data-width="100" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>
		</div>

        <!-- Begin MailChimp Signup Form -->
        <div id="group mc_embed_signup">
            <form action="//cminds.us3.list-manage.com/subscribe/post?u=f48254f757fafba2669ae5918&amp;id=142732cbf9" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                <div id="mc_embed_signup_scroll">
                    <h3 for="mce-EMAIL">CM Newsletter - coupons, deals, news</h3>
                    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
                    <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="btn button">
                    <span style="display:inline-block; position: relative"><div class="cmtt_field_help" title="We only send newsletters a couple of times a year. They include great deals, promo codes and information about our new plugins!"></div></span>
                    <!-- real people should not fill this in and expect good things - do not remove this or risk fsorm bot signups-->
                    <div style="position: absolute; left: -5000px;"><input type="text" name="b_f48254f757fafba2669ae5918_142732cbf9" tabindex="-1" value=""></div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <!--End mc_embed_signup-->
        
    </div>
    
    <div class="clear"></div>
</div>

<style type="text/css">
#settings {clear: right;}
.cminds_call_to_action {display: inline-block; float: right;}
.cminds_call_to_action .group {margin: 0.5em 0 1em 0;}
.cminds_call_to_action h3 {display: inline-block; margin: 0 0.5em 0 0; font-size: 110%;}
.cminds_call_to_action h3, .cminds_call_to_action .button, .cminds_call_to_action input, .cminds_call_to_action iframe, .cminds_call_to_action .fb-like {vertical-align: middle;}
.cminds_call_to_action .inner_box{
    border: 1px solid #dddddd;
    padding: 0.5em 1em;
    background: white;
}
.cminds_call_to_action a{
    text-decoration: none;
}
.fb_iframe_widget{vertical-align: top;line-height: 2em;}

</style>