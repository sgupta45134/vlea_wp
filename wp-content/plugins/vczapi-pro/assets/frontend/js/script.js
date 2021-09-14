"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  /*!
   * Get an object value from a specific path
   * (c) 2018 Chris Ferdinandi, MIT License, https://gomakethings.com
   * @param  {Object}       obj  The object
   * @param  {String|Array} path The path
   * @param  {*}            def  A default value to return [optional]
   * @return {*}                 The value
   */
  var get = function get(obj, path, def) {
    /**
     * If the path is a string, convert it to an array
     * @param  {String|Array} path The path
     * @return {Array}             The path array
     */
    var stringToPath = function stringToPath(path) {
      // If the path isn't a string, return it
      if (typeof path !== 'string') return path; // Create new array

      var output = []; // Split to an array with dot notation

      path.split('.').forEach(function (item) {
        // Split to an array with bracket notation
        item.split(/\[([^}]+)\]/g).forEach(function (key) {
          // Push to the new array
          if (key.length > 0) {
            output.push(key);
          }
        });
      });
      return output;
    }; // Get the path as an array


    path = stringToPath(path); // Cache the current object

    var current = obj; // For each item in the path, dig into the object

    for (var i = 0; i < path.length; i++) {
      // If the item isn't found, return the default (or null)
      if (!current[path[i]]) return def; // Otherwise, update the current  value

      current = current[path[i]];
    }

    return current;
  };
  /*!
   * Replaces placeholders with real content
   * Requires get() - https://vanillajstoolkit.com/helpers/get/
   * (c) 2019 Chris Ferdinandi, MIT License, https://gomakethings.com
   * @param {String} template The template string
   * @param {String} local    A local placeholder to use, if any
   */


  var placeholders = function placeholders(template, data) {
    'use strict'; // Check if the template is a string or a function

    template = typeof template === 'function' ? template() : template;
    if (['string', 'number'].indexOf(_typeof(template)) === -1) throw 'PlaceholdersJS: please provide a valid template'; // If no data, return template as-is

    if (!data) return 'template'; // Replace our curly braces with data

    template = template.replace(/\{\{([^}]+)\}\}/g, function (match) {
      // Remove the wrapping curly braces
      match = match.slice(2, -2); // Get the value

      var val = get(data, match); // Replace
      //slight modification sent empty

      if (!val) return '';
      return val;
    });
    return template;
  };
  /**
   * Common Frontend
   * @type {{init: init, _cacheDOM: _cacheDOM, _evntListeners: _evntListeners, _isValidEmailAddress: (function(*=): boolean), toggleAllOccurrences: toggleAllOccurrences}}
   */


  var vczapi_pro_frontend = {
    init: function init() {
      this._cacheDOM();

      this._evntListeners();
    },
    _cacheDOM: function _cacheDOM() {
      this.$allOccurrences = $('.zvc-all-occurrences');

      if (this.$allOccurrences.length > 0) {
        this.$allOccurrencesToggler = this.$allOccurrences.find('.zvc-all-occurrences__toggle-button');
        this.$allOccurrencesList = this.$allOccurrences.find('.zvc-all-occurrences__list');
      }
    },
    _evntListeners: function _evntListeners() {
      if (this.$allOccurrences.length > 0) {
        this.$allOccurrencesToggler.on('click', this.toggleAllOccurrences.bind(this));
      }

      if ($('.vczapi-pro-datatable-render').length > 0) {
        $('.vczapi-pro-datatable-render').dataTable({
          order: [1, "asc"],
          responsive: true,
          language: vczapi_dt_i18n
        });
      }
    },
    toggleAllOccurrences: function toggleAllOccurrences(e) {
      e.preventDefault();
      this.$allOccurrencesList.toggle();
    },
    _isValidEmailAddress: function _isValidEmailAddress(emailAddress) {
      var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
      return pattern.test(emailAddress);
    }
  };
  /**
   * Registration INIT
   * @type {{init: init, _cacheDOM: _cacheDOM, _evntListeners: _evntListeners, registerUser: registerUser, _validateFormData: (function(*, *): boolean)}}
   */

  var vczapi_registration = {
    init: function init() {
      this._cacheDOM();

      this._evntListeners();
    },
    _cacheDOM: function _cacheDOM() {},
    _evntListeners: function _evntListeners() {
      $(document).on('submit', '#vczapi-pro-registration-form', this.registerUser.bind(this));
    },
    registerUser: function registerUser(e) {
      e.preventDefault();
      var formData = $(e.currentTarget).serializeArray();
      var notice = $('.vczapi-pro-registration-notice');

      var passValidation = this._validateFormData(e, formData); //Validation Succesfull


      if (passValidation) {
        formData.push({
          name: 'action',
          value: 'register_user'
        });
        notice.html('<p>Loading. Please wait..</p>').show();
        var $registrationForm = $('.vczapi-pro-registration-form');
        $.post(vczapi_pro.ajaxurl, formData).done(function (result) {
          if (result.success === false) {
            notice.html('<p class="vczapi-pro-registration-data-result-failed">' + result.data + '</p>').show();
            $('html, body').animate({
              scrollTop: $('.vczapi-pro-registration-data-result-failed').offset().top - 100
            }, 500);
          }

          if (result.success === true) {
            $($registrationForm).html('<p class="vczapi-pro-registration-data-result">' + result.data + '</p>').show(); //Hide the Loading notice

            notice.html('').hide();
          }
        });
      }
    },
    _validateFormData: function _validateFormData(e, formData) {
      var passed = true;
      var first_name = $(e.currentTarget).find('#first_name'); //Validation Start

      if (first_name.val() === "") {
        if (!$(first_name).next('.error').length) {
          $(first_name).css('border-color', 'red').after('<p class="error">' + vczapi_pro.first_name_required + '</p>');
          passed = false;
        }
      } else {
        $(first_name).css('border-color', '#ddd').next('.error').remove();
      }

      var last_name = $(e.currentTarget).find('#last_name');

      if (last_name.val() === "") {
        if (!$(last_name).next('.error').length) {
          $(last_name).css('border-color', 'red').after('<p class="error">' + vczapi_pro.last_name_required + '</p>');
        }

        passed = false;
      } else {
        $(last_name).css('border-color', '#ddd').next('.error').remove();
      }

      var email = $(e.currentTarget).find('#email_address');

      if (email.val() === "" || !vczapi_pro_frontend._isValidEmailAddress(email.val())) {
        if (!$(email).next('.error').length) {
          $(email).css('border-color', 'red').after('<p class="error">' + vczapi_pro.email_required + '</p>');
        }

        passed = false;
      } else {
        $(email).css('border-color', '#ddd').next('.error').remove();
      }

      return passed;
    }
  };
  /**
   * Frontend Meeting Create Update Deleted.
   * @type {{init: init, _cacheDOM: _cacheDOM, createMeeting: createMeeting, meetingPassword: meetingPassword, dataValidation: (function(*): boolean), _loadDependencies: _loadDependencies}}
   */

  var vczapi_pro_frontend_meeting_CRUD = {
    init: function init() {
      this._cacheDOM();

      this._loadDependencies();
    },
    _cacheDOM: function _cacheDOM() {
      this.select2 = $('.vczapi-pro-form-control-select2');
      this.createMeetingForm = $('#vczapi-pro-frontend-meeting-create-form');
      this.startTimePicker = $('#vczapi-pro-meeting-start-time');
      this.notificationHolder = $('.vczapi-api-meeting-create-notifications'); //Datatable DOM

      this.authrMeetingListTbl = $('#vczapi-pro-frontend-author-meeting-list-table');
      this.selectAllMeetings = $('.vczapi-pro-frontend-author-meeting-select-all');
      this.selectCertainMeetings = $('.vczapi-pro-frontend-author-meetings');
    },
    _loadDependencies: function _loadDependencies() {
      //for Select fields
      if (this.select2.length > 0) {
        this.select2.select2();
      } //For DatePicker


      if (this.startTimePicker.length > 0) {
        var d = new Date();
        var month = d.getMonth() + 1;
        var day = d.getDate();
        var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
        var output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + ' ' + time;
        var start_date_check = this.startTimePicker.data('existingdate');

        if (start_date_check) {
          output = start_date_check;
        }

        this.startTimePicker.datetimepicker({
          value: output,
          step: 30,
          minDate: 0,
          format: 'Y-m-d H:i',
          mask: true
        });
      } //Author meeting list table


      if ($(this.authrMeetingListTbl).length > 0) {
        var datatable_data = false; //IF this is a registrants listing page

        if (typeof vczapi_view !== 'undefined' && vczapi_view.list_registrants) {
          datatable_data = {
            ajaxurl: vczapi_pro.ajaxurl + '?action=get_author_meeting_list&list=registrants&pg=' + vczapi_pro.current_page,
            columns: [{
              data: 'title'
            }, {
              data: 'start_date'
            }, {
              data: 'meeting_id'
            }, {
              data: 'type'
            }, {
              data: 'view_registrants'
            }],
            colmDefs: false,
            order: [1, "desc"]
          };
        } else {
          datatable_data = {
            ajaxurl: vczapi_pro.ajaxurl + '?action=get_author_meeting_list&pg=' + vczapi_pro.current_page,
            columns: [{
              data: 'sort'
            }, {
              data: 'title'
            }, {
              data: 'start_date'
            }, {
              data: 'meeting_id'
            }, {
              data: 'type'
            }, {
              data: 'start_link'
            }],
            colmDefs: [{
              "render": function render(data, type, row) {
                return data + '<div class="vczapi-pro-frontend-mtg-actions"><a href="' + row.edit_link + '" class="vczapi-pro-frontend-mtg-actions-edit">Edit</a> | <a href="' + row.permalink + '" class="vczapi-pro-frontend-mtg-actions-view">View</a></div>';
              },
              "targets": 1
            }, {
              "render": function render(data, type, row) {
                return '<input type="checkbox" name="meeting-select[]" value="' + row.post_id + '" class="vczapi-pro-frontend-author-meetings vczapi-pro-frontend-author-meeting-' + row.post_id + '">';
              },
              "targets": 0,
              "orderable": false
            }],
            order: [2, "desc"]
          };
        }

        $(this.authrMeetingListTbl).dataTable({
          ajax: {
            url: datatable_data.ajaxurl
          },
          columns: datatable_data.columns,
          order: [datatable_data.order],
          columnDefs: datatable_data.colmDefs,
          responsive: true,
          language: vczapi_dt_i18n
        });
      } //On Create Meeting Submit


      this.createMeetingForm.on('submit', this.createMeeting.bind(this)); //For Password field

      $('.vczapi-pro-meeting-password').on('keypress', this.meetingPassword.bind(this));
      this.selectAllMeetings.on('click', this.selectBulkMtgs.bind(this));
      $(document).on('click', this.selectCertainMeetings, this.selectAMeetingForAction.bind(this));
      $(document).on('click', '.vczapi-pro-view-registrants', this.viewRegistrantsCallback.bind(this));
      $(document).on('click', '.vczapi-modal-close', this.closeRegistrantsPopup.bind(this));
    },
    dataValidation: function dataValidation(e) {
      var notify = this.notificationHolder;
      var validated = true;
      $(e.currentTarget).find('input, select').each(function (i, r) {
        if ($(r).data('required') === true) {
          var elemName = $(r).attr('name');

          if (r.value === null || r.value === "") {
            if (!$(".vczapi-pro-" + elemName + "-validate-error").length) {
              $(r).parent().addClass('vczapi-pro-field-validate-error');
            }

            validated = false;
          } else {
            $(r).parent().removeClass('vczapi-pro-field-validate-error');
            $('.vczapi-pro-' + elemName + '-validate-error').remove();
          }
        }
      });

      if (validated) {
        notify.html('');
      } else {
        notify.html('<p class="vczapi-api-meeting-create-notifications-error">Required fields are missing !</p>');
        $('html, body').animate({
          scrollTop: $(".vczapi-api-meeting-create-notifications-error").offset().top - 80
        }, 500);
      }

      return validated;
    },
    viewRegistrantsCallback: function viewRegistrantsCallback(e) {
      e.preventDefault();
      var post_id = $(e.currentTarget).data('post');
      var postData = {
        post_id: post_id,
        action: 'get_meeting_registrants'
      };
      $('.vczapi-modal').html('<p class="vczapi-modal-loader">' + vczapi_view.loading + '</p>').show();
      $.get(vczapi_pro.ajaxurl, postData).done(function (response) {
        $('.vczapi-modal').html(response.data).show();
      });
    },
    closeRegistrantsPopup: function closeRegistrantsPopup(e) {
      e.preventDefault();
      $('.vczapi-modal-content').remove();
      $('.vczapi-modal').hide();
    },
    createMeeting: function createMeeting(e) {
      e.preventDefault();
      var notify = this.notificationHolder; //Validate all data first

      if (this.dataValidation(e)) {
        var postData = $(e.currentTarget).serialize() + '&action=create_meeting_frontend';
        var redirect_to = $(e.currentTarget).find('input[name="redirect_to"]').val();
        $('.vczapi-pro-form-fields').remove();
        notify.html('<p class="vczapi-api-meeting-create-notifications-success">Submitting Form.. Please wait..</p>');
        $.post(vczapi_pro.ajaxurl, postData).done(function (result) {
          console.log(result);

          if (result.success === true) {
            notify.html('<p class="vczapi-api-meeting-create-notifications-success">' + result.data + '</p>');
            window.location.href = redirect_to;
          } else {
            notify.html('<p class="vczapi-api-meeting-create-notifications-error">' + result.data + '</p>');
            location.reload();
          }
        });
      }
    },
    meetingPassword: function meetingPassword(e) {
      if (!/([a-zA-Z0-9])+/.test(String.fromCharCode(e.which))) {
        return false;
      }

      var text = $(this).val();
      var maxlength = $(this).data('maxlength');

      if (maxlength > 0) {
        $(this).val(text.substr(0, maxlength));
      }
    },
    selectBulkMtgs: function selectBulkMtgs(e) {
      if ($(e.currentTarget).is(":checked")) {
        $('.vczapi-pro-frontend-author-meetings').prop("checked", true);

        if ($('.vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete').length === 0) {
          $("#vczapi-pro-frontend-author-meeting-list-table_filter").append("<div id='vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete' class='vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete'><a href='javascript:void(0);'>Move to Trash</a></div>");
        }
      } else {
        $('.vczapi-pro-frontend-author-meetings').prop("checked", false);
        $("#vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete").remove();
      }
    },
    selectAMeetingForAction: function selectAMeetingForAction(e) {
      var check_selected = true;
      $('.vczapi-pro-frontend-author-meetings').each(function (i, r) {
        if ($(r).is(':checked')) {
          check_selected = false;
          return false;
        }
      });

      if (check_selected) {
        $("#vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete").remove();
      } else if ($('.vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete').length === 0) {
        $("#vczapi-pro-frontend-author-meeting-list-table_filter").append("<div id='vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete' class='vczapi-pro-frontend-author-meeting-list-table_filter-bulk_delete'><a href='javascript:void(0);'>Move to Trash</a></div>");
      }
    }
  };
  /**
   * Calender INIT
   * @type {{init: init, renderCalendar: renderCalendar, cacheDOM: cacheDOM}}
   */

  var vczapiFullCalendar = {
    init: function init() {
      this.cacheDOM();

      if (this.$calendarEl.length > 0) {
        this.renderCalendar();
      }
    },
    cacheDOM: function cacheDOM() {
      this.meetings = [];
      this.$calendarEl = $('.vczapi-pro-calendar');
      window.tippyInstances = [];
    },
    renderCalendar: function renderCalendar() {
      var lang = document.documentElement.lang;
      this.$calendarEl.each(function (i, obj) {
        var calendarInstance = $(this);
        var calendar_default_view = calendarInstance.data('calendar_default_view');
        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        var calendarOptions = {
          initialView: calendar_default_view,
          initialDate: new Date(),
          locale: lang,
          events: {
            url: vczapi_pro.ajaxurl + '?action=vczapi_get_calendar_meetings',
            extraParams: {
              author: calendarInstance.data('author'),
              show: calendarInstance.data('show'),
              timezone: timezone
            }
          },
          loading: function loading(isLoading) {
            var $currentCalendar = $(this.el);

            if (isLoading) {
              $currentCalendar.parent('.vczapi-pro-calendar-container').find('.vczapi-pre-loader').show();
            } else {
              $currentCalendar.parent('.vczapi-pro-calendar-container').find('.vczapi-pre-loader').hide();
            }
          },
          eventClick: function eventClick(info) {
            tippyInstances.forEach(function (instance) {
              instance.destroy();
            });
            tippyInstances.length = 0; // clear it

            var templateEl = $(this.el).parent().find('.vczapi-calendar-tpl');
            var template = templateEl[0];
            var template_string = template.innerHTML;
            var event = info.event;
            var eventExtendedProps = event.extendedProps;
            var data = {
              title: event.title
            };

            for (var property in eventExtendedProps) {
              if (eventExtendedProps.hasOwnProperty(property)) {
                data[property] = eventExtendedProps[property];
              }
            }

            var content = placeholders(template_string, data);
            var tooltip = tippy(info.el, {
              content: '<div class="vczapi-calendar-tooltip">' + content + '</div>',
              interactive: true,
              allowHTML: true,
              theme: 'light',
              trigger: 'click',
              zIndex: 99999,
              showOnCreate: true,
              popperOptions: {
                strategy: 'fixed',
                container: 'body',
                arrow: false
              }
            });
            window.tippyInstances = tippyInstances.concat(tooltip);
          },
          views: {
            dayGridMonth: {
              dayMaxEventRows: 3,
              eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: 'short'
              }
            }
          }
        };
        var show_calendar_views = calendarInstance.data('show_calendar_views');

        if (show_calendar_views === 'yes') {
          calendarOptions.headerToolbar = {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
          };
        }

        var calendar = new FullCalendar.Calendar(calendarInstance[0], calendarOptions); //console.log(calendar);

        calendar.render();
      });
    }
  };
  $(function () {
    vczapi_pro_frontend.init();
    vczapi_registration.init();
    vczapi_pro_frontend_meeting_CRUD.init();
    vczapiFullCalendar.init();
  });
})(jQuery);