# GC-HR-Shoutouts
PHP integration with Wordpress to allow users to submit 'shoutouts' to fellow staff members.

This code is currently live at <a href='https://humanresources.gordon.edu'>Gordon College's Human Resources Website</a>.

Once this code is written to the admin.php page on a wordpress site, you will be able to use the shortcodes below to display each module:
<ul>
  <li><b>[snippet_authorization_control]:</b> This will allow admin-approved users to add others to the approved list.
  <li><b>[snippet_thankyou_approval]:</b> This will display unapproved shoutout messages to be approved or deleted by admin users.
  <li><b>[snippet_build_thankyou]:</b> This is the main form of the program which will allow users to submit a shoutout message.
  <li><b>[snippet_display_thankyous]:</b> This shortcode will display the most recent 5 shoutouts to the user and allow them to view 5 more at a time with a button.
