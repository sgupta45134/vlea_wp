jQuery(function ($) {

    $('.cmppp-settings-tabs a').click(function () {
        var match = this.href.match(/\#tab\-([^\#]+)$/);
        $('#settings .settings-category.current').removeClass('current');
        $('#settings .settings-category-' + match[1]).addClass('current');
        $('.cmppp-settings-tabs a.current').removeClass('current');
        $('.cmppp-settings-tabs a[href="#tab-' + match[1] + '"]').addClass('current');
        this.blur();
    });

    $('body').on('click', '.cmppp-modal-group-tabs a', function (e) {
        e.preventDefault();
        var match = this.href.match(/\#tab\-([^\#]+)$/);
        $('#TB_ajaxContent .modal-group.current').removeClass('current');
        $('#TB_ajaxContent .modal-group-' + match[1]).addClass('current');
        $('.cmppp-modal-group-tabs a.current').removeClass('current');
        $('.cmppp-modal-group-tabs a[href="#tab-' + match[1] + '"]').addClass('current');
        this.blur();
    });

    $('body').on('click', '.modal-group .pagination a', function (e) {
        e.preventDefault();
        let pageLink = $(this);

        if (pageLink.hasClass('current')) {
            return false;
        }

        let currentLink = pageLink.closest('.pagination').find('.current');
        let currentPage = parseInt(currentLink.text());
        let pageNumber = null;
        let form = pageLink.closest('form');
        let postType = form.find('input[name=post_type]').val();
        let payment = form.find('input[name=payment]').val();
        let group_index = form.find('input[name=group_index]').val();

        if (pageLink.hasClass('next')) {
            pageNumber = parseInt(currentLink.text()) + 1;

        } else if (pageLink.hasClass('prev')) {
            pageNumber = parseInt(currentLink.text()) - 1;

        } else if (pageNumber === null) {
            pageNumber = parseInt(pageLink.text());
        }

        let data = {
            action: 'tie_posts_to_group_page',
            newPage: pageNumber,
            currentPage: currentPage,
            postType: postType,
            payment: payment,
            group_index: group_index,
        }

        $.get(ajaxurl, data, function (html) {
            let html_obj = $(html);

            form.find('table tbody').html(html_obj.find('.tbody-wrapper tbody').html());
            form.find('.pagination').html(html_obj.find('.pagination-wrapper .pagination').html());

        }, 'html');

        return false;
    });

    $('body').on('click', '.cmppp-tie-post-group-action-go', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let group_type = form.find('input[name=payment]').val();
        let group_index = form.find('input[name=group_index]').val();
        let post_type = form.find("input[name=post_type]").val();


        $('<img src="/wp-content/plugins/cm-pay-per-posts-pro/asset/img/ajax-loader.gif">')
            .insertAfter($(this));
        $(this).remove();

        let data = {
            action: 'tie_posts_to_group_action',
            formData: form.serialize(),
        };

        $.get(ajaxurl, data, function (r) {
            let data = {
                action: 'tie_posts_to_group',
                group_type: group_type,
                index: group_index
            };

            $.get(ajaxurl, data, function (html) {
                let modalGroup = form.closest('.modal-group');
                modalGroup.html(
                    $(html).find('.modal-group-' + post_type).html()
                );
            }, 'html');


        }, 'json');

        return false;
    });

    $('body').on('submit', '#TB_ajaxContent .modal-group form', function (e) {
        e.preventDefault();
        return false;
    });

    $('body').on('click', '#TB_ajaxContent .modal-group .search_post', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');

        let postName = $(this).parent().find('input[type=search]').val().trim();

        if (postName.length) {
            $.get(ajaxurl, {
                action: 'tie_posts_to_group__search_post',
                post_name: postName,
                post_type: form.find("input[name=post_type]").val(),
                group_index: form.find("input[name=group_index]").val(),
                payment: form.find("input[name=payment]").val(),
            }, function (html) {
                // console.log('r', html);

                if (html.length) {
                    form.find('.search-results').html(html);
                    form.find('.search-results').show();
                }

            }, 'html');
        }


        return false;
    });

    $('body').on('click', '#TB_ajaxContent .modal-group .close-search-wrapper', function (e) {
        e.preventDefault();
        $(this).closest('.search-results').hide();
        return false;
    });

    $('body').on('click', '#TB_ajaxContent .modal-group .search-results a.add', function (e) {
        e.preventDefault();

        let form = $(this).closest('form');
        let post_type = form.find("input[name=post_type]").val();


        $('<img src="/wp-content/plugins/cm-pay-per-posts-pro/asset/img/ajax-loader.gif">')
            .insertAfter($(this));
        $(this).remove();


        let data = {
            action: 'tie_posts_to_group__add_post_to_group',
            post_id: $(this).data('post-id'),
            post_type: post_type,
            group_index: form.find("input[name=group_index]").val(),
            payment: form.find("input[name=payment]").val(),
        };

        $.post(ajaxurl, data, function (r) {

            let data = {
                action: 'tie_posts_to_group',
                group_type: form.find("input[name=payment]").val(),
                index: form.find("input[name=group_index]").val()
            };

            $.get(ajaxurl, data, function (html) {

                let modalGroup = form.closest('.modal-group');
                modalGroup.html(
                    $(html).find('.modal-group-' + post_type).html()
                );
            }, 'html');

        }, 'json');

        return false;
    });


    if (location.hash.length > 0) {
        $('.cmppp-settings-tabs a[href="' + location.hash + '"]').click();
    } else {
        $('.cmppp-settings-tabs li:first-child a').click();
    }

    $('.cmppp-report-filter #status_select').change(function () {
        $(this).parents('form').submit();
    });

    $('.cmppp-report-table .cmppp-actions a[data-confirm]').click(function () {
        return confirm($(this).data('confirm'));
    });

    $('.cmppp-report-table .cmppp-show-refund-reason').click(function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        $(this).parents('td').first().find('.cmppp-refund-reason').show();
    });

    $('.cmppp-subscription-add-form').each(function () {
        var form = $(this);

        form.find('a.add').click(function () {
            form.find('.inner').show();
            $(this).blur();
            return false;
        });

        var loginInput = form.find('input[name=user_login]');
        loginInput.autocomplete({
            source: ajaxurl + '?action=cmppp_user_suggest',
            delay: 500,
            minLength: 2,
            open: function () {
                $(this).addClass('open');
            },
            close: function () {
                $(this).removeClass('open');
            }
        });

        var postInput = form.find('input[name=post_find]');
        postInput.autocomplete({
            source: ajaxurl + '?action=cmppp_post_suggest',
            delay: 500,
            minLength: 2,
            open: function () {
                $(this).addClass('open');
            },
            close: function () {
                $(this).removeClass('open');
            },
            select: function (event, ui) {
                postInput.val('');
                form.find('.cmppp-subscription-add-post span').text(ui.item.label);
                form.find('.cmppp-subscription-add-post input').val(ui.item.value);
                form.find('.cmppp-subscription-add-find-post').hide();
                form.find('.cmppp-subscription-add-post').show();
            }
        });

        form.find('.cmppp-subscription-add-post-remove').click(function () {
            postInput.val('');
            form.find('.cmppp-subscription-add-post span').text('');
            form.find('.cmppp-subscription-add-post input').val(0);
            form.find('.cmppp-subscription-add-find-post').show();
            form.find('.cmppp-subscription-add-post').hide();
            postInput.focus();
            return false;
        });

    });

    var mpPriceRemove = function () {
        var button = $(this);
        button.parents('.cmmp-price').first().remove();
        return false;
    };
    $('.cmmp-price-remove').click(mpPriceRemove);

    var mpGroupRemove = function () {
        var button = $(this);
        button.parents('.cmmp-group').first().remove();
        return false;
    };
    $('.cmmp-group-remove').click(mpGroupRemove);

    $('.cmmp-group-add').click(function (e) {
        var button = $(this);
        var groups = button.parents('td').first().find('.cmmp-groups').first();
        var newGroupIndex = generateNewMpGroupIndex(button.parents('td').first());
        var source = $(button.data('template').replace(/__group_index__/g, newGroupIndex).replace('%s', ''));
        source.find('.cmppp-assign-posts-to-group').addClass('not-saved');
        groups.append(source);
        $('.cmmp-group-remove', groups).last().click(mpGroupRemove);
        $('.cmmp-price-add', groups).last().click(mpPriceAdd);
        return false;
    });

    var mpPriceAdd = function (e) {
        var button = $(this);
        var prices = button.parents('.cmmp-group').find('.cmmp-prices').first();
        var newPriceIndex = getNewmpPriceIndex(button.parents('.cmmp-group').first());
        var source = button.data('template').replace(/__item_index__/g, newPriceIndex).replace('%s', '');
        prices.append(source);
        $('.cmmp-price-remove', prices).last().click(mpPriceRemove);
        return false;
    };
    $('.cmmp-price-add').click(mpPriceAdd);

    var generateNewMpGroupIndex = function (container) {
        var max = $('#settings').data('maxMpGroupIndex');
        max = max ? max : 0;
        var groups = container.find('.cmmp-group');
        for (var i = 0; i < groups.length; i++) {
            var index = parseInt($(groups[i]).data('groupIndex'));
            if (index > max) {
                max = index;
            }
        }
        max++;
        $('#settings').data('maxMpGroupIndex', max);
        return max;
    };

    var getNewmpPriceIndex = function (container) {
        var max = 0;
        var prices = container.find('.cmmp-price');
        for (var i = 0; i < prices.length; i++) {
            var index = $(prices[i]).data('priceIndex');
            if (index > max) {
                max = index;
            }
        }
        max++;
        return max;
    };

    // Settings list key-val
    $('.cmppp-list-key-value-row input[type=button]').click(function () {
        $(this).parents('.cmppp-list-key-value-row').remove();
    });

    $('.cmppp-list-key-value-add-btn').click(function () {
        var btn = $(this);
        var wrapper = btn.parents('.cmppp-list-key-value');
        var item = wrapper.find('.cmppp-list-key-value-row:first-child').clone(true);
        var num = parseInt(wrapper.find('.cmppp-list-key-value-row').last().attr('data-num')) + 1;
        btn.before(item);
        item.attr('data-num', num);
        item.find('input').each(function () {
            var input = $(this);
            var fieldName = wrapper.data('name') + '[' + num + ']' + input.attr('name').replace('template', '');
            input.attr('name', fieldName);
        });
    });

    $('body').on('click', '#cmppp_pricing_bulk_submit', function () {

        var bulkBtn = $(this);

        var PaymentModel = $("input[name='cmppp_bulk_payment_method']:checked").val();
        var SubscriptionModel = $("input[name='cmppp_bulk_payment_by_post_or_bulk']:checked").val();

        var Categories = [];
        $.each($("input[name='cmppp_bulk_payment_specific_categories[]']:checked"), function () {
            Categories.push($(this).val());
        });

        var Period = $("input[name='cmppp_pricing_bulk_period']").val();
        var Unit = $("select[name='cmppp_pricing_bulk_unit']").val();
        var Price = $("input[name='cmppp_pricing_bulk_price']").val();

        if (Period > 0 && Price > 0) {
            var r = confirm("Are you sure you want to update all supported post types individual pricing?");
            if (r) {
                var data = {
                    action: 'bulk_request_from_admin_for_categories',
                    PaymentModel: PaymentModel,
                    SubscriptionModel: SubscriptionModel,
                    Period: Period,
                    Unit: Unit,
                    Price: Price,
                    Categories: Categories
                };
                // var data = {
                //     action: 'bulk_request_from_admin',
                //     PaymentModel: PaymentModel,
                //     SubscriptionModel: SubscriptionModel,
                //     Period: Period,
                //     Unit: Unit,
                //     Price: Price,
                //     Categories: Categories.join(",")
                // };


                bulkBtn.hide();
                $('<img class="bulk-loader" style="margin-top:10px;" src="/wp-content/plugins/cm-pay-per-posts-pro/asset/img/ajax-loader.gif">')
                    .insertAfter(bulkBtn);

                $.post(ajaxurl, data, function (response) {
                    alert(response);
                    bulkBtn.parent().find('.bulk-loader').remove();
                    bulkBtn.show();
                });
            }
        } else {
            alert("Period and Price should be greater than zero.");
        }

    });


    var $modal = $('#cmppp-price-group-modal');
    var $modalCont = $modal.find('>*');

    window.cmpppPriceGroupModalInit = function (that, group_type, group_index) {

        if ($(that).hasClass('not-saved')) {
            alert('This group hasn\'t been saved. Please save it and then you can tie posts to the group.')

        } else {

            $modalCont.html("Loading...");

            tb_show('Posts/Pages/etc. of the group', '/?TB_inline&inlineId=cmppp-price-group-modal&width=900&height=500');


            if (group_type === 'cmppp_edd_pricing_groups') {
                group_type = 'EDD';
            }

            if (group_type === 'cmppp_mp_groups') {
                group_type = 'Mircopayments';
            }

            if (group_type === 'cmppp_woo_pricing_groups') {
                group_type = 'WooCommerce';
            }

            let data = {
                action: 'tie_posts_to_group',
                group_type: group_type,
                index: group_index
            };

            $.get(ajaxurl, data, function (html) {
                $modalCont.html(html);
            }, 'html');
        }
        return false;
    };


    //////
    /* SHOW/HIDE DEPENDENT OPTION */
    var hideShowHideContentOptionDependency = function () {
        var body = $('body');
        var cmppp_hide_page_content = body.find('.cmppp_hide_page_content__option_wrapper');
        if (cmppp_hide_page_content.length) {

            var only_patr_block = body.find('.cmppp_use_post_excerpt__option_wrapper,' +
                '.cmppp_use_post_percent__option_wrapper,' +
                '.cmppp_fade_enabled__option_wrapper');

            var specified_block = body.find('.cmppp_hide_page_content_id__option_wrapper,' +
                '.cmppp_hide_page_content_additional_blocks__option_wrapper');

            var hideContentValue = cmppp_hide_page_content.find(':checked').val();

            if (hideContentValue == 0) {
                specified_block.hide();
                only_patr_block.fadeIn();

            } else if (hideContentValue === 'specified_block') {
                only_patr_block.hide();
                specified_block.fadeIn();

            } else {
                only_patr_block.fadeOut();
                specified_block.fadeOut();
            }

        }
    }

    //////
    /* SHOW/HIDE PERCENT PER USER OPTION */
    var hidePercentPerUserOption = function () {
        var body = $('body');
        var cmppp_percentage = body.find('.cmppp_percentage__option_wrapper');

        if (cmppp_percentage.length) {
            var percentage_per_user = body.find('.cmppp_percent_of_points_to_author__option_wrapper');
            var hidePercentPerUser = cmppp_percentage.find(':checked').val();

            if (hidePercentPerUser === 0) {
                percentage_per_user.hide();
            } else if (hidePercentPerUser === 'percentage_on') {
                percentage_per_user.fadeIn();
            } else {
                percentage_per_user.fadeOut();
            }
        }
    }

    var hideShowAutoredirectOptionDependency = function () {
        var body = $('body');
        var cmppp_autoredirect_to_paid_post = body.find('.cmppp_autoredirect_to_paid_post__option_wrapper');
        if (cmppp_autoredirect_to_paid_post.length) {
            var cmppp_autoredirect_to_paid_post_seconds = body.find('.cmppp_autoredirect_to_paid_post_seconds__option_wrapper');
            var autoredirectToPostValue = cmppp_autoredirect_to_paid_post.find(':checked').val();

            if (autoredirectToPostValue != 0) {
                cmppp_autoredirect_to_paid_post_seconds.fadeIn();
            } else {
                cmppp_autoredirect_to_paid_post_seconds.fadeOut();
            }
        }
    }

    hideShowHideContentOptionDependency();
    hidePercentPerUserOption();
    hideShowAutoredirectOptionDependency();


    $('body').on('change', '.cmppp_hide_page_content__option_wrapper input', function () {
        hideShowHideContentOptionDependency();
    });
    $('body').on('change', '.cmppp_percentage__option_wrapper input', function () {
        hidePercentPerUserOption();
    });
    $('body').on('change', '.cmppp_autoredirect_to_paid_post__option_wrapper input', function () {
        hideShowAutoredirectOptionDependency()
    });
    /****************************/


    /* REQUIRED FIELDS */
    var validateRequiredFields = function () {
        var settings = $('body.wp-admin #settings');
        var requiredFieldsWrapper = settings.find('table .required ');
        requiredFieldsWrapper.removeClass('validation-error');

        var invalidFields = [];

        requiredFieldsWrapper.each(function () {
            var requiredFieldWrapper = $(this);
            var requiredField = requiredFieldWrapper.find('input');
            var requiredDependency = requiredFieldWrapper.data('required-dependency');


            if (requiredDependency !== undefined && requiredDependency.length) {
                requiredDependency.forEach(element => {

                    var r_field_name = Object.keys(element)[0];
                    var r_field_value = element[r_field_name];
                    var r_field = settings.find('[name="' + r_field_name + '"]:checked');


                    if (r_field.val() === r_field_value) {
                        var v = requiredField.val().trim();

                        if (!v.length) {
                            var label = requiredField.closest('tr').find('.option-title')
                                .text().trim().replace(':', '');

                            invalidFields.push({
                                label: label,
                                elementJq: requiredField
                            });
                        }
                    }
                });
            }
        });

        return invalidFields;
    };
    /*******************/


    $('body').on('submit', 'form#settings', function () {
        var invalidFields = validateRequiredFields();
        if (invalidFields.length) {

            var labelsList = '';
            var alertMessage = 'These fields cannot be empty:' + "\r\n";

            invalidFields.forEach(element => {
                element.elementJq.addClass('validation-error');
                labelsList += "   - " + element.label + "\r\n";
            });

            alert(alertMessage + labelsList);
            return false;
        }
    });



    $('.bulk_select_all').click(function () {
        if($(this).is(':checked')) {
            $('.bulk_checkbox').each(function () {
                $(this).prop('checked', true);
            });
        }
        else {
            $('.bulk_checkbox').each(function () {
                $(this).prop('checked', false);
            });
        }

    });

    $('#bulk_remove').click(function (e){

        e.preventDefault();

        if ($('#bulk_actions').val() != '') {
            var link = $('#bulk_remove').attr('href');
            var link = link + "&action=" + $('#bulk_actions').val();

            var selected = [];

            $('.bulk_checkbox').each(function () {
                if ($(this).is(':checked')) {
                    selected.push($(this).val());
                }
            });

            var ids = "&ids=";

            for(var i = 0; i < selected.length; i++) {
                ids += selected[i] + ',';
            }

            link = link + ids;

            $('#bulk_remove').attr('href', link);

            window.location.href = link;
        }

    });

    $(document).ready(function (){
        if ($('body').hasClass('cm-pay-per-posts-pro_page_cmppp-subscriptions')) {
            var pages = $('.cmppp-pagination > li');
            var lastPage = parseInt($(pages[pages.length-1]).children().text());

            if (lastPage > 50) {
                lastPage = 50;
            }

            $('.cmppp-page-to').val(lastPage);
        }
    });

    $('.cmppp-export-subscriptions').click(function (e) {

        e.preventDefault();

        function range(start, end) {
            var ans = [];
            for (let i = start; i <= end; i++) {
                ans.push(i);
            }
            return ans;
        }

        var from = $('.cmppp-page-from').val();
        var to = $('.cmppp-page-to').val();

        if (from > to) {
            alert('Value "From" can\'t be bigger than "To"');
            return false;
        }

        if (from < 0 || to < 0) {
            alert('You can input only numbers that bigger than 0');
            return false;
        }

        var range = range(from, to).length;


        if (range > 50) {
            alert('Range can\'t be bigger than 50');
            return false;
        }

        var pages = $('.cmppp-pagination > li');
        var lastPage = parseInt($(pages[pages.length-1]).children().text());

        if (from >= lastPage) {
            alert('Value "From" can\'t be more than the number of existing pages');
            return false;
        }

        if (lastPage < to && from < lastPage) {
            alert('You have only ' + lastPage + ', will be exported from ' + from + ' to ' + lastPage);
        }



        var link = $(this).attr('href');

        link = link + '&from-to=' + from + ',' + to;
        window.location.href = link;

    });


});
