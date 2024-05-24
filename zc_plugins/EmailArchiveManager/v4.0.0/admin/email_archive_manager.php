<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 17  Plugin version 4.0 $
 *
 * @var messageStack $messageStack
 * @var queryFactory $db
 */

/**
 * Email Archive Manager
*/

define('SUBJECT_SIZE_LIMIT', 200);
define('MESSAGE_SIZE_LIMIT', 550);
define('MESSAGE_LIMIT_BREAK', '&hellip;');

require 'includes/application_top.php';

$max_records_per_page = (int)MAX_DISPLAY_SEARCH_RESULTS_ORDERS;

function zen_get_email_archive_search_query(
    string $search_text,
    string $sd_raw,
    string $ed_raw,
    string $search_module,
    string $only_errors
): string
{
    $select_fields = [
        'archive_id',
        'email_to_name',
        'email_to_address',
        'email_from_name',
        'email_from_address',
        'email_subject',
        'email_html',
        'email_text',
        'date_sent',
        'module',
        'errorinfo',
    ];
    $where_clauses = [];

    if ($only_errors) {
        $where_clauses[] = 'errorinfo IS NOT NULL';
    }

    if (!empty($sd_raw)) {
        $where_clauses[] = "date_sent >= '" . zen_db_input($sd_raw) . "'";
    }

    if (!empty($ed_raw)) {
        $where_clauses[] = "date_sent <= DATE_ADD('" . zen_db_input($ed_raw) . "', INTERVAL 1 DAY)";
    }

    if (!empty($search_text)) {
        $keywords = zen_db_input(zen_db_prepare_input($search_text));
        $where_clauses[] = implode(' OR ', array_map(
            static fn($field) => "$field LIKE '%$keywords%'",
                ['email_to_address', 'email_subject', 'email_html', 'email_text', 'email_to_name', 'errorinfo']
            )
        );
    }

    if (!empty($search_module)) {
        $where_clauses[] = "module = '" . zen_db_input($search_module) . "'";
    }

    // Build the SQL
    $archive_search_sql = 'SELECT ' . implode(', ', $select_fields) . ' FROM ' . TABLE_EMAIL_ARCHIVE . " \n";

    if (count($where_clauses) !== 0) {
        $archive_search_sql .= ' WHERE (' . implode(")\n AND (", $where_clauses) . ")\n";
    }

    $archive_search_sql .= " ORDER BY archive_id DESC";

    return $archive_search_sql;
}

function zen_is_message_trustable($from, $module): bool
{
    if ($from !== EMAIL_FROM) {
        return false;
    }
    if ($module === 'contact_us' || $module === 'ask_a_question') {
        return false;
    }
    return true;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// handle remembering previous search criteria
$rememberable_vars = ['start_date', 'end_date', 'date_range', 'search_text', 'module', 'isForDisplay', 'only_errors'];
if ($action === 'reset') {
    unset($_SESSION['email_archive_search_criteria']);
    zen_redirect(zen_href_link(FILENAME_EMAIL_HISTORY));
}
if ($action === 'search') {
    foreach($rememberable_vars as $var) {
        $_SESSION['email_archive_search_criteria'][$var] = $_POST[$var] ?? null;
    }
} elseif (!empty($_SESSION['email_archive_search_criteria'])) {
    foreach($rememberable_vars as $var) {
        if (isset($_SESSION['email_archive_search_criteria'][$var])) {
                $_POST[$var] = $_SESSION['email_archive_search_criteria'][$var];
        }
    }
}

$allow_html = true;
$isForDisplay = empty($_POST['print_format']);
if ($action === 'prev_text' || $action === 'prev_html') {
    $isForDisplay = false;
}
$only_errors = !empty($_POST['only_errors']);
$search_text = isset($_POST['search_text']) && zen_not_null($_POST['search_text']) ? $_POST['search_text'] : '';
$search_module = !empty($_POST['module']) && (int)$_POST['module'] !== 1 ? $_POST['module'] : 0;
$search_sd = !empty($_POST['start_date']);
$search_ed = !empty($_POST['end_date']);
$sd_raw = $ed_raw = '';
if ($search_sd) {
    $sd_raw = $_POST['start_date'];
}
if ($search_ed) {
    $ed_raw = $_POST['end_date'];
}
$date_range = $_POST['date_range'] ?? ' ';
// If no date range at all is supplied, initial page load should default to past 30 days
if (!isset($_POST['date_range']) && !isset($_POST['start_date']) && !isset($_POST['end_date'])) {
    $date_range = 'last_30_days';
    $sd_raw = new \DateTime();
    $sd_raw->sub(DateInterval::createFromDateString(('30 days')));
    $ed_raw = new \DateTime();

    $sd_raw = $sd_raw->format(zen_datepicker_format_fordate(DATE_FORMAT_DATE_PICKER));
    $ed_raw = $ed_raw->format(zen_datepicker_format_fordate(DATE_FORMAT_DATE_PICKER));
}

if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd') {
    $local_fmt = zen_datepicker_format_fordate();

    if (!empty($sd_raw)) {
        $dt = DateTime::createFromFormat($local_fmt, $sd_raw);
        $sd_raw = 'null';
        if (!empty($dt)) {
            $sd_raw = $dt->format('Y-m-d');
        }
    }
    if (!empty($ed_raw)) {
        $dt = DateTime::createFromFormat($local_fmt, $ed_raw);
        $ed_raw = 'null';
        if (!empty($dt)) {
            $ed_raw = $dt->format('Y-m-d');
        }
    }
}
if (zcDate::validateDate($sd_raw) !== true) {
    $search_sd = false;
}
if (zcDate::validateDate($ed_raw) !== true) {
    $search_ed = false;
}

