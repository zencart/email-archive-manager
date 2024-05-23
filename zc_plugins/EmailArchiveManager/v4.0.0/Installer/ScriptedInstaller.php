<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 17  Plugin version 4.0 $
 *
 * @var sniffer $sniffer;
 */

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{

    /**
     * Zen Cart pre-v2.1 does not have this function built in, so provide a local implementation.
     *
     * @param string $table_name
     * @param string $index_name
     * @return boolean
     */
    public function indexExists_local(string $table_name, string $index_name): bool
    {
        global $db;

        $check = $db->Execute(
            'SHOW INDEX FROM `' . $db->prepare_input($table_name) . '` ' .
            "WHERE `Key_name` = '" . $db->prepare_input($index_name) . "'"
        );
        return !$check->EOF;
    }

    protected function executeInstall(): void
    {
        global $db;
        zen_deregister_admin_pages(['emailArchive']);
        zen_register_admin_page('emailArchive', 'BOX_TOOLS_EMAIL_ARCHIVE_MANAGER','FILENAME_EMAIL_HISTORY', '', 'tools', 'Y', 20);

        global $sniffer;
        if (!$sniffer->field_exists(TABLE_EMAIL_ARCHIVE, 'errorinfo')) {
            $sql = 'ALTER TABLE ' . TABLE_EMAIL_ARCHIVE . ' ADD COLUMN errorinfo TEXT DEFAULT NULL';
            $this->executeInstallerSql($sql);
        }

        // Zen Cart pre-v2.1 does not have this index on email_archive table
        $indexExists = false;
        if (method_exists($sniffer, 'indexExists')) {
            $indexExists = $sniffer->indexExists(TABLE_EMAIL_ARCHIVE, 'idx_email_date_sent_zen');
        } else {
            $indexExists = $this->indexExists_local(TABLE_EMAIL_ARCHIVE, 'idx_email_date_sent_zen');
        }
        if (!$indexExists) {
            $db->Execute(
                "ALTER TABLE " . TABLE_EMAIL_ARCHIVE . " ADD INDEX idx_email_date_sent_zen (date_sent);"
            );
        }
    }

    protected function executeUninstall(): void
    {
        zen_deregister_admin_pages(['emailArchive']);
        // Note: Do not remove idx_email_date_sent_zen as it is expected to be in core Zen Cart post v2.1
    }
}
