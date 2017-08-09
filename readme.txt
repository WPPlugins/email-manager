=== Email Manager ===
Contributors: brooksX
Tags: emails, html emails, schedule, notices, bulk mail
Requires at least: 3.0
Tested up to: 4.1
Stable tag: 0.2
License: GPLv2 or later

Email Manager helps you send and schedule beautiful professional email and WordPress notifications.

== Description ==

This plugin provides you with a platform to send and schedule beautiful professional email and WordPress notifications to Selected WordPress User groups.
It allows for archiving emails enabling use of view in browser link in the html emails. You can create re-usable HTML email templates for emails sent on the regular.

Features:

* Creation of Reusable HTML Email templates
* Short-codes for view in browser link, unsubscribe link and posts content
* Scheduling of emails to be sent on at a future date
* Timed emails that are sent after a user preset time interval
* Allows modification of the in-built WordPress notices  
* Auto inline styling of your HTML email styles to enable correct display on several email clients
* Many more to come!


== Installation ==

Upload the Email Manager plugin to your WordPress plugin directory and activate it from the plugins panel of your WordPress.

== Short Codes ==
This page lists the different ShortCodes that are availed by Email Manager.
= Special links Short Codes =
= Unsubscribe link in Emails =
[wpem link=unsubscribe]

*This short code adds an unsubscribe link for Users in your email. When users click this link, they will be automatically unsigned from the emailing list if they are logged in or directed to a page with unsubscribing instructions. That page along with the link text can be set from Email Manager's settings page.
= View email in browser link =
[wpem archive=7]

*This short code should only be used if you are using an "un-edited" Email Template. This will produce a link to that Email template that can be viewed in the browser.  The link text can be configured in Email Manager settings.
The shortcode above will produce a link pointing to Email Template with ID 7

= Post ShortCodes =
These short codes are used to include a post, page or custom post type link, title, body or excerpt into an Email

[wpem id=4 content=title]
*Adds the title of post with id 4 to the email.

[wpem id=4 content=post_link]
*Adds the permalink of post with id 4 to the email.

[wpem id=4 content=title_link]
*Adds a link pointing to post with id 4 with the post title as the link text to the email.

[wpem id=4 content=excerpt]
*Adds the excerpt of post with id 4 to the email.

[wpem id=4 content=body]
*Adds the content of post with id 4 to the email.

[wpem id=1 content=img]
*Adds a featured image 

[wpem id=1 content=img img_size=thumb-medium]
*Adds a featured image with image size medium any image sizes defined in WordPress will work

== Screenshots ==
1. Send email Interface
2. Editable Notifications Interface 
3. Some of the plugin’s settings
4. User created Scheduled emails

== Frequently Asked Questions ==

= How to import a Pre-made stand alone HTML email Template to Wordpress Email Manager =

*Create a new Email Template from wp-admin->Email Templates->Add New
*Click the Text tab of the Editor
*Open the stand alone email template with your favorite code Editor e.g  NotePad or NotePad++
*Copy the CSS in the head of the HTML document and paste it in the CSS box
*Copy the HTML with in the body tag and paste it in the WordPress Editor
*Save. Now you can click the Visual tab of the Editor to see the template in its full glory.
*Edit Text as required.

= How to Create a News letter template =
To create a news letter template for your posts, you only need to know the right shortcodes to use.
There is a whole list of WordPress Email Manager shortcodes outlined above in the shortcode section.
= Is Support Free =
Yes, We'll provide free support for all bugs or malfunctions you may get while using the plugin
= Can you extend eMail Manager to support my email subscription plugin or email collecting form =
Yes, we can do this if there is considerable demand otherwise we may negotiate a small fee if the demand pertains only you.

== Changelog ==
= 0.1=
* First launch
= 0.2=
* Minor bug crushes
