(function (window, document, $, undefined) {

    'use strict';

    var ElementPackMagicCopy = {
        //Initializing properties and methods
        init                      : function (e) {
            ElementPackMagicCopy.globalVars();
            ElementPackMagicCopy.loadxdLocalStorage();
            ElementPackMagicCopy.loadContextMenuGroupsHooks();
        },
        globalVars                : function (e) {
            window.mc_ajax_url = bdt_ep_magic_copy.ajax_url;
            window.mc_ajax_nonce = bdt_ep_magic_copy.nonce;
            window.mc_key = bdt_ep_magic_copy.magic_key;
        },
        loadxdLocalStorage        : function () {
            xdLocalStorage.init({
                iframeUrl   : 'https://elementpack.pro/eptools/magic/index.html',
                initCallback: function () {
                    // if need any callback
                }
            });
        },
        loadContextMenuGroupsHooks: function () {
            elementor.hooks.addFilter('elements/section/contextMenuGroups', function (groups, element) {
                return ElementPackMagicCopy.prepareMenuItem(groups, element);
            });

            elementor.hooks.addFilter('elements/widget/contextMenuGroups', function (groups, element) {
                return ElementPackMagicCopy.prepareMenuItem(groups, element);
            });

            elementor.hooks.addFilter('elements/column/contextMenuGroups', function (groups, element) {
                return ElementPackMagicCopy.prepareMenuItem(groups, element);
            });
        },
        prepareMenuItem           : function (groups, element) {
            var index = _.findIndex(groups, function (element) {
                return 'clipboard' === element.name;
            });
            groups.splice(index + 1, 0, {
                name   : 'bdt-ep-live-paste',
                actions: [
                    {
                        name    : 'ep-live-paste',
                        title   : 'Live Paste',
                        icon    : 'bdt-wi-element-pack',
                        callback: function () {
                            ElementPackMagicCopy.livePaste(element);
                        }
                    }
                ]
            });
            return groups;
        },
        livePaste                 : function (e) {
            return xdLocalStorage.getItem(mc_key, function (data) {
                const magicData = JSON.parse(data.value);
                const widgetCode = magicData.widget;
                const EncodedWidgetCode = JSON.stringify(widgetCode);
                const hasResourcesFiles = /\.(jpeg|jpg|png|gif|svg|)/gi.test(EncodedWidgetCode);
                const ElementType = e.model.get('elType');

                var model = {elType: 'section', settings: widgetCode.settings};
                var options = {at: 0};

                if ('column' === ElementType) {
                    var containerSelector = e.getContainer().parent;
                } else {
                    var containerSelector = e.getContainer();
                }

                var container = containerSelector.parent;
                model.elements = widgetCode.elements;
                options.at = containerSelector.view.getOption('_index') + 1;
                model.isInner = false;

                var widgetSelector = $e.run('document/elements/create', {
                    container: container,
                    model    : model,
                    options  : options
                });

                if (hasResourcesFiles) {
                    $.ajax({
                        url       : mc_ajax_url,
                        method    : 'POST',
                        data      : {
                            action  : 'ep_elementor_import_magic_copy_assets_files',
                            data    : EncodedWidgetCode,
                            security: mc_ajax_nonce,
                        },
                        beforeSend: function () {
                            widgetSelector.view.$el.append('<div id="bdt-magic-copy-importing-images-loader">Importing Images..</div>');
                        }
                    }).done(function (response) {
                        if (response.success) {
                            var data = response.data[0];
                            model.settings = data.settings;
                            model.elType = data.elType;
                            if ('widget' === data.elType) {
                                model.widgetType = data.widgetType;
                            } else {
                                model.elements = data.elements;
                            }
                            setTimeout(function () {
                                $e.run('document/elements/delete', {container: widgetSelector});
                                var e = $e.run('document/elements/create', {
                                    model    : model,
                                    container: container,
                                    options  : options
                                });
                            }, 800);
                            $('#bdt-magic-copy-importing-images-loader').remove();
                        }
                    });
                }
            });
        }
    };
    ElementPackMagicCopy.init();
})(window, document, jQuery, xdLocalStorage);