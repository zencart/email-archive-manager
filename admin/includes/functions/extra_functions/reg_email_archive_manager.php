<?php
/*
 * Email Archive Manager
*/

/**
 * Add entry to Tools menu
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (function_exists('zen_register_admin_page')) {
  if (!zen_page_key_exists('emailArchive')) {
    zen_register_admin_page('emailArchive', 'BOX_TOOLS_EMAIL_ARCHIVE_MANAGER','FILENAME_EMAIL_HISTORY', '', 'tools', 'Y', 20);
  }
}
