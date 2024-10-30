=== Community Jar ===
Contributors: cmdtv
Donate link: http://ChurchMediaDesign.tv
Tags: volunteering, serving, church, helping, project managemant, volunteer, help, coordinate, serve
Requires at least: 3.4
Tested up to: 3.8.1
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Have a need or Help a need? The Community Jar makes it easy for anyone to submit a service project or volunteer to help meet a need. 

== Description ==

The Community Jar makes it easy for anyone to submit a service project or volunteer to help meet a need. 

Features include:

* Service project moderation / management
* Volunteer moderation / management
* Custom email templates
* Front end project submission
* No site membership required
* Project and volunteer information is only shared with those who need it
* Project owners can remain anonymous from public
* Form authentication
* Templates and shortcodes
* Fully Responsive

Get started today, everything works out-of-the-box!

This plugin was created with churches and non-profits in mind who want to allow people to host a project or volunteer, without signing up for yet another website or service.

For more information: http://cmd.tv/cj

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' Plugin Dashboard
2. Select `community-jar.zip` from your computer
3. Upload
4. Activate the plugin on the WordPress Plugin Dashboard

= Using FTP =

1. Extract `community-jar.zip` to your computer
2. Upload the `community-jar` directory to your `wp-content/plugins` directory
3. Activate the plugin on the WordPress Plugins dashboard

== Frequently Asked Questions ==

= How are projects submitted? =

You can submit a project from the Wordpress Admin or you can also utilize our awesome front end project submission page making it easy for anyone to host a project.

= What's the name all about? =

Have you ever seen a tray at a gas station that reads "leave a penny, take a penny"? 

We think this is just as true with volunteering / serving. We don't always have money but we do have time or skills that can help someone else out, other times we could just a use a helpful hand. So whether you have a need, or you have some time to help a need, everyone can get involved.

== Changelog ==

= 1.1.2 =
* FIXED: Admin icons and look now compatible with WordPress Version 3.8 and Higher. 
* FIXED: A bug that would allow users to change the page name for email templates making them unusable.
* FIXED: Date format was not saved correctly in database and was showing old projects. Date is now in proper format and all areas that use date are using human readable format, not database format.
* ADDED: New update routine to help with maintenance fixes
* UPDATED: Project Edit Page to utilize new date convention (should look the same to the end user).
* FIXED: Misc. spelling errors and cleaned up code.

= 1.1 =
* FIXED: Changed CPT's from 'project' & 'email' to 'cj_project' & 'cj_email' to remove any possible conflicts with other plugins or themes
* ADDED: Admin notification that lets you convert old 'project' CPT's to the new 'cj_project'
* FIXED: When visiting the 'Project Update Page' you can now mark your project as complete, which removes it from public listings.
* UPDATED: page templates now reflect new custom post type name convention.
* FIXED: Misc. spelling errors and cleaned up code.

= 1.0.3 =
* FIXED: Many Theme functions updated to work outside the Loop
* FIXED: Icon font now compatible with FontAwesome
* FIXED: Once the Project Submission Template has been copied to the active plugin directory do not remove it.
* UPDATED: Templates now reflect icon font changes

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.1.2 =
Now compatible with new WordPress Admin. Also fixed a major date format issue that would show past projects that should be hidden.

= 1.1 =
This version fixes Custom Post Type conflicts.  Upgrade immediately.