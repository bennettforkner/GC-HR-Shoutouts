# Gordon College Human Resources Shoutouts
PHP integration with Wordpress to allow users to submit 'shoutouts' to fellow staff members.

This code is currently live at <a href='https://humanresources.gordon.edu'>Gordon College's Human Resources Website</a>.

<br><br>In order to use this code, you will need to:
    <ul><li>
      Write the code to your admin.php WordPress file (or another php plugin)
    </li><li>
  Write the below shortcodes on wordpress pages where you would like them to be displayed:
    <ul>
    <li><b>[snippet_authorization_control]:</b> This will allow admin-approved users to add others to the approved list.
    <li><b>[snippet_thankyou_approval]:</b> This will display unapproved shoutout messages to be approved or deleted by admin users.
    <li><b>[snippet_build_thankyou]:</b> This is the main form of the program which will allow users to submit a shoutout message.
    <li><b>[snippet_display_thankyous]:</b> This shortcode will display the most recent 5 shoutouts to the user and allow them to view 5 more at a time with a button.
  </ul>
      </li><li>
      Fill in the database information on all 4 code pages with your site's database credentials (can be found in wp-config file) (There may be more than one place to do so in each file)
    </li><li>
      Execute these two mySQL queries into the wordpress database:
      <ul><li>
        CREATE TABLE `staff_thankyou` (
 `thankyou_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `employee_name` varchar(30) DEFAULT NULL,
 `employee_department` varchar(30) DEFAULT NULL,
 `employee_email` varchar(40) DEFAULT NULL,
 `sender_email` varchar(40) NOT NULL,
 `thankyou_text` text,
 `thankyou_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `status` varchar(16) NOT NULL DEFAULT 'submitted',
 PRIMARY KEY (`thankyou_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8
      </li><li>
        CREATE TABLE `approval_users` (
 `user_id` bigint(20) unsigned NOT NULL,
 `user_login` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 PRIMARY KEY (`user_id`),
 KEY `username` (`user_login`),
 CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`ID`),
 CONSTRAINT `username` FOREIGN KEY (`user_login`) REFERENCES `wp_users` (`user_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
      </li></ul>