$archive_search_sql = zen_get_email_archive_search_query($search_text, $sd_raw, $ed_raw, $search_module, $only_errors);

switch($action) {
    case 'resend':
        // retrieve the email record
        $email_sql = $db->Execute("SELECT * FROM " . TABLE_EMAIL_ARCHIVE . " WHERE archive_id = " . (int)$_POST['archive_id'], 1);
        $email = new objectInfo($email_sql->fields);
        // resend the message
        // we use 'xml_record' to block out the HTML content.
        zen_mail($email->email_to_name, $email->email_to_address, zen_output_string_protected($email->email_subject), $email->email_text, $email->email_from_name, $email->email_from_address, [], 'xml_record');
        $messageStack->add_session(sprintf(SUCCESS_EMAIL_RESENT, $email->archive_id, $email->email_to_address), 'success');
        zen_redirect(zen_href_link(FILENAME_EMAIL_HISTORY));
        break;

    case 'delete':
        if (!empty((int)$_POST['archive_id'])) {
            $db->Execute("DELETE FROM " . TABLE_EMAIL_ARCHIVE . " WHERE archive_id = " . (int)$_POST['archive_id']);
        } else {
            die('No tampering allowed.');
        }
        zen_redirect(zen_href_link(FILENAME_EMAIL_HISTORY));
        break;

    case 'trim_confirm':
        $age = $_POST['email_age'] ?? '';
        if ($age === '1_months') {
            $cutoff_date = '1 MONTH';
        }
        if ($age === '6_months') {
            $cutoff_date = '6 MONTH';
        } elseif ($age === '1_year') {
            $cutoff_date = '12 MONTH';
        }
        $db->Execute("DELETE FROM " . TABLE_EMAIL_ARCHIVE . " WHERE date_sent <= DATE_SUB(NOW(), INTERVAL " . $cutoff_date . ")");
        $db->Execute("OPTIMIZE TABLE " . TABLE_EMAIL_ARCHIVE);
        $messageStack->add_session(sprintf(SUCCESS_TRIM_ARCHIVE, $cutoff_date), 'success');
        zen_redirect(zen_href_link(FILENAME_EMAIL_HISTORY));
        break;
}

// Get list of modules related to records in this db
$results = $db->Execute("SELECT DISTINCT module FROM " . TABLE_EMAIL_ARCHIVE . " ORDER BY module");
$email_module_array[] = [
    'id' => 1,
    'text' => TEXT_ALL_MODULES,
];
foreach ($results as $result) {
    $email_module_array[] = [
        'id' => $result['module'],
        'text' => $result['module'],
    ];
}

