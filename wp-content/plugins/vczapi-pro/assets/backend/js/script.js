"use strict";

jQuery(function ($) {
  var vcapi_admin_main = {
    load: function load() {
      vczapi_metabox_recurring.init();
      vczapi_metabox_registrants.init();
      vczapi_recurring_sync.init();
    }
  };
  var vczapi_metabox_registrants = {
    init: function init() {
      this.cacheDOM();
      this.dependenciesInject();
    },
    cacheDOM: function cacheDOM() {
      this.dataTable = $('.vczapi-registrants-table');
      this.overideAddtionRegistrationFields = $('.vczapi-override-registration-fields');
      this.addtionalRegistrationFieldsTbl = $('.vczapi-registration-addtional-fields'); //Event Listeners

      $(document).on('click', '.vczapi-pro-admin-deny-registrant', this.changeRegistrationStatus.bind(this));
      $('.meeting-webinar-registraion-fields-selector').select2();
      this.overideAddtionRegistrationFields.on('click', this.showHideRegistrationAdditionalFields.bind(this));
    },
    dependenciesInject: function dependenciesInject() {
      this.editMeetingsPageDataTable();
    },
    showHideRegistrationAdditionalFields: function showHideRegistrationAdditionalFields(e) {
      if ($(e.currentTarget).is(':checked')) {
        this.addtionalRegistrationFieldsTbl.show();
      } else {
        this.addtionalRegistrationFieldsTbl.hide();
      }
    },
    editMeetingsPageDataTable: function editMeetingsPageDataTable() {
      var post_id = $('#post_ID').val();

      if ($(this.dataTable).length > 0 && post_id) {
        $(this.dataTable).dataTable({
          ajax: {
            url: ajaxurl + '?action=get_registrants_lists&post_id=' + post_id
          },
          dom: 'Bfrtip',
          buttons: ['csv', 'excel', 'print'],
          columns: [{
            data: 'email'
          }, {
            data: 'first_name'
          }, {
            data: 'last_name'
          }, {
            data: 'create_time'
          }, {
            data: 'status'
          }, {
            data: 'change_status'
          }]
        });
      }
    },
    changeRegistrationStatus: function changeRegistrationStatus(e) {
      e.preventDefault();
      var meeting_id = $(e.currentTarget).data('meeting');
      var status = $(e.currentTarget).data('status');
      var post_id = $(e.currentTarget).data('post');
      var registrant_id = $(e.currentTarget).data('registrant-id');
      var registrant_email = $(e.currentTarget).data('registrant-email');
      var type = $(e.currentTarget).data('type');
      var c = confirm("Are you sure you want to deny this registrant ?");

      if (c) {
        $('.vczapi-pro-admin-notices').html('<div class="vczapi-pro-admin-notice-success"><p>Loading.. Please wait..</div>');
        $.post(ajaxurl, {
          action: 'update_registrants_status',
          registrant_id: registrant_id,
          registrant_email: registrant_email,
          post_id: post_id,
          id: meeting_id,
          status: status,
          type: type
        }).done(function (result) {
          if (result.success) {
            $('.vczapi-pro-admin-notices').html('<div class="vczapi-pro-admin-notice-success"><p>' + result.data + '</p></div>');
            location.reload();
          } else {
            $('.vczapi-pro-admin-notices').html('<div class="vczapi-pro-admin-notice-error"><p>' + result.data + '</p></div>');
          }
        });
      }
    }
  };
  var vczapi_metabox_recurring = {
    init: function init() {
      this.cacheDOM();
      this.dependenciesInject();
      this.evntListeners();
    },
    cacheDOM: function cacheDOM() {
      this.endDateCalendarInstance = null;
      this.enableRecurring = $('#vczapi-enable-recurring-meeting');
      this.enableRegistration = $('#vczapi-enable-registration');
      this.registrationType = $('.vczapi-registration-type-on-off');
      this.endDate = $('#vczapi-end-date-time');
      this.repeatInterval = $('#vczapi-repeat-interval');
      this.frequency = $('#vczapi-recurrence-frequency');
      this.noFixedTime = $('.vczapi-recurring-show-hide-no-fixed-time');
      this.registrationSection = $('.vczapi-show-registration-section');
      this.frequencyText = $('.vczapi-repeat-type-text');
      this.recurringData = $('.vczapi-recurring-show-hide');
      this.startDate = $('#datetimepicker');
      this.hidePMI = $('.show-hide-pmi-radio');
      this.pmiSelector = $('#vczapi-use-pmi');
      this.occurrence = {
        weekly: $('.vczapi-weekly-occurrence-show-hide'),
        monthy: $('.vczapi-monthly-occurrence-show-hide')
      };
      this.endDateTime;
      this.weeklyOccurrence = $('input[name="vczapi-weekly-occurrence[]"]');
      this.monthlyOccurenceType = $('input[name="vczapi-monthly-occurrence-type"]');
    },
    dependenciesInject: function dependenciesInject() {
      if ($(this.endDate).length > 0) {
        this.endDateCalendarInstance = $(this.endDate).datetimepicker({
          minDate: 0,
          defaultDate: new Date(),
          timepicker: false,
          format: 'Y-m-d',
          scrollMonth: false
        });
      } //Show start date by default


      if ($(this.enableRecurring).length > 0) {
        if ($(this.enableRecurring).is(':checked') && $(this.frequency).val() === "4") {
          $(this.startDate).closest('tr').hide();
        } else {
          $(this.startDate).closest('tr').show();
        }
      }
    },
    evntListeners: function evntListeners() {
      this.frequency.on('change', this.showRepeatText.bind(this)); //For end_date_time calculations

      this.startDate.on('change', this.updateEndDateCalendar.bind(this));
      this.frequency.on('change', this.updateEndDateCalendar.bind(this));
      this.repeatInterval.on('change', this.updateEndDateCalendar.bind(this));
      this.weeklyOccurrence.on('change', this.updateEndDateCalendar.bind(this));
      this.monthlyOccurenceType.on('change', this.updateEndDateCalendar.bind(this)); //day of month

      $('#vczapi-monthly-occurrence').on('change', this.updateEndDateCalendar.bind(this));
      $('#vczapi-monthly-occurence-week').on('change', this.updateEndDateCalendar.bind(this));
      $('#vczapi-monthly-occurrence-day').on('change', this.updateEndDateCalendar.bind(this));
      this.enableRecurring.on('click', this.showHideRecurringFields.bind(this));
      this.enableRegistration.on('click', this.showHideBasedRegistrationsEnabledSetting.bind(this));
      this.pmiSelector.on('change', this.changeBasedonPMI.bind(this));
    },
    getISODayOfWeek: function getISODayOfWeek(zoomDayOfWeek) {
      //ISOMonday to sunday Monday = 1 , Sunday = 7
      //Zoom : Sunday = 1, Sat = 7
      zoomDayOfWeek = parseInt(zoomDayOfWeek);
      var ISODayOfWeek = 1;

      switch (zoomDayOfWeek) {
        case 1:
          ISODayOfWeek = 7;
          break;

        case 2:
          ISODayOfWeek = 1;
          break;

        case 3:
          ISODayOfWeek = 2;
          break;

        case 4:
          ISODayOfWeek = 3;
          break;

        case 5:
          ISODayOfWeek = 4;
          break;

        case 6:
          ISODayOfWeek = 5;
          break;

        case 7:
          ISODayOfWeek = 6;
          break;
      }

      return ISODayOfWeek;
    },
    updateEndDateCalendar: function updateEndDateCalendar(e) {
      console.log('ues');
      var $el = this.frequency;

      switch ($el.val()) {
        case '1':
          this.updateEndCalendarForDaily();
          break;

        case '2':
          this.updateEndCalendarForWeekly();
          break;

        case '3':
          this.updateEndCalendarForMonthly();
          break;
      }
    },
    updateEndCalendarForDaily: function updateEndCalendarForDaily() {
      var daysInterval = parseInt(this.repeatInterval.val());
      var nextDate = moment(this.startDate.val()).add(daysInterval, 'days').format('YYYY-MM-DD');
      this.endDateCalendarInstance.datetimepicker('setOptions', {
        minDate: nextDate,
        value: nextDate
      });
    },
    getNextOccurenceForWeek: function getNextOccurenceForWeek(startDate, dayINeed, weekInterval) {
      //https://stackoverflow.com/questions/34979051/find-next-instance-of-a-given-weekday-ie-monday-with-moment-js
      var today = moment(startDate).isoWeekday(); // if we haven't yet passed the day of the week that I need:

      if (today <= dayINeed) {
        // then just give me this week's instance of that day
        return moment(startDate).isoWeekday(dayINeed);
      } else {
        // otherwise, give me *next week's* instance of that same day
        return moment(startDate).add(parseInt(weekInterval), 'weeks').isoWeekday(dayINeed);
      }
    },
    updateEndCalendarForWeekly: function updateEndCalendarForWeekly() {
      //returns minimum first occurrence
      var weekInterval = parseInt(this.repeatInterval.val());
      var startDate = this.startDate.val();
      var selectedDaysOfWeek = [];
      var getISODayOfWeek = this.getISODayOfWeek;
      var getNextOccurenceForWeek = this.getNextOccurenceForWeek; //console.log(getISODayOfWeek);

      $('input[name="vczapi-weekly-occurrence[]"]:checked').each(function () {
        var ISODateOfWeek = getISODayOfWeek($(this).val()); //console.log(ISODateOfWeek);

        selectedDaysOfWeek.push(ISODateOfWeek);
      }); //can't be empty prolly

      if (selectedDaysOfWeek.length === 0 && selectedDaysOfWeek !== 'undefined') {
        return false;
      }

      var probableEndDates = [];
      $(selectedDaysOfWeek).each(function (index, value) {
        var endDate = getNextOccurenceForWeek(startDate, value, weekInterval);
        probableEndDates.push(endDate);
        console.log('list of dates', endDate.format('YYYY-MM-DD'));
      });
      var earliestNextDate = moment.min(probableEndDates);
      this.endDateCalendarInstance.datetimepicker('setOptions', {
        minDate: earliestNextDate.format('YYYY-MM-DD'),
        value: earliestNextDate.format('YYYY-MM-DD')
      });
    },
    updateEndCalendarForMonthly: function updateEndCalendarForMonthly() {
      var startDate = this.startDate.val();
      var monthInterval = parseInt(this.repeatInterval.val());
      var selectedType = $('input[name="vczapi-monthly-occurrence-type"]:checked').val(); //1 = By Day of Month, 2 = By Sunday / Friday of Month

      var possibleEndDate = moment(startDate).add(monthInterval, 'M');

      if (selectedType === '1') {
        //@todo need to handle february
        var selectedDate = parseInt($('#vczapi-monthly-occurrence').val()); //adding the interval
        // 1 is february confusing i know but that's what it is

        var futureMonth = parseInt(possibleEndDate.month());
        console.log(futureMonth);

        if (futureMonth === 1 && (selectedDate === 31 || selectedDate === 30 || selectedDate === 29)) {
          this.endDateCalendarInstance.datetimepicker('setOptions', {
            minDate: possibleEndDate.endOf('month').format('YYYY-MM-DD'),
            value: possibleEndDate.endOf('month').format('YYYY-MM-DD')
          });
        } else if ((futureMonth === 3 || futureMonth === 5 || futureMonth === 8 || futureMonth === 10) && selectedDate === 31) {
          this.endDateCalendarInstance.datetimepicker('setOptions', {
            minDate: possibleEndDate.endOf('month').format('YYYY-MM-DD'),
            value: possibleEndDate.endOf('month').format('YYYY-MM-DD')
          });
        } else {
          this.endDateCalendarInstance.datetimepicker('setOptions', {
            minDate: possibleEndDate.date(selectedDate).format('YYYY-MM-DD'),
            value: possibleEndDate.date(selectedDate).format('YYYY-MM-DD')
          });
        }
      } else if (selectedType === '2') {
        // monthlyOccurrenceType = First(1), Second(2), Third(3), Fourth(4), Last(-1)
        // var timeOfMonth = $('#vczapi-monthly-occurence-week').val();
        // var dayOfWeek = $('#vczapi-monthly-occurrence-day').val();
        this.endDateCalendarInstance.datetimepicker('setOptions', {
          minDate: possibleEndDate.endOf('M').format('YYYY-MM-DD'),
          value: possibleEndDate.endOf('M').format('YYYY-MM-DD')
        });
      }
    },
    showRepeatText: function showRepeatText(e) {
      var value = $(e.currentTarget).val();
      var repeatInterval = '';

      if (value === "1") {
        $(this.frequencyText).text(recurring_strings.repeat_every_day);
        this.occurrence.weekly.hide();
        this.occurrence.monthy.hide();
        this.noFixedTime.show();
        $(this.startDate).closest('tr').show();
        this.hidePMI.hide();

        for (var i = 1; i <= 15; i++) {
          repeatInterval += '<option value="' + i + '">' + i + '</option>';
        }

        this.repeatInterval.html(repeatInterval);
      } else if (value === "2") {
        $(this.frequencyText).text(recurring_strings.repeat_every_week);
        this.occurrence.weekly.show();
        this.occurrence.monthy.hide();
        this.noFixedTime.show();
        $(this.startDate).closest('tr').show();
        this.hidePMI.hide();

        for (var i = 1; i <= 12; i++) {
          repeatInterval += '<option value="' + i + '">' + i + '</option>';
        }

        this.repeatInterval.html(repeatInterval);
      } else if (value === "3") {
        $(this.frequencyText).text(recurring_strings.repeat_every_month);
        this.occurrence.weekly.hide();
        this.occurrence.monthy.show();
        this.noFixedTime.show();
        $(this.startDate).closest('tr').show();
        this.hidePMI.hide();

        for (var i = 1; i <= 3; i++) {
          repeatInterval += '<option value="' + i + '">' + i + '</option>';
        }

        this.repeatInterval.html(repeatInterval);
      } else if (value === "4") {
        this.occurrence.weekly.hide();
        this.occurrence.monthy.hide();
        this.noFixedTime.hide();
        $(this.startDate).closest('tr').hide();
        this.hidePMI.show();
      }
    },
    showHideRecurringFields: function showHideRecurringFields(e) {
      var frequency = $(this.frequency).val(); //Remove check for registration as there are some restrictions for Registration when certain recurring types are selected

      if ($(e.currentTarget).is(':checked')) {
        $(this.recurringData).show();

        if (this.enableRegistration.is(':checked')) {
          this.registrationType.show();
        }

        if (frequency === "1") {
          this.hidePMI.hide();
        } else if (frequency === "2") {
          this.occurrence.weekly.show();
          $(this.startDate).closest('tr').show();
          this.hidePMI.hide();
        } else if (frequency === "3") {
          this.occurrence.monthy.show();
          $(this.startDate).closest('tr').show();
          this.hidePMI.hide();
        } else if (frequency === "4") {
          this.occurrence.weekly.hide();
          this.occurrence.monthy.hide();
          this.noFixedTime.hide();
          $(this.startDate).closest('tr').hide();
          this.hidePMI.show();
        }
      } else {
        this.registrationType.hide();
        this.recurringData.hide();
        $(this.startDate).closest('tr').show();
        this.hidePMI.show();

        if (frequency === "2") {
          this.occurrence.weekly.hide();
        } else if (frequency === "3") {
          this.occurrence.monthy.hide();
        } else if (frequency === "4") {
          this.occurrence.weekly.hide();
          this.occurrence.monthy.hide();
          this.registrationSection.show();
        }
      }
    },
    showHideBasedRegistrationsEnabledSetting: function showHideBasedRegistrationsEnabledSetting(e) {
      if ($(e.currentTarget).is(':checked')) {
        $(this.pmiSelector).val('1'); //Set Value to Auto Generated

        if (this.enableRecurring.is(':checked')) {
          this.registrationType.show();
        }
      } else {
        this.registrationType.hide();
      }
    },
    changeBasedonPMI: function changeBasedonPMI(e) {
      //IF PMI
      if ($(e.currentTarget).val() === '2') {
        this.enableRegistration.removeAttr('checked');
      }
    }
  };
  var vczapi_recurring_sync = {
    init: function init() {
      this.cacheDOM();
      this.evntListeners();
    },
    cacheDOM: function cacheDOM() {
      this.notify = $('.vczapi-pro-notify');
      this.syncMethod = $('.vczapi-pro-sync-method');
      this.showOnMeetingIDSelected = $('.vczapi-pro-show-sync-method-meetingid');
      this.showOnUserSelected = $('.vczapi-pro-show-sync-method-user');
      this.userID = $('.vczapi-pro-sync-by-user');
      this.syncType = $('.vczapi-pro-sync-type'); //MEETING ID Fields

      this.selectUser = $('.vczapi-pro-sync-meeting-id-user-id');
      this.meeting_id = $('.vczapi-pro-sync-meeting-id');
      this.syncViaMeetingIDBtn = $('#vczapi-pro-sync-via-meeting-id');
    },
    evntListeners: function evntListeners() {
      this.syncMethod.on('change', this.syncTypeSelected.bind(this));
      this.syncViaMeetingIDBtn.on('click', {
        type: 'sync_via_id'
      }, this.syncViaMeetingID.bind(this));
      this.userID.on('change', this.fetchRecurringMeetings.bind(this));
      this.syncType.on('change', this.changeSyncType.bind(this));
      $(document).on('click', '.vczapi-pro-sync-meeting-by-id', {
        type: 'sync_via_user'
      }, this.syncViaMeetingID.bind(this));
    },
    syncTypeSelected: function syncTypeSelected(e) {
      e.preventDefault();

      if (parseInt($(e.currentTarget).val()) === 1) {
        this.showOnMeetingIDSelected.show();
        this.showOnUserSelected.hide();
      } else {
        this.showOnMeetingIDSelected.hide();
        this.showOnUserSelected.show();
      }
    },
    syncViaMeetingID: function syncViaMeetingID(e) {
      var user_id = '';
      var meeting_id = '';
      var type = 'meeting';

      if (e.data.type === "sync_via_user") {
        user_id = $(e.currentTarget).data('user');
        meeting_id = $(e.currentTarget).data('meeting');
        type = $(e.currentTarget).data('type');
      } else {
        user_id = $(this.selectUser).val();
        meeting_id = $(this.meeting_id).val();
        type = $('.vczapi-pro-sync-type option:selected').val();
      }

      if (meeting_id !== '' && user_id !== '') {
        var postData = {
          'host_id': user_id,
          'meeting_id': meeting_id,
          'meeting_type': type,
          'action': 'sync_meeting_id',
          'type': 'check'
        };
        this.checkBeforeSync(postData);
      } else {
        $(this.notify).html('<p>' + vczapi_pro_i10n.required + '</p>').addClass('vczapi-error');
        this.animate('.vczapi-error');
      }
    },
    checkBeforeSync: function checkBeforeSync(postData) {
      var that = this;
      $(that.notify).html('<p>Fetching.. Please wait..</p>');
      $.post(ajaxurl, postData).done(function (response) {
        if (response.success) {
          that.sync(postData).removeClass('vczapi-error').addClass('vczapi-success');
          that.animate('.vczapi-success');
        } else {
          $(that.notify).html('<p>' + response.data + '</p>').addClass('vczapi-error');
          that.animate('.vczapi-error');
        }
      });
    },
    sync: function sync(postData) {
      var that = this;
      postData.type = 'sync';
      $.post(ajaxurl, postData).done(function (response) {
        if (response.success) {
          $(that.notify).html('<p>' + response.data.msg + '</p>').removeClass('vczapi-error').addClass('vczapi-success');
          that.animate('.vczapi-success');
        } else {
          $(that.notify).html('<p>' + response.data + '</p>').addClass('vczapi-error');
          that.animate('.vczapi-error');
        }
      });
    },
    changeSyncType: function changeSyncType(e) {
      var user_id = $('.vczapi-pro-sync-by-user option:selected').val();
      var sync_method = $("input[name='sync_method']:checked").val();

      if (sync_method == '2') {
        this.fetchRecurringMeetings(e, user_id);
      }
    },
    fetchRecurringMeetings: function fetchRecurringMeetings(e, user_id) {
      e.preventDefault();
      var that = this;

      if (!user_id) {
        user_id = $(e.currentTarget).val();
      }

      var postData = {
        action: 'sync_user',
        type: 'fetch'
      };
      var results = $('.vczapi-pro-user-results');
      results.html('<p>' + vczapi_sync_i10n.before_sync + '</p>');
      var sync_type = $('.vczapi-pro-sync-type').val();
      $.post(ajaxurl, postData).done(function (response) {
        //Success
        if (response.success) {
          results.html('<p>' + response.data + '</p>');
          $('.vczapi-pro-sync-meeting-table').DataTable({
            serverSide: true,
            processing: true,
            searching: false,
            ajax: {
              url: ajaxurl,
              type: 'POST',
              data: function data(d) {
                d.action = 'sync_user';
                d.type = 'check';
                d.user_id = user_id;
                d.mtg_type = sync_type;
              }
            },
            "columnDefs": [{
              "targets": 0,
              "orderable": false
            }]
          });
        } else {
          results.html('<p>' + response.data + '</p>').addClass('vczapi-error');
          that.animate('.vczapi-error');
        }
      });
    },
    animate: function animate(dom) {
      $('html, body').animate({
        scrollTop: $(dom).offset().top - 20
      }, 500);
    }
  };
  vcapi_admin_main.load();
});