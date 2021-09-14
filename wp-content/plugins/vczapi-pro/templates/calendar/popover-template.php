<?php
/**
 * The template for displaying POPUP information about the events
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/calendar/popover-template.php
 *
 * @author CodeManas
 * @created October 21st, 2020
 * @modified 1.0.0
 * @copyright CodeManas
 */
?>

<div class="vczapi-calendar-tpl">
    {{image_html}}
    <h5>{{title}}</h5>
    <div class="meeting-date">{{meetingDate}}</div>
    <div class="meeting-description">{{eventDescription}}</div>
    <div class="meeting-link">{{meetingLink}}</div>
</div>
<div class="vczapi-pre-loader">
    <img id="loading-image" src="<?php echo VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/frontend/images/ajax-loader.gif'; ?>" alt="Loading..."/>
</div>