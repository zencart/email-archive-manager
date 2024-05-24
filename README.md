# Email Archive Manager plugin for Zen Cart 

Allows you to view emails sent by your shopping cart.

(Requires the `Email Archiving Active?` flag in Admin > Configuration > Email to be set to `true`.)

- Search for emails using any combination of date range, subject, recipient, body text, delivery failure message, or what part of the store sent the message.
- Printer-friendly display of email, including recipient/subject information.
- Resend messages to the original recipient, with original headers (however, the date will be the new date).
- One-click link to begin composing email to the recipient of the selected email, through either the Zen Cart admin send-email interface or a standard mailto link.
- Trim archive database (to control size) at increments of 1, 6, or 12 months, or messages can be deleted one at a time.

Note:
If you are sending out HTML emails, you may view the HTML or plain TEXT portion of your email. However, for inbound emails that come from your website (ie: contact us, ask a question), you may only see the plain text version of an email.  This is because inbound emails may be malicious and can't be safely displayed in HTML format.

<p align="center"><img src="https://raw.githubusercontent.com/zencart/email-archive-manager/main/Email%20Archive%20Manager%20screenshot.png" alt="Screenshot"></p>

## Installation instructions

Note: Always backup your shop files and database before making changes.

Installation:

1. Download the package and unzip to a temp directory.

2. Copy the contents of entire "zc_plugins/EmailArchiveManager" folder to the "zc_plugins/EmailArchiveManager" folder of your shop. The files are already arranged in the appropriate structure, and there are *no* overwrites!

3. Go to Admin > Modules > Plugin Manager and enable the Email Archive Manager plugin.

4. You'll find "Email Archive Manager" under "Tools" in the Admin, which is how you will use this plugin.

5. If you want non-Super-Admin's to be able to use it, grant access to additional user profiles via Admin > Admins > Profiles, depending on whom you wish to allow permission to use it.

6. By default, email archiving is turned OFF; to start archiving emails, you must turn it ON under Admin > Configuration > Email > "Email Archiving Active?"

## Upgrade instructions

1. Download the package and unzip to a temp directory.

2. Look under the "zc_plugins/EmailArchiveManager" folder and see the version numbers available. For any version-number directories that are not already on your store's server under the "zc_plugins/EmailArchiveManager" folder, copy them to the server.

3. Go to Admin > Modules > Plugin Manager and click on Email Archive Manager, and click Upgrade. Choose the version you want to upgrade to, and confirm.


## UPGRADING FROM OLDER VERSION BEFORE Zen Cart 2.0.0

1. First, delete the following old files from your store's server:

`YOUR_ADMIN/email_archive_manager.php`
`YOUR_ADMIN/includes/extra_datafiles/email_archive_manager_defines.php`
`YOUR_ADMIN/includes/functions/extra_functions/reg_email_archive_manager.php`
`YOUR_ADMIN/includes/languages/english/email_archive_manager.php`

2. Then follow the Installation instructions above to install this plugin to use the Plugin Manager.



### Credits: 
- Initial code by Frank Koehl
- Additional coding support by https://github.com/neekfenwick
- Additional coding support by DrByte
- Additional coding support by That Software Guy (www.thatsoftwareguy.com)
- Additional coding support by TwitchToo
- Code development sponsored by Destination ImagiNation, Inc. www.destinationimagination.org

