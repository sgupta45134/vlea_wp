$window.on('elementor/frontend/init', function () {
    var ModuleHandler = elementorModules.frontend.handlers.Base,
        VisibilityControls;

    VisibilityControls = ModuleHandler.extend({
 
        bindEvents: function () {
            this.run();
        },

        getDefaultSettings: function () {
            return {
                direction: 'alternate',
                easing: 'easeInOutSine',
                loop: true,
            };
        },

        onElementChange: debounce(function (prop) {
            if (prop.indexOf('ep_floating') !== -1) {
                this.anime && this.anime.restart();
                this.run();
            }
        }, 400),

        settings: function (key) {
            return this.getElementSettings('ep_floating_effects_' + key);
        },

        run: function () {
            var options = this.getDefaultSettings(),
                element = this.findElement('.elementor-widget-container').get(0);


            if (this.settings('show')) {
                options.targets = element;
                if (
                    this.settings('translate_toggle') ||
                    this.settings('rotate_toggle') ||
                    this.settings('scale_toggle') ||
                    this.settings('skew_toggle') ||
                    this.settings('border_radius_toggle')
                ) {
                    this.anime = window.anime && window.anime(options);
                }
            }

        }
    });

    //console.log($(this.$element).hasClass("elementor-section"));

    // elementorFrontend.hooks.addAction('frontend/element_ready/section', function($scope) {
    //     elementorFrontend.elementsHandler.addHandler(VisibilityControls, { $element: $scope });
    // });

    elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
        elementorFrontend.elementsHandler.addHandler(VisibilityControls, {
            $element: $scope
        });
    });
});