# Email Archive Manager plugin for Zen Cart 

Allows you to view emails sent by your shopping cart.  Requires the `Email Archiving Active?` flag in Admin > Configuration > Email to be set to `true`.

- Search for e-mails using any combination of date range, embodied text, or e-mail module.
- Printer-friendly display of email, including header information.
- Resend messages to the original recipient, with complete original headers
- One-click link to begin composing email to the recipient of the selected e-mail, both through the Zen web mail interface and a standard mailto link
- Trim archive database (to control size) at increments of 1, 6, or 12 months, or messages can be deleted one at a time.

Note:
If you are sending out HTML emails, you may view the HTML or plain text copy of your email.  However, for inbound emails that come from your website (ie: contact us, ask a question), you may only see the plain text version of an email.  This is because inbound emails may be malicious and can't be safely displayed in HTML format.

<p align="center"><img src="Email Archive manager screenshot.png" alt="Screenshot"></p>

## Installation instructions

Note: Always backup your shop files and database before making changes.


Installation:

1. Download the package and unzip to a temp directory.

2. Copy the contents of entire "zc_plugins/EmailArchiveManager" folder to the "zc_plugins/EmailArchiveManager" folder of your shop. The files are already arranged in the appropriate structure, and there are *no* overwrites!

3. Go to Admin->Modules->Plugin Manager and enable the Email Archive Manager plugin.

4. You'll find "E-mail Archive Manager" under "Tools" in the Admin. 

5. If you want non-Super-Admin's to be able to use it, grant access to additional user profiles via Admin->Admin->Profiles, depending on who you wish to allow permission to use it.

6. By default, email archiving is turned OFF; to start archiving emails, you must turn it ON under
   Admin > Configuration > Email > "Email Archiving Active?"

## Upgrade instructions

1. Download the package and unzip to a temp directory.

2. Look under the "zc_plugins/EmailArchiveManager" folder and see the version numbers available. For any version-number directories that are not already on your store's server under the "zc_plugins/EmailArchiveManager" folder, copy them to the server.

3. Go to Admin > Modules > Plugin Manager and click on Email Archive Manager, and click Upgrade. Choose the version you want to upgrade to, and confirm.


## UPGRADING FROM OLDER VERSION BEFORE Zen Cart 2.0.0

1. Delete the following old files from your store's server:

`YOUR_ADMIN/email_archive_manager.php`
`YOUR_ADMIN/includes/extra_datafiles/email_archive_manager_defines.php`
`YOUR_ADMIN/includes/functions/extra_functions/reg_email_archive_manager.php`
`YOUR_ADMIN/includes/languages/english/email_archive_manager.php`

2. Follow the Installation instructions above.



### Credits: 
By Frank Koehl
- Additional coding support by https://github.com/neekfenwick
- Additional coding support by DrByte
- Additional coding support by That Software Guy (www.thatsoftwareguy.com)
- Additional coding support by TwitchToo
- Code development sponsored by Destination ImagiNation, Inc. www.destinationimagination.org

