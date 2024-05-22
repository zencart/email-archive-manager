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
    protected function executeInstall(): void
    {
        zen_deregister_admin_pages(['emailArchive']);
        zen_register_admin_page('emailArchive', 'BOX_TOOLS_EMAIL_ARCHIVE_MANAGER','FILENAME_EMAIL_HISTORY', '', 'tools', 'Y', 20);

        global $sniffer;
        if (!$sniffer->field_exists(TABLE_EMAIL_ARCHIVE, 'errorinfo')) {
            $sql = 'ALTER TABLE ' . TABLE_EMAIL_ARCHIVE . ' ADD COLUMN errorinfo TEXT DEFAULT NULL';
            $this->executeInstallerSql($sql);
        }
    }

    protected function executeUninstall(): void
    {
        zen_deregister_admin_pages(['emailArchive']);
    }
}