?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
<?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<?php
if ($action === 'prev_text' || $action === 'prev_html') {
  $body_params = ' marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF"';
}
?>
<body<?=$body_params ?? '' ?>>
if ($isForDisplay) { ?>
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<?php } ?>
<div class="container-fluid">

        <?php
        switch ($action) {

        case 'prev_text':
        case 'prev_html':
        // rebuild email preview
        $this_email = $db->Execute("SELECT * FROM " . TABLE_EMAIL_ARCHIVE . " WHERE archive_id = " . (int)$_GET['archive_id'], 1);

        $text_content = nl2br(zen_output_string_protected($this_email->fields['email_text']));
        if ($action === 'prev_html') {
            $html_safe = zen_is_message_trustable($this_email->fields['email_from_address'], $this_email->fields['module']);
            if ($allow_html && $html_safe) {
                $html_content = $this_email->fields['email_html'];
            } else {
                // Swap to text
                $html_content = '<b>' . HEADING_TEXT_INSTEAD . '</b><br><br>' . $text_content;
            }
        }
        ?>
        <div class="row">
            <div class="col-sm-12 text-center pageHeading">
                <?= TEXT_EMAIL_NUMBER . $this_email->fields['archive_id'] ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-1 text-right"><b><?= TEXT_EMAIL_FROM ?></b></div>
            <div class="col-sm-6 text-left"><?= $this_email->fields['email_from_name'] . ' [' . $this_email->fields['email_from_address'] . ']' ?></div>
        </div>
        <div class="row">
            <div class="col-sm-1 text-right"><b><?= TEXT_EMAIL_TO ?></b></div>
            <div class="col-sm-6 text-left"><?= $this_email->fields['email_to_name'] . ' [' . $this_email->fields['email_to_address'] . ']' ?></div>
        </div>
        <div class="row">
            <div class="col-sm-1 text-right"><b><?= TEXT_EMAIL_DATE_SENT ?></b></div>
            <div class="col-sm-6 text-left"><?= zen_datetime_short($this_email->fields['date_sent']) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-1 text-right"><b><?= TEXT_EMAIL_SUBJECT ?></b></div>
            <div class="col-sm-6 text-left"><?= zen_output_string_protected($this_email->fields['email_subject']) ?></div>
        </div>

        <div id="PreviewEmailBlock" class="row">
            <div class="col-sm-11 col-sm-offset-1 mt-3">
                <!-- NOTE: a table with old syntax is used here intentionally for the sake of email HTML, to accommodate lowest-common-denominator (email HTML standards are very ancient) -->
                <table id="PreviewEmailTable" border="0" cellspacing="0" cellpadding="10">
                    <tr>
                        <td><?= ($action === 'prev_html') ? $html_content : $text_content ?></td>
                    </tr>
                </table>
            </div>
        </div>

    <?php
    break;

    case 'trim':
    ?>
    <div id="TrimArchiveBlock" class="hidden-print">
        <div class="row">
            <div class="col-sm-12 h1">
                <?= TEXT_TRIM_ARCHIVE ?>
            </div>
        </div>
        <?= zen_draw_form('trim_timeframe', FILENAME_EMAIL_HISTORY) ?>
        <?= zen_draw_hidden_field('action', 'trim_confirm') ?>

        <div class="row">
            <div class="col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <?= zen_draw_label(HEADING_TRIM_INSTRUCT, '') ?>
                <div class="radio">
                    <label for="1mo">
                        <?= zen_draw_radio_field('email_age', '1_months', true, '', 'id="1mo"') . RADIO_1_MONTH . ' (' . date("m/d/Y", mktime(0, 0, 0, (int)date("m") - 1, (int)date("d"), (int)date("Y"))) . ')' ?>
                    </label>
                </div>
                <div class="radio">
                    <label for="6mo">
                        <?= zen_draw_radio_field('email_age', '6_months', false, '', 'id="6mo"') . RADIO_6_MONTHS . ' (' . date("m/d/Y", mktime(0, 0, 0, (int)date("m") - 6, (int)date("d"), (int)date("Y"))) . ')' ?>
                    </label>
                </div>
                <div class="radio">
                    <label for="1yr">
                        <?= zen_draw_radio_field('email_age', '1_year', false, '', 'id="1yr"') . RADIO_1_YEAR . ' (' . date("m/d/Y", mktime(0, 0, 0, (int)date("m"), (int)date("d"), (int)date("Y") - 1)) . ')' ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 text-center warningBox">
                <div class="warningText">
                    <?= TRIM_CONFIRM_WARNING ?><br/><br/>
                    <p>
                        <input type="submit" value="<?= BUTTON_TRIM_CONFIRM ?>" class="btn btn-sm btn-danger">
                        <input type="button" value="<?= BUTTON_CANCEL ?>" class="btn btn-sm btn-default" onClick="<?= 'window.location.href=\'' . zen_href_link(FILENAME_EMAIL_HISTORY) . '\'' ?>">
                    </p>
                </div>
            </div>
        </div>
        <?= '</form>' ?>

    </div>


        <?php
        break;

    case 'search':
    default:
    ?>
    <div class="row">
    <?php if (!$isForDisplay) { ?>
        <div class="col-sm-4">
            <?= '<a href="' . zen_href_link(FILENAME_EMAIL_HISTORY, 'action=' . $action) . '"><span class="pageHeading">' . HEADING_TITLE . '</span></a>' ?>
        </div>
        <div class="col-sm-6">
            <?= date('l M d, Y', time()) ?>
        </div>

    <?php } else { ?>

        <div class="h1 col-sm-12">
            <?= HEADING_TITLE ?>
        </div>

        <div class="col-sm-3 col-md-6">
            <?= HEADING_SEARCH_INSTRUCT ?>
        </div>
        <div class="col-sm-3 col-md-6 text-right">
            <?= '<a href="' . zen_href_link(FILENAME_EMAIL_HISTORY, 'action=trim') . '" class="btn btn-primary" role="button">' . TEXT_TRIM_ARCHIVE . '</a>' ?>
        </div>

        <?= zen_draw_form('search', FILENAME_EMAIL_HISTORY) ?>
        <?= zen_draw_hidden_field('action', 'search') ?>
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    <?= zen_draw_label(HEADING_START_DATE, 'start_date', 'class="control-label"') ?>
                    <div class="date input-group" id="datepicker_start_date">
                        <span class="input-group-addon datepicker_icon"><?= zen_icon('calendar-days', size: 'lg') ?></span>
                        <?= zen_draw_input_field('start_date', $sd_raw, 'class="form-control" id="start_date"') ?>
                    </div>
                </div>
                <div class="form-group">
                    <?= zen_draw_label(HEADING_END_DATE, 'end_date', 'class="control-label"') ?>
                    <div class="date input-group" id="datepicker_end_date">
                        <span class="input-group-addon datepicker_icon"><?= zen_icon('calendar-days', size: 'lg') ?></span>
                        <?= zen_draw_input_field('end_date', $ed_raw, 'class="form-control" id="end_date"') ?>
                    </div>
                </div>
                <div class="form-group">
                    <?= zen_draw_label(HEADING_DATE_RANGE, 'date_range', 'class="control-label"') ?>
                    <?= zen_draw_pull_down_menu('date_range', [
                        ['id' => ' ', 'text' => TEXT_DROPDOWN_DATE_SELECT_ALL],
                        ['id' => 'last_7_days', 'text' => TEXT_DROPDOWN_DATE_SELECT_7_DAYS],
                        ['id' => 'last_30_days', 'text' => TEXT_DROPDOWN_DATE_SELECT_30_DAYS],
                        ['id' => 'last_3_months', 'text' => TEXT_DROPDOWN_DATE_SELECT_3_MONTHS],
                        ['id' => 'last_year', 'text' => TEXT_DROPDOWN_DATE_SELECT_LAST_YEAR],
                    ],
                    $date_range, 'id="date_range" class="form-control"') ?>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <?= zen_draw_label(HEADING_SEARCH_TEXT . ' ' . zen_icon('circle-info', TOOLTIP_SEARCH_TEXT), 'search_text', 'class="control-label"') ?>
                    <?= zen_draw_input_field('search_text', $search_text, 'id="search_text" class="form-control"') ?>
                    <?php
                    if (!empty($search_text)) {
                        echo '<br>' . HEADING_SEARCH_TEXT_FILTER . zen_output_string_protected($search_text);
                    }
                    ?>
                </div>
                <div class="form-group">
                <?php
                echo zen_draw_label(HEADING_MODULE_SELECT, 'search_module', 'class="control-label"') . '<br>';
                echo zen_draw_pull_down_menu('module', $email_module_array, $search_module, 'id="search_module" class="form-control"');
                ?>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="checkbox">
                    <label for="print_format">
                        <?= zen_draw_checkbox_field('print_format', 1, !$isForDisplay, '', 'id="print_format"') ?>
                        <?= HEADING_PRINT_FORMAT ?>
                    </label>
                </div>
                <div class="checkbox">
                    <label for="only_errors">
                    <?= zen_draw_checkbox_field('only_errors', 1, $only_errors, '', 'id="only_errors"') ?>
                        <?= HEADING_ONLY_ERRORS . '&nbsp;' . zen_icon('circle-info', TOOLTIP_ONLY_ERRORS) ?>
                    </label>
                </div>

                <input type="submit" value="<?= BUTTON_SEARCH_ARCHIVE ?>" class="btn btn-primary">
                <input type="submit" value="<?= BUTTON_RESET_SEARCH_ARCHIVE ?>" class="btn btn-default" formaction="<?= zen_href_link(FILENAME_EMAIL_HISTORY, 'action=reset') ?>">
            </div>
        </div>

        <?= '</form>' ?>


    <?php } ?>

<!--    </div>-->

    <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
        <table class="table table-hover" role="listbox">
            <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_EMAIL_DATE ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_CUSTOMERS_NAME ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_CUSTOMERS_EMAIL ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_EMAIL_SUBJECT ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_EMAIL_ERRORINFO ?></th>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_EMAIL_FORMAT ?></th>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
            </tr>
            </thead>
            <tbody>
            <?php

        // Split Page
        // reset page when page is unknown
        $href_page_param = '';
        if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['archive_id'])) {
            $check_page = $db->Execute($archive_search_sql);
            $check_count = 0;
            if ($check_page->RecordCount() > $max_records_per_page) {
                foreach ($check_page as $item) {
                    if ((int)$item['archive_id'] === (int)$_GET['archive_id']) {
                        break;
                    }
                    $check_count++;
                }
                $_GET['page'] = round((($check_count / $max_records_per_page) + (fmod_round($check_count, $max_records_per_page) != 0 ? .5 : 0)), 0);
                $href_page_param = (!empty($_GET['page']) && (int)$_GET['page'] !== 1) ? 'page=' . $_GET['page'] . '&' : '';
            } else {
                $_GET['page'] = 1;
            }
        }

        $email_split = new splitPageResults($_GET['page'], $max_records_per_page, $archive_search_sql, $email_query_numrows);

        $results = $db->Execute($archive_search_sql);

        foreach ($results as $archive_record) {
            if ((!isset($_GET['archive_id']) || (isset($_GET['archive_id']) && ($_GET['archive_id'] == $archive_record['archive_id']))) && !isset($archive)) {
                $archive = new objectInfo($archive_record);
            }

            $class_and_id = 'class="dataTableRow"';
            $role = 'role="option" aria-selected="false"';
            $href = zen_href_link(FILENAME_EMAIL_HISTORY, zen_get_all_get_params(['archive_id']) . 'archive_id=' . $archive_record['archive_id']);
            if (isset($archive) && is_object($archive) && ($archive_record['archive_id'] == $archive->archive_id) && $isForDisplay) {
                $href = zen_href_link(FILENAME_EMAIL_HISTORY, zen_get_all_get_params(['archive_id', 'action']) . 'archive_id=' . $archive->archive_id . '&action=view');
                $class_and_id = 'id="defaultSelected" class="dataTableRowSelected"';
                $role = 'role="option" aria-selected="true"';
            }
            ?>
            <tr <?= $class_and_id ?> onclick="document.location.href='<?= $href ?>'" <?= $role ?>>
                <td class="dataTableContent"><?= zen_icon('circle-info', sprintf(TEXT_ARCHIVE_ID, $archive_record['archive_id'])) .
                    '&nbsp;' . zen_datetime_short($archive_record['date_sent']) ?></td>
                <td class="dataTableContent"><?= $archive_record['email_to_name'] ?></td>
                <td class="dataTableContent"><?= $archive_record['email_to_address'] ?></td>
                <td class="dataTableContent overflowText"><?= zen_output_string_protected(zen_trunc_string($archive_record['email_subject'], SUBJECT_SIZE_LIMIT)) ?></td>
                <td class="dataTableContent overflowText"><?= zen_output_string_protected(zen_trunc_string($archive_record['errorinfo'], MESSAGE_SIZE_LIMIT)) ?></td>
                <td class="dataTableContent text-right"><?= !empty($archive_record['email_html']) ? TABLE_FORMAT_HTML : TABLE_FORMAT_TEXT ?></td>
                <td class="dataTableContent text-right actions">
                <?php
                    if (isset($archive) && is_object($archive) && ($archive_record['archive_id'] == $archive->archive_id) && $isForDisplay) {
                        echo zen_icon('caret-right', ICON_SELECTED, '2x', true);
                    } else {
                    ?>
                    <a href="<?= zen_href_link(FILENAME_EMAIL_HISTORY, $href_page_param . 'archive_id=' . $archive_record['archive_id']) ?>" role="button" title="<?= IMAGE_ICON_INFO ?>">
                        <?= zen_icon('circle-info', '', '2x', true, false) ?>
                    </a>
                    <?php
                    }
                ?>
                </td>
            </tr>
            <?php
            }
            if ($results->RecordCount() === 0) {
                echo '<tr><td colspan="8" class="text-center"><strong>' . TEXT_NO_ARCHIVE_RECORDS_FOUND . '</strong></td></tr>';
            }

            ?>
            </tbody>
        </table>
        <div class="row">
            <table class="table">
                <tr>
                    <td><?= $email_split->display_count($email_query_numrows, $max_records_per_page, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_EMAILS) ?></td>
                    <td class="text-right"><?= $email_split->display_links($email_query_numrows, $max_records_per_page, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(['archive_id', 'page'])) ?></td>
                </tr>
            </table>
        </div>

        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">

        <?php
        // create sidebox
        $heading = [];
        $contents = [];

        if (isset($archive) && is_object($archive)) {
            // get the customer ID and determine "send" button action
            $customer = $db->Execute("SELECT customers_id FROM " . TABLE_CUSTOMERS . " WHERE customers_email_address LIKE '" . zen_db_input($archive->email_to_address) . "'", 1);
            if ($customer->RecordCount() === 1) {
                $mail_button = '<a href="' . zen_href_link(FILENAME_MAIL, 'origin=' . FILENAME_EMAIL_HISTORY . '&customer=' . $archive->email_to_address . '&cID=' . $customer->fields['customers_id']) . '" class="btn btn-primary" role="button">' . SEND_NEW_EMAIL . '</a>';
            } else {
                $mail_button = '<a href="mailto:' . $archive->email_to_address . '" class="btn btn-primary" role="button">' . SEND_NEW_EMAIL . '</a>';
            }

            $heading[] = ['text' => '<b>' . sprintf(TEXT_ARCHIVE_ID, $archive->archive_id) . '&nbsp; - &nbsp;' . zen_datetime_short($archive->date_sent) . '</b>'];

            $contents = ['form' => zen_draw_form('archive_actions', FILENAME_EMAIL_HISTORY, 'action=resend', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('archive_id', $archive->archive_id)];

            $contents[] = [
                'align' => 'center',
                'text' => $mail_button,
            ];

            $contents[] = ['align' => 'center', 'text' => '
                                <button type="submit" class="btn btn-primary" onclick="return confirm(\'' . POPUP_CONFIRM_RESEND . '\');" >' . IMAGE_ICON_RESEND . '</button><br><br>
                                <button type="submit" class="btn btn-danger" onclick="return confirm(\'' . POPUP_CONFIRM_DELETE . '\');" formaction="'. zen_href_link(FILENAME_EMAIL_HISTORY, 'action=delete') .'">' . IMAGE_ICON_DELETE . '</button>'
            ];

            $contents[] = [
                'align' => 'center',
                'text' => '<a href="' . zen_href_link(FILENAME_EMAIL_HISTORY, 'archive_id=' . $archive->archive_id . '&action=prev_text') . '" class="btn btn-primary" role="button" TARGET="_blank">' . IMAGE_ICON_TEXT . '</a>',
            ];
            if ($archive->email_html !== '') {
                // For outbound messages only
                $html_safe = zen_is_message_trustable($archive->email_from_address, $archive->module);
                if ($allow_html && $html_safe) {
                    $contents[] = [
                        'align' => 'center',
                        'text' => '<a href="' . zen_href_link(FILENAME_EMAIL_HISTORY, 'archive_id=' . $archive->archive_id . '&action=prev_html') . '" class="btn btn-primary" TARGET="_blank">' . IMAGE_ICON_HTML . '</a>',
                    ];
                }
            }
            $contents[] = ['text' => '<br>' . zen_draw_separator()];
            $contents[] = ['text' => '<br><b>' . TEXT_EMAIL_MODULE . '</b>' . $archive->module];
            $contents[] = ['text' => '<b>' . TEXT_EMAIL_FROM . '</b>' . $archive->email_from_name . ' [' . $archive->email_from_address . ']'];
            $contents[] = ['text' => '<br><b>' . TEXT_EMAIL_TO . '</b>' . $archive->email_to_name . ' [' . $archive->email_to_address . ']'];
            $contents[] = ['text' => '<b>' . TEXT_EMAIL_DATE_SENT . '</b>' . $archive->date_sent];
            $contents[] = ['text' => '<b>' . TEXT_EMAIL_SUBJECT . '</b>' . zen_output_string_protected($archive->email_subject)];
            $contents[] = ['text' => '<br><b>' . TEXT_EMAIL_EXCERPT . '</b>'];

            $contents[] = ['text' => '<br>' . nl2br(substr(zen_output_string_protected($archive->email_text), 0, MESSAGE_SIZE_LIMIT)) . MESSAGE_LIMIT_BREAK];

            if (!empty($archive->errorinfo)) {
                $contents[] = ['text' => '<br><b>' . TEXT_EMAIL_ERRORINFO . '</b>'];
                $contents[] = ['text' => '<br>' . nl2br(substr(zen_output_string_protected($archive->errorinfo), 0, MESSAGE_SIZE_LIMIT)) . MESSAGE_LIMIT_BREAK];
            }
        }

        if (!empty($heading) && !empty($contents) && $isForDisplay) {
            $box = new box;
            echo $box->infoBox($heading, $contents);
        }
        ?>
        </div>
</div><!-- row_eof //-->
</div><!-- container-fluid_eof //-->

<?php
break;
} // end switch($action)
?>
</div>
<?php
if ($isForDisplay) {
    require DIR_WS_INCLUDES . 'footer.php';
    ?>
    <!-- script for datepicker -->
    <script>
        $(function () {
            const startPicker = $('input[name="start_date"]').datepicker();
            const endPicker = $('input[name="end_date"]').datepicker();
            $('#date_range').on('change', (e) => {
                console.log('Date range to ', e);
                let start = new Date();
                start.setUTCHours(0);
                start.setUTCMinutes(0);
                start.setUTCSeconds(0);
                let end = new Date();
                end.setUTCHours(23);
                end.setUTCMinutes(59);
                end.setUTCSeconds(59);
                switch (e.target.value) {
                    case ' ':
                        start = null;
                        end = null;
                        break;
                    case 'last_7_days':
                        start.setUTCDate(start.getUTCDate() - 7);
                        break;
                    case 'last_30_days':
                        start.setUTCDate(start.getUTCDate() - 30);
                        break;
                    case 'last_3_months':
                        start.setUTCMonth(start.getUTCMonth() - 3);
                        break;
                    case 'last_year':
                        start.setUTCMonth(start.getUTCMonth() - 12);
                        break;
                }

                startPicker.datepicker('setDate', start);
                endPicker.datepicker('setDate', end);
            })
        })
    </script>
<?php
}
?>
</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
