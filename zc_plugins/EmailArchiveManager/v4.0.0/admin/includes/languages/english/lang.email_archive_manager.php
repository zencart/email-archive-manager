<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 17  Plugin version 4.0 $
 */

$define = [
    'HEADING_TITLE' => 'Email Archive Manager',

    'HEADING_SEARCH_INSTRUCT' =>  'You may search by any combination of the following criteria...',

    'HEADING_MODULE_SELECT' =>  'Filter by module:',
    'HEADING_SEARCH_TEXT' =>  'Search for text:',
    'HEADING_SEARCH_TEXT_FILTER' =>  'Current search filter: ',
    'HEADING_SEARCH_ERROR' => 'Search for error',
    'HEADING_START_DATE' =>  'Start Date:',
    'HEADING_END_DATE' =>  'End Date:',
    'HEADING_PRINT_FORMAT' =>  'Display results in print format?',
    'HEADING_SHOW_ERRORS' => 'Display Errors (if any)',
    'HEADING_SHOW_ERRORS_OFF' => 'Off',
    'HEADING_SHOW_ERRORS_ALL_INC' => 'All Incl Error',
    'HEADING_SHOW_ERRORS_ONLY' => 'Only With Error',
    'HEADING_TRIM_INSTRUCT' =>  'Delete email older than...',

    'TOOLTIP_SEARCH_TEXT' => 'Searches in: Recipient Name and Address, email Subject, email HTML and TEXT content.',
    'TOOLTIP_SHOW_ERRORS' => 'Adds an \'Error Info\' column to the table below and filters results accordingly.',
    'TOOLTIP_SEARCH_ERROR' => 'Searches in Error Info and turns on \'Display Errors\'.',

    'HEADING_TEXT_INSTEAD' =>  'Showing TEXT for safety; HTML may be malicious.',

    'TABLE_HEADING_ARCHIVE_ID' => 'Archive ID',
    'TABLE_HEADING_EMAIL_DATE' =>  'Date Sent',
    'TABLE_HEADING_CUSTOMERS_NAME' =>  'Customer Name',
    'TABLE_HEADING_CUSTOMERS_EMAIL' =>  'Email Address',
    'TABLE_HEADING_EMAIL_FORMAT' =>  'Format',
    'TABLE_HEADING_EMAIL_SUBJECT' =>  'Subject',
    'TABLE_HEADING_EMAIL_ERRORINFO' => 'Error Info',
    'TABLE_FORMAT_TEXT' =>  'TEXT',
    'TABLE_FORMAT_HTML' =>  'HTML',

    'TEXT_TRIM_ARCHIVE' =>  'Trim email archive...',
    'TEXT_ARCHIVE_ID' =>  'Archive #',
    'TEXT_ALL_MODULES' =>  'All Modules',
    'TEXT_DISPLAY_NUMBER_OF_EMAILS' =>  'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> emails)',
    'TEXT_EMAIL_MODULE' =>  'Module: ',
    'TEXT_EMAIL_TO' =>  'To: ',
    'TEXT_EMAIL_FROM' =>  'From: ',
    'TEXT_EMAIL_DATE_SENT' =>  'Sent: ',
    'TEXT_EMAIL_SUBJECT' =>  'Subject: ',
    'TEXT_EMAIL_EXCERPT' =>  'Message Excerpt:',
    'TEXT_EMAIL_ERRORINFO' => 'Error Information:',
    'TEXT_EMAIL_NUMBER' =>  'Email #',

    'TEXT_NO_ARCHIVE_RECORDS_FOUND' =>  'No matching records found.',

    'RADIO_1_MONTH' =>  ' 1 month',
    'RADIO_6_MONTHS' =>  ' 6 months',
    'RADIO_1_YEAR' =>  ' 12 months',
    'TEXT_RESEND_PREFIX' => 'Resend: ',
    'TRIM_CONFIRM_WARNING' =>  'Warning: This will permanently remove email from the archive.<br>Are you sure?',
    'POPUP_CONFIRM_RESEND' =>  'Are you sure you want to resend this message?',
    'POPUP_CONFIRM_DELETE' =>  'Are you sure you want to delete this message?',
    'SUCCESS_TRIM_ARCHIVE' =>  'Success: Email older than %s has been removed',
    'SUCCESS_EMAIL_RESENT' =>  'Success: Email #%s has been resent to %s',

    'IMAGE_ICON_HTML' =>  ' View HTML Message ',
    'IMAGE_ICON_TEXT' =>  ' View Text Message ',
    'IMAGE_ICON_RESEND' =>  ' Resend Message ',
    'IMAGE_ICON_EMAIL' =>  ' Email Recipient ',
    'IMAGE_ICON_DELETE' =>  ' Delete Message ',

    'SEND_NEW_EMAIL' =>  'Send New Email',
    'BUTTON_SEARCH' =>  'Search Archive',
    'BUTTON_TRIM_CONFIRM' =>  'Delete email',
    'BUTTON_CANCEL' =>  'Cancel',
];

return $define;
