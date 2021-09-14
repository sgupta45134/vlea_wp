// Load Elementor Icons. Needed because we use fa- icons while elementor uses
// eicon- icons, that do not need loading icon libraries.
jQuery(window).on('elementor:init', function () {
	elementor.iconManager.loadIconLibraries();
});

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
        vars[key] = value;
    });
    return vars;
}
function getUrlParam(parameter, defaultvalue) {
    var urlparameter = defaultvalue;
    if (window.location.href.indexOf(parameter) > -1) {
        urlparameter = getUrlVars()[parameter];
    }
    return urlparameter;
}

function dce_get_element_id_from_cid(cid) {
    var iFrameDOM = jQuery("iframe#elementor-preview-iframe").contents();
    var eid = iFrameDOM.find('.elementor-element[data-model-cid=' + cid + ']').data('id');
    return eid;
}

function dce_get_setting_name(einput) {
    if (einput.hasClass('elementor-input')) {
        if (einput.data('setting') == 'url') {
            var settingName = '';
            jQuery.each(einput.closest('.elementor-control').attr('class').split(' '), function (index, element) {
                if (index == 1) {
                    settingName = element.replace('elementor-control-', '');
                    return false;
                }
            });
            if (settingName) {
                return settingName;
            }
        }
    }
    return einput.data('setting');
}
function dce_toBase64(url, callback) {
    var img = new Image();
    img.crossOrigin = "anonymous";
    img.onload = function () {
        var canvas = document.createElement("canvas");
        var ctx = canvas.getContext("2d");
        canvas.height = this.height;
        canvas.width = this.width;
        ctx.drawImage(this, 0, 0);
        var dataURL = canvas.toDataURL("image/png");
        callback(dataURL);
        canvas = null;
    };
    img.src = url;
}
function dce_getimageSizes(url, callback) {
    var img = new Image();
    img.crossOrigin = "anonymous";
    img.onload = function () {
        var sizes = {};
        sizes.height = this.height;
        sizes.width = this.width;
        sizes.coef = sizes.height / sizes.width;
        callback(sizes);
    };
    img.src = url;
}

