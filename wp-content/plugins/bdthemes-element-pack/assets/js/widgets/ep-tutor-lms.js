/**
 * Start tutor lms widget script
 */

( function( $, elementor ) {

	'use strict';

	var widgetTutorCarousel = function( $scope, $ ) {

		var $tutorCarousel = $scope.find( '.bdt-tutor-lms-course-carousel' );
            
        if ( ! $tutorCarousel.length ) {
            return;
        }

		var $tutorCarouselContainer = $tutorCarousel.find('.swiper-container'),
			$settings 		 = $tutorCarousel.data('settings');

		var swiper = new Swiper($tutorCarouselContainer, $settings);

		if ($settings.pauseOnHover) {
			 $($tutorCarouselContainer).hover(function() {
				(this).swiper.autoplay.stop();
			}, function() {
				(this).swiper.autoplay.start();
			});
		}

	};


	jQuery(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-tutor-lms-course-carousel.default', widgetTutorCarousel );
	});

}( jQuery, window.elementorFrontend ) );

/**
 * End tutor lms widget script
 */