jQuery(window).on('elementor:init', function () {
    // Query Control
    var DCEControlQuery = elementor.modules.controls.Select2.extend({

        cache: null,
        isTitlesReceived: false,
        getSelect2Placeholder: function getSelect2Placeholder() {
            var self = this;
            return {
                id: '',
                text: self.model.get('placeholder'), //'All',
            };
        },
        getSelect2DefaultOptions: function getSelect2DefaultOptions() {
            var self = this;
            return jQuery.extend(elementor.modules.controls.Select2.prototype.getSelect2DefaultOptions.apply(this, arguments), {
                ajax: {
                    transport: function transport(params, success, failure) {
                        var data = {
                            q: params.data.q,
                            query_type: self.model.get('query_type'),
                            object_type: self.model.get('object_type'),
                        };
                        return elementorCommon.ajax.addRequest('dce_query_control_filter_autocomplete', {
                            data: data,
                            success: success,
                            error: failure,
                        });
                    },
                    data: function data(params) {
                        return {
                            q: params.term,
                            page: params.page,
                        };
                    },
                    cache: true
                },
                escapeMarkup: function escapeMarkup(markup) {
                    return markup;
                },
                minimumInputLength: 1
            });
        },
        getValueTitles: function getValueTitles() {
            var self = this,
                    ids = this.getControlValue(),
                    queryType = this.model.get('query_type');
            objectType = this.model.get('object_type');
            if (!ids || !queryType)
                return;
            if (!_.isArray(ids)) {
                ids = [ids];
            }

            elementorCommon.ajax.loadObjects({
                action: 'dce_query_control_value_titles',
                ids: ids,
                data: {
                    query_type: queryType,
                    object_type: objectType,
                    unique_id: '' + self.cid + queryType,
                },
                success: function success(data) {
                    self.isTitlesReceived = true;
                    self.model.set('options', data);
                    self.render();
                },
                before: function before() {
                    self.addSpinner();
                },
            });
        },
        addSpinner: function addSpinner() {
            this.ui.select.prop('disabled', true);
            this.$el.find('.elementor-control-title').after('<span class="elementor-control-spinner dce-control-spinner">&nbsp;<i class="fa fa-spinner fa-spin"></i>&nbsp;</span>');
        },
        onReady: function onReady() {
            setTimeout(elementor.modules.controls.Select2.prototype.onReady.bind(this));
            if (this.ui.select) {
                var self = this,
                        ids = this.getControlValue(),
                        queryType = this.model.get('query_type');
                objectType = this.model.get('object_type');
                jQuery(this.ui.select).attr('data-query_type', queryType);
                if (objectType) {
                    jQuery(this.ui.select).attr('data-object_type', objectType);
                }
                dce_update_query_btn(this.ui.select);
            }

            if (!this.isTitlesReceived) {
                this.getValueTitles();
            }
        },
        onBeforeDestroy: function onBeforeDestroy() {
            if (this.ui.select.data('select2')) {
                this.ui.select.select2('destroy');
            }

            this.$el.remove();
        },
    });
    // Add Control Handlers
    elementor.addControlView('ooo_query', DCEControlQuery);
    jQuery(document).on('change', '.elementor-control-type-ooo_query select', function () {
        var eid = dce_get_element_id_from_cid(dce_model_cid);
        var iFrameDOM = jQuery("iframe#elementor-preview-iframe").contents();
        dce_update_query_btn(this);
    });
});
function dce_update_query_btn(ooo) {
    var setting = jQuery(ooo).data('setting'),
            query_type = jQuery(ooo).attr('data-query_type'),
            object_type = jQuery(ooo).attr('data-object_type');
    jQuery(ooo).siblings('.dce-elementor-control-quick-edit').remove();
    if (jQuery(ooo).val() && (!jQuery.isArray(jQuery(ooo).val()) || (jQuery.isArray(jQuery(ooo).val()) && jQuery(ooo).val().length == 1))) {
        var edit_link = '#';
        switch (query_type) {
            case 'posts':
                if (!object_type || object_type != 'type') {
                    edit_link = ElementorConfig.home_url + '/wp-admin/post.php?post=' + jQuery(ooo).val();
                    if (object_type == 'elementor_library') {
                        edit_link += '&action=elementor';
                    } else {
                        edit_link += '&action=edit';
                    }
                }
                break;
            case 'users':
                if (!object_type || object_type != 'role') {
                    edit_link = ElementorConfig.home_url + '/wp-admin/user-edit.php?user_id=' + jQuery(ooo).val();
                }
                break;
            case 'terms':
                if (object_type) {
                    edit_link = ElementorConfig.home_url + '/wp-admin/term.php?tag_ID=' + jQuery(ooo).val();
                    edit_link += '&taxonomy=' + object_type;
                }
                break;
        }
        if (edit_link != '#') {
            jQuery(ooo).parent().append('<div class="elementor-control-unit-1 tooltip-target dce-elementor-control-quick-edit" data-tooltip="Quick EDIT"><a href="' + edit_link + '" target="_blank" class="dce-quick-edit-btn"><i class="eicon-pencil"></i></a></div>');
        }
    } else {
        var new_link = '#';
        switch (query_type) {
            case 'posts':
                if (!object_type || object_type != 'type') {
                    new_link = ElementorConfig.home_url + '/wp-admin/post-new.php';
                    if (object_type) {
                        new_link += '?post_type=' + object_type;
                        if (object_type == 'elementor_library') {
                            new_link = ElementorConfig.home_url + '/wp-admin/edit.php?post_type=' + object_type + '#add_new';
                        }
                    }
                }
                break;
            case 'users':
                if (!object_type || object_type != 'role') {
                    new_link = ElementorConfig.home_url + '/wp-admin/user-new.php';
                }
                break;
            case 'terms':
                new_link = ElementorConfig.home_url + '/wp-admin/edit-tags.php';
                if (object_type) {
                    edit_link += '&taxonomy=' + object_type;
                }
                break;
        }
        if (new_link != '#') {
            jQuery(ooo).parent().prepend('<div class="elementor-control-unit-1 tooltip-target dce-elementor-control-quick-edit" data-tooltip="Add NEW"><a href="' + new_link + '" target="_blank" class="dce-quick-edit-btn"><i class="eicon-plus"></i></a></div>');
        }
    }
}

jQuery(window).on('load', function () {
    if (jQuery('#elementor-preview-iframe').length) {
        var element = getUrlParam('element');
        if (element) {
            var iFrame = jQuery("iframe#elementor-preview-iframe");
            var iFrameDOM = iFrame.contents();
            var thisTimeout = setInterval(function(){
                if (!jQuery('#elementor-loading:visible').length) {
                    iFrameDOM.find("div.elementor-element-" + element).trigger('click');
                    clearInterval(thisTimeout);
                }
            }, 1000);
        }
    }
});
