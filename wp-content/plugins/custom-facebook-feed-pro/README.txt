=== Custom Facebook Feed Pro ===
Author: Smash Balloon
Support Website: http://smashballoon/custom-facebook-feed/
Requires at least: 3.0
Tested up to: 4.5.3
Version: 2.6.8.1
License: Non-distributable, Not for resale

The Custom Facebook Feed allows you to display a completely customizable Facebook feed of any public Facebook page or group on your website.

== Description ==
Display a **completely customizable**, **responsive** and **search engine crawlable** version of your Facebook feed on your website. Completely match the look and feel of the site with tons of customization options!

* **Completely Customizable** - by default inherits your theme's styles
* **Feed content is crawlable by search engines adding SEO value to your site** - other Facebook plugins embed the feed using iframes which are not crawlable
* **Completely responsive and mobile optimized** - works on any screen size
* Display statuses, photos, videos, events, links and offers from your Facebook page or group
* Choose which post types are displayed. Only want to show photos, videos or events? No problem
* Display multiple feeds from different Facebook pages on the same page or throughout your site
* Show likes, shares and comments for each post
* Automatically embeds YouTube and Vimeo videos right in your feed
* Show event information - such as the name, time/date, location, link to a map, description and a link to buy tickets
* Filter posts by string or #hashtag
* Post tags - creates links when using the @ symbol to tag other people in your posts
* Post caching means that your feed is load lightning fast
* Fully internationalized and translatable into any language
* Enter your own custom CSS for even deeper customization

== Installation ==
1. Install the Custom Facebook Feed either via the WordPress plugin directory, or by uploading the files to your web server (in the /wp-content/plugins/ directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Facebook Feed' settings page to configure your feed.
4. Use the shortcode [custom-facebook-feed] in your page, post or widget to display your feed.
5. You can display multiple feeds of different Facebook pages by specifying a Page ID directly in the shortcode: [custom-facebook-feed id=smashballoon num=5].

== Changelog ==
= 2.6.8.1 =
* Fix: Fixed an issue introduced in the last update which caused photo feeds from Facebook Pages not to appear correctly

= 2.6.8 =
* Note: Due to Facebook deprecating version 2.0 of their API on August 8th, 2016, it will not longer be possible to display photo grid feeds from Facebook **Groups**. Photo grids from Facebook Pages will still work as normal.
* Tweak: The plugin will now show up to 100 image attachments at the bottom of the popup lightbox for each post rather than the previous limit of 12
* Tweak: Group wall feed posts are now ordered based on recent activity, rather than by the date they were created, to better reflect the order on the Facebook Group wall
* Tweak: Album feeds are now ordered based on when the last photo was added to an album, as they are on Facebook, rather than by when they were created
* Fix: Removed any dependencies on version 2.0 of the Facebook API
* Fix: Fixed an issue where line breaks in event descriptions weren't being displayed correctly when the HTML was being minimized
* Fix: Fixed a minor issue when using the keyboard to navigate through the popup lightbox
* Fix: When using a custom event date format the end date can now be automatically hidden when it ends on the same day as it starts
* Note: We're working hard on **version 3.0** which will be coming soon!

= 2.6.7 =
* Tweak: The "2 days ago" date format can now be translated via the shortcode
* Fix: Fixed an issue with video titles not displaying due to a Facebook API change
* Fix: The "post limit" setting is now working correctly in the video grid feed
* Fix: Fixed an issue with some keyboard keys incidentally launching the lightbox
* Fix: Fixed an issue with the font size not being applied to the post author text successfully in some themes
* Fix: The `likeboxcover` shortcode option is now correctly correctly
* Fix: The absolute path is no longer exposed in the page source code

= 2.6.6.3 =
* Updated to use the latest version of the Facebook API (2.6)
* Fix: Fixed an issue with the post URLs when sharing to Facebook
* Fix: Now using the Object ID in the post link for visitor posts as it's more reliable
* Fix: Fixed an issue with the event name sometimes displaying twice on timeline events
* Fix: Fixed an issue with the share link in the Facebook Like Box widget not working correctly
* Fix: Added support for proxy settings defined in the wp-config file
* Fix: When navigating through the lightbox using keyboard arrows the videos now stop playing as expected

= 2.6.6.2 =
* Fix: Fixed a JavaScript error in the admin area when using WordPress 4.5

= 2.6.6.1 =
* Fix: Fixed an issue with the Like Box not being displayed (unless a width was set) due to a recent Facebook change to the Like Box widget

= 2.6.6 =
* New: Added support for the [Reviews](https://smashballoon.com/extensions/reviews/) extension
* Tweak: Added settings from the Carousel, Masonry Columns, and Reviews extensions to the System Info
* Fix: Removed the Spanish .pot file as it isn't needed and was causing update issues occasionally
* Fix: Fixed a rare error related to strange link formats when the post text is linked

= 2.6.5.2 =
* Fix: Fixed some stray PHP notices that appeared if image attachments in the comments of a post didn't have a title
* Fix: Removed PHP notices that would appear when using the [Multifeed](https://smashballoon.com/extensions/multifeed/) extension if one of the Facebook pages wasn't public

= 2.6.5.1 =
* Fix: Fixed an issue where video titles weren't being displayed when displaying a video grid due to a Facebook API change
* Fix: Fixed an issue with the order of events when using the Multifeed extension
* Fix: Fixed an issue where the post offset setting wasn't working correctly with Multifeed events

= 2.6.5 =
* Tweak: Added the post text as the alt tag of the post images to help benefit SEO
* Fix: Fixed an issue caused by the Photon setting in the Jetpack plugin which caused some images not to display successfully. The plugin now includes an automatic workaround for this.
* Fix: Fixed an issue with the 'offset' setting not working for event feeds
* Fix: Increased the width of the Share popup to accomodate the new Google+ icon
* Fix: Fixed an issue where the Locale setting was not saving successfully on the settings page
* Fix: Fixed a problem where thumbnails weren't appearing in the popup lightbox when displaying albums from a group, even when using an "User" Access Token
* Fix: Fixed and issue where grids of videos wouldn't display when using a newly created Access Token due to a Facebook API change
* Fix: Fixed an issue with the thumbnail HTML formatting which sometimes occured when first opening the popup lightbox
* Fix: Fixed a rare issue with Ajax caching of the number of likes and comments
* Fix: Renamed a function to prevent conflicts
* Fix: Added a friendly error message if there is an error trying to retrieve events
* Fix: Added a friendly error message if trying to display group events without using a "User" Access Token

= 2.6.4 =
* Fix: Fixed an issue with Facebook group album cover photos not being displayed successfully due to a Facebook API change
* Fix: Fixed an issue with ajax caching
* Fix: Fixed an issue when events are displayed within the new [Carousel](https://smashballoon.com/extensions/carousel/) extension which caused duplicate empty items
* Fix: Fixed a margin issue in the new [Masonry Columns](https://smashballoon.com/extensions/masonry-columns/) extension when posts have a background color applied

= 2.6.3 =
* Fix: Fixed an issue with links not being formatted correctly in the lightbox caption
* Fix: Fixed an issue where some upcoming events weren't being displayed correctly for some Facebook pages

= 2.6.2 =
* Fix: Fixed an issue with events in the Date Range and Featured Post extensions
* Fix: Fixed an issue with some HTML code being displayed when photos were hidden from posts
* Fix: Squished a bug where HTML5 video controls weren't displaying when playing videos in a feed with the lightbox disabled

= 2.6.1 =
* Fix: Fixed an formatting issue in the last update which occurred with some themes

= 2.6 =
* New: Added support for two new extensions; [Carousel](https://smashballoon.com/extensions/carousel/) and [Masonry Columns](https://smashballoon.com/extensions/masonry-columns/)
* New: Added a 'Buy Tickets' link for event feeds
* New: Added a setting to allow you to use a fixed pixel width for the feed on desktop but switch to a 100% width responsive layout on mobile
* New: You can now click on the name of a setting on the admin pages to reveal the corresponding shortcode for that setting
* New: Added quick links to the top of the Customize settings pages to make it easier to find certain settings
* Tweak: Timeline events now use the layout select on the "Post Layout" settings page instead of always using the Thumbnail layout
* Tweak: The selected thumbnail is now highlighted in the pop-up lightbox
* Tweak: Event feeds now use the Graph API v2.5 instead of FQL in preparation for its deprecation this year
* Tweak: Updated the event placeholder image which is shown when an event doesn't have an image on Facebook
* Tweak: Moved a few of the settings to more logical locations
* Fix: Hashtag linking now works with all languages and character sets
* Fix: Caption text is now fully formatted in the pop-up lightbox for albums
* Fix: Fixed a bug which affected the photo/album grid layout when the Like Box was displayed at the top of the feed
* Fix: Fixed an issue where the Album extension wouldn't work if photos were selected as the only post type on the settings page
* Fix: Fixed an issue where the hyphen/dash wasn't hidden with the event end date when using a specific date format
* Fix: Fixed an issue with the height of photos in a grid when multiple grids were on the same page but with different numbers of columns
* Fix: Updated the icon font link to use SSL

= 2.5.15 =
* New: Events posted on your timeline will now show the full event cover photo and use whichever layout you select on the 'Post Layout' settings page
* Fix: Fixed an issue with messages tags in some posts when using an Access Tokens created using a Facebook API 2.5 app
* Fix: Added a maximum width to images in the comments
* Fix: Fixed an issue with group events and albums not displaying due to a change in the recent Facebook API 2.5 update
* Fix: Added a check so that the plugin JavaScript isn't run twice even if it's included twice in the page

= 2.5.14 =
* Fix: Fixed an issue where if you had the plugin set to display more than 93 posts then it would result in an error due to a change in the recent Facebook API 2.5 update which limits the total amount of posts that can be requested
* Fix: Added a check to the top of the plugin's JavaScript file so that it isn't run twice if included in the page more than once

= 2.5.13 =
* Fix: If you're experiencing an issue with your Facebook feed not automatically updating successfully then please update the plugin and enable the following setting: Custom Facebook Feed > Customize > Misc > Misc Settings > Force cache to clear on interval. If you set this setting to 'Yes' then it should force your plugin cache to clear either every hour, 12 hours, or 24 hours, depending on how often you have the plugin set to check Facebook for new posts.

= 2.5.12 =
* Fix: Fixed an issue caused by the recent Facebook API 2.5 update where some posts would display post tags incorrectly
* Fix: Fixed an issue where shared links without a title would produce a PHP notice

= 2.5.11 =
* Fix: Fixed a positioning issue with the Facebook "Like Box / Page Plugin" widget caused by a recent Facebook update which was causing it to overlap on top of other content
* Fix: Fixed an issue caused by the recent Facebook API 2.5 update where the posts wouldn't display when using a brand new Access Token
* Fix: Hashtags containing Chinese characters are now linked
* Fix: Fixed an issue where the photo lightbox was ocassionally intefering with other lightboxes used on a website
* Tweak: Videos in the video grid layout can now be filtered using the plugin's 'Filter' settings
* Tweak: Added a timezone for Sydney, Australia
* Tweak: Removed the 'Featured Post ID' field from the Settings page when the extension is in use, as it makes more sense to just set the ID directly in the shortcode

= 2.5.10 =
* Fix: Fixed an issue caused by the WordPress 4.3 update where feeds from longer page IDs wouldn't update correctly due to the cache not clearing when expired

= 2.5.9 =
* New: Added comments replies. If a comment has replies then a link is displayed beneath it which allows you to show them. The 'Reply' and 'Replies' text can be translated on the plugin's 'Custom Text / Translate' tab.
* Tweak: Added a setting which allows you to manually change the request method used to fetch Facebook posts which is necessary for some server setups
* Tweak: Added the ability to use the [Date Range](https://smashballoon.com/extensions/date-range/) extension with either album or video feeds
* Fix: Fixed an issue caused by the recent Facebook API 2.4 update where some group photos wouldn't display correctly
* Fix: Fixed a minor issue with shared link posts where the post text was set to be linked to the Facebook post it would link to the shared link URL instead

= 2.5.8 =
* Fix: Fixed an issue with album feeds not displaying when using some Access Tokens due to a recent change in the Facebook API

= 2.5.7 =
* Fix: Added a workaround for a [bug in the Facebook API](https://developers.facebook.com/bugs/486654544831076/) which is causing issues displaying events and photo feeds

= 2.5.6 =
* New: Added a couple of new customization options for the Facebook Like Box/Page Plugin which allow you to select a small/slim header for the Like Box and hide the call-to-action button (if available)
* Tweak: The post "story" can now be hidden independently of the rest of the post text. Just add the following to the plugin's Custom CSS section to hide the post story: `#cff .cff-story{ display: none; }`. The post story is the text at the beginning of the post which describes the post, such as 'Smash Balloon created an event'.
* Tweak: User avatars in the comments now use a headshot silhouette icon until the Facebook profile picture is loaded
* Tweak: When using the [Album extension](https://smashballoon.com/extensions/album/) to display photos the filter and exfilter options can now be used to hide or show photos based on a string or hashtag in the photo description
* Fix: The plugin now works with Access Tokens which use the new recently-released version 2.4 of the Facebook API
* Fix: Fixed an issue with links in the post text in the pop-up lightbox not working correctly
* Fix: Fixed an issue with some post tags caused by the recent Facebook API 2.4 update
* Fix: Fixed an issue with shared link thumbnails not being displayed in the Safari web browser

= 2.5.5 =
* New: Display a grid of your latest Facebook videos directly from your Facebook Videos page/album. To do this just select 'Videos' as the only post type in the 'Post Types' section of the Customize page, or use the following shortcode `[custom-facebook-feed type=videos videosource=videospage]`.
* New: If a Facebook post contains an interactive Flash object then it will now be shown in the pop-up lightbox and can be interacted with directly on your website
* Tweak: When displaying events a 'See More' link is now added to the event details so that it can be expanded if needed. The text character limit is controlled by the 'Maximum Description Length' setting, or the `desclength` shortcode option.
* Tweak: Automatically link the event name to the event now rather than it having to be enabled on the plugin's 'Typography' settings page
* Fix: Fixed an issue with photos or albums not displaying under rare circumstances when set as the only post type
* Fix: Removed empty style tags from some elements
* Fix: The URLs used for the 'Share' icons are now encoded to prevent HTML validation errors

= 2.5.4 =
* New: Photos in the comments are now displayed
* Tweak: Added stricter CSS rules to the paragraphs within comments to prevent styling conflicts
* Fix: Links within post descriptions weren't opening in a new tab
* Fix: Fixed an issue which would cause an Facebook API 100 error when an older Access Tokens was used

= 2.5.3 =
* New: Added an option to display the full-size images for shared link posts. This can be enabled at: Customize > Typography > Shared Links, or by using the following shortcode options: fulllinkimages=true
* New: The pop-up lightbox now contains the full text from the post and maintains all links and tags
* Tweak: Added video poster images back in so that all videos display an image initially before being played
* Tweak: When a post contains a Facebook video then move the name of the video to after the post story
* Fix: Hashtags which contain foreign chracters are now correctly linked
* Fix: Fixed an issue where photo attachments displayed in the pop-up lightbox would be displayed from the album the photos were added to, rather than from the post itself
* Fix: Fixed an issue which was causing the event details not to display for event posts on your timeline
* Fix: Removed some line breaks after the post text of some posts which was causing a gap
* Fix: Emjois in comment text are now displayed correctly inline if the theme supports them

= 2.5.2 =
* Fix: Fixed an issue where the additional photo thumbnails weren't appearing in the lightbox for some posts/albums

= 2.5.1 =
* Fix: Fixed an issue where the number of likes for some posts was displayed as zero
* Fix: Fixed an issue where the number of posts displayed was off by one

= 2.5 =
* New: Replace the 'Like Box' with the new Facebook 'Page Plugin' as the Like Box will be deprecated on June 23rd, 2015. Settings can be found under the Misc tab on the plugin's Customize page.
* Tweak: When displaying events, if there are no upcoming events then the message 'No upcoming events' is now displayed. This can be changed or translated on the plugin's 'Custom Text / Translate' settings page.
* Tweak: Now always displays the post "story" first in the post text if it's available
* Tweak: Applied the 'locale' to albums so that the default album names, like 'Timeline Photos' are now translated correctly
* Tweak: The 'Share' link is now added to events when displayed from your Facebook Events page
* Tweak: The 'filer' feature is now also applied to the photo stream when displaying photos from your Facebook Photos page
* Tweak: Added the Access Token to the end of the Facebook API request for the photo stream
* Tweak: Removed the number from the icon which appears on posts which contain more than one photo, as a change in the Facebook API means it's no longer possible to get this number accurately
* Tweak: Add some stricter CSS to some parts of the feed to prevent theme conflicts
* Fix: The individual caption is now shown for each photo in an album when viewed in the pop-up lightbox
* Fix: Fixed an issue caused by a Facebook API change where the post photo attachments wouldn't be displayed for some posts
* Fix: Shared posts now link to the new shared post and not to the original post that was shared
* Fix: The 'photos' text is now translated correctly when displaying only albums
* Fix: The exclude filter setting is now also applied to albums
* Fix: Fixed an issue with the Vimeo embed code due to a change in the Vimeo link format
* Fix: Fixed an issue where some HTML entities were disrupting the application of the post tags
* Fix: The 'offset' setting now works correctly when only displaying a specific post type and when displaying low numbers of posts
* Fix: Fixed an issue with the Multifeed extension not working correctly when displaying just 1 or 2 posts
* Fix: Completely removed the 'Error Reporting' option as it was causing issues with some theme options
* Fix: Corrected a minor issue with the plugin caching string
* Fix: The Extensions page is now hardcoded so that it no longer makes a JSON request to smashballoon.com
* Fix: Made some minor changes based on the deprecation of the Facebook API 1.0

= 2.4.8 =
* New: Added support for the SoundCloud audio player. Any SoundCloud files will now automatically be embedded into your posts.
* Fix: Fixed an issue with the layout of some timeline events
* Fix: Fixed an issue with the mobile layout for event-only feeds
* Fix: Removed some stray PHP notices
* Fix: Removed a line of code which was disabling WordPress Debug/Error Reporting. If needed, this can be disabled again by using the setting at the bottom of the plugin's 'Misc' settings page.

= 2.4.7 =
* New: Added a setting to load a local copy of the icon font instead of the CDN version, or to not load the icon font at all if it's already included in your site. This can be found at the bottom of the 'Misc' settings page.
* Fix: Added support for Vimeo videos which are embedded into the original Facebook post using shortened URLs, such as http://spr.ly
* Fix: Fixed a rare bug which was causing the WordPress admin section to load very slowly for a few users whose site's IP addresses were blocked by our web host 
* Fix: Removed query string from the end of CSS and JavaScript file references and replaced it with the wp_enqueue_script 'ver' parameter instead
* Fix: Removed some PHP notices inadvertently introduced in the last update

= 2.4.6 =
* New: Added an email link to the sharing icons
* Fix: Added a workaround for Facebook changing the event URLs in their API from absolute to relative URLs
* Fix: Facebook removed the 'relevant_count' parameter from their API so added a workaround to get the number of photos attached to a post
* Fix: Removed video poster images as the images in the Facebook API weren't high enough quality
* Fix: Added a workaround for 'story_tags' which Facebook deprecated from their API

= 2.4.5 =
* Tweak: Changed the jQuery 'resize' function used in the plugin which was causing issues with some WordPress themes
* Tweak: Removed the 'frameborder=0' parameter from the video iframes as it's been deppreciated in HTML5. The border is now removed using CSS.
* Fix: Fixed a bug where lightbox captions would be cut off when they included double quotes
* Fix: Fixed a bug where the shortcode 'num' option wasn't working correctly when showing a photos-only feed
* Fix: Fixed an issue with padding and margin not being automatically applied to Event feeds when adding a background color to the events
* Fix: Fixed a bug where a forward slash was missing from some URLs in the 'View on Facebook' link within the pop-up lightbox
* Fix: Fixed an issue where the full event end date was being shown even if the event ended on the same day which it started
* Fix: Fixed a formatting issue on posts which have been shared from inside an event to the Facebook page timeline
* Fix: Added a check to the file_get_contents data retrieval method to check whether the Open SSL wrapper is enabled
* Fix: Added the post limit to the caching string to prevent a rare issue with the same cache being pulled for multiple feeds
* Fix: Fixed a bug where the comments weren't showing up for events on your timeline
* Fix: The `eventtitlelink` shortcode option now works correctly
* Fix: The `offset` shortcode option now works when only displaying events
* Fix: Fixed a rare issue where past events from a Facebook page would display very old events first

= 2.4.4 =
* Fix: Reversed a bug introduced in the last update where the plugin would check for updates on page in the WordPress admin area which caused slower page load time
* Fix: Fixed an issue with displaying group events

= 2.4.3 =
* New: Added previous/next navigation to the pop-up photo/video lightbox
* Tweak: Added some missing settings to the System Info section
* Tweak: Added the plugin license type to the plugin name
* Tweak: Added a prefix to the IDs on all posts so that they can now be targeted via CSS
* Tweak: Updated the plugin update script
* Fix: Fixed an issue with the caption and 'View on Facebook' link not showing up in the pop-up lightbox for some photos
* Fix: Removed duplicate IDs on the share icons
* Fix: Added a fix for a wpautop content formatting issue caused by some themes
* Fix: Changed the event handlers on some parts of the feed so that they continue to work after splitting the feed into two columns

= 2.4.2 =
* Tweak: Extended the plugin's "Filter" function to album names when you're displaying albums from your Facebook Photos page
* Tweak: Added an option to disable the Ajax caching added in version 2.4. This can be found at the bottom of 'Customize > Misc'.
* Tweak: Added "nofollow" to all links by default. This can be disabled by using `nofollow=false` in the shortcode.
* Fix: Fixed an issue with Vimeo videos not autoplaying in the video lightbox in the Firefox browser
* Fix: Fixed a rare issue where the likes and comments box would load a 404 error
* Fix: Fixed a minor bug with the album options not being displayed initially on the Customize page when selecting albums as the only post type

= 2.4.1 =
* Fix: Fixed an issue with old events showing up in the events feed
* Fix: Fixed a minor bug in the WP_Http fallback method

= 2.4 =
* New: You can now view photos directly on your site in a popup lightbox. Just click on the photo in the post to view it in the lightbox. This can be disabled on the plugin's Customize page, or by using `disablelightbox=true` in the shortcode.
* New: When a post contains more than 1 photo you can now view the other photos attached to the post in the popup photo lightbox
* New: When displaying a grid of your Facebook albums you can now view the contents of the album in the popup photo lightbox by clicking on the photo
* New: All videos (Facebook, YouTube and Vimeo) can now be played at full-size on your site in a popup video lightbox
* New: Added a share link which allows you to share posts to Facebook, Twitter, Google+ or LinkedIn. This can be disabled at the very bottom of the Typography tab, or by using `showsharelink=false` in the shortcode.
* New: Videos can now either all be played directly in the feed or link to the post on Facebook
* New: The number of likes and comments is now cached in the database to prevent having to retrieve them from Facebook on every page load
* New: If you only want to display the Facebook Like Box and no posts then you can now just set the number of posts to be zero: [custom-facebook-feed num=0]
* New: Added a unique ID to albums, events and photos so that they can be targeted individually or hidden
* New: You can now use the Date Range extension to show posts from a relative/moving date. For example, you can show all posts from the past week by using [custom-facebook-feed from="-1 week" until="now"]

* Tweak: Updated the plugin to use the latest version of the Facebook API
* Tweak: Using your own Facebook Access Token in the plugin is still optional but is now recommended in order to protect yourself against future Access Token related issues
* Tweak: Improved cross-theme CSS consistency
* Tweak: Increased the accuracy of the character count when links are included in the text
* Tweak: Replaced the rel attribute with the HTML5 data attribute when storing data on an element
* Tweak: Added HTTPS stream wrapper check to the System Info to aid in troubleshooting
* Tweak: Updated the plugin's icon font to the latest version
* Tweak: Tweaked the mobile layout of the feed
* Tweak: Updated the plugin updater script
* Tweak: Added the Smash Balloon logo to the credit link which can be optionally displayed at the bottom of your feed. The setting for this is at the bottom of the Misc tab on the Customize page.
* Tweak: Added a shortcode option to only show the Smash Balloon credit link on certain feeds: [custom-facebook-feed credit=true]

* Fix: Reworked the jQuery click function in order to preserve event handlers when splitting the feed into two columns
* Fix: Added error handling to the likes and comments count script in order to fail gracefully if an error occurs
* Fix: Fixed an issue with quotes being escaped in custom/translated text
* Fix: Display an error message if WPHTTP function isn't working correctly
* Fix: Fixed an issue with the license key renewal notice being displayed if you entered an incorrect license key
* Fix: The `postbgcolor` shortcode option is now working correctly
* Fix: Fixed and issue with the dark likes and comments icons not being displayed

= 2.3.2 =
* Fix: Fixed a Facebook application issue which sporadically produced an 'Application request limit reached' error

= 2.3.1 =
* Fix: Fixed a JavaScript error which occurs if a Facebook post doesn't contain any text
* Fix: Fixed an issue with the link color not being applied to links in description text

= 2.3 =
* New: Added a 24 hour clock event date format
* New: Added a text area to the Support tab which contains all of the plugin settings and site info for easier troubleshooting
* Tweak: Removed the 'Buy Tickets' link from events as Facebook removed this from their API
* Tweak: Changed the default event date format to be Jul 25, 2013
* Tweak: If the user doesn't add a unit to the width, height or padding then automatically add 'px'
* Tweak: Added social media sharing links to the bottom of the settings page and an option to add a credit link to the bottom of the feed
* Fix: Fixed an issue with posts not always appearing after first installing the plugin due to an issue with the plugin activation function
* Fix: Fixed an issue with hashtags not being linked when followed immediately by punctuation
* Fix: Facebook group events can now be displayed again, but require a ["User" Access Token](https://smashballoon.com/custom-facebook-feed/docs/get-extended-facebook-user-access-token/)
* Fix: When displaying a shared link if the caption is the same as the link URL then don't display it
* Fix: Added a space before the feed header's style attribute to remove HTML validation error
* Fix: Fixed a bug when selecting the 'Always use the Full-width layout when feed is narrow?' setting which caused it not to be applied to more than one feed on a page
* Fix: Strip HTML tags from captions when used in the image alt tag
* Fix: Prefixed the 'top' and 'bottom' classes used on the Like box to prevent CSS conflicts
* Fix: Fixed a bug with the Event Date 'Text Weight' setting not being applied correctly

= 2.2.1 =
* Fix: Fixed an bug introduced in the last update with events sometimes appearing in random order

= 2.2 =
* New: Added a shortcode option to allow you to offset the number of posts to be shown. Eg: offset=2
* New: Added a Spanish translation - thanks to [Andrew Kurtis](http://www.webhostinghub.com)
* Tweak: If the event end date is the same as the start date then show the end time rather than the entire date
* Tweak: The date 'Timezone' setting is now also included on the plugin's Settings page
* Tweak: Added a note to the Events only options showing how to use the 'pastevents=true' shortcode option
* Tweak: Now renders the plugin's JavaScript variables in the head of your page to prevent issues with themes that render files in the wp_footer function in reverse
* Fix: Fixed an issue with events which have the exact same date and start time not both being displayed
* Fix: Added closing tags when displaying an error message
* Fix: Added some fixes for the [Lightbox extension](https://smashballoon.com/extensions/lightbox/)
* Fix: Added a fix for the [Multifeed extension](https://smashballoon.com/extensions/multifeed/) which was causing an error message to occur if posts weren't available from any one of the Page IDs used
* Fix: Now displays a notification when activating the Pro version if the free version is already installed

= 2.1.1 =
* Tweak: If using the thumbnail layout then any HTML5 videos in your feed smaller than 150px wide are automatically expanded when played to improve their watchability
* Fix: Fixed an issue with upcoming and past page events using the same cached data

= 2.1 =
* New: You can now display a feed of the past events from your Facebook page by using the 'pastevents' shortcode option, like so: [custom-facebook-feed type=events pastevents=true]
* New: Added support for the new [Lightbox](https://smashballoon.com/extensions/lightbox/) extension which allows you to view photos in your feed in a popup lightbox directly on your website
* Tweak: Improved the license key checking procedure to speed up the loading of the plugin's settings pages
* Fix: Fixed a bug which was causing the License page to display as blank on occasion
* Fix: Fixed a rare bug when checking whether extensions were activated or not
* Fix: Removed some stray PHP notices when display only the photos post type

= 2.0.1 =
* Tweak: If the post author is being hidden then change the default date position to be the bottom of the post
* Tweak: Added some default character limits to the post text and descriptions
* Fix: Fixed an issue with the date not being hidden when unchecked in the Show/Hide section
* Fix: Fixed an issue with the 'seconds' custom text string not being saved correctly
* Fix: Fixed issue with the order of photos in the Album extension

= 2.0 =
* New: Added avatar images to comments
* New: Added an HTML5 video player to videos which aren't YouTube or Vimeo, so that they can be played directly in the feed. If the web browser doesn't support HTML5 video then it just links to the video on Facebook instead.
* New: Added an option to display the post date immediately below the author name - as it is on Facebook. This is now the default date position.
* New: Added options to add a background color and rounded corners to your posts
* New: Updated the like, share and comment icons to match Facebook's new design
* New: Added an option to reveal the comments box below each post by default
* New: Added an option to select how many comments to show initially below each post
* New: You can now display photos directly from your Facebook Photos page by setting the post type to be Photos and the Photos Source to be your Photos page. This can be done on the plugin's 'Post Layout' settings page, or directly in the shortcode: [custom-facebook-feed type=photos photosource=photospage]
* New: Added an option to preserve/save your plugin options after uninstalling the plugin. This makes manually updating the plugin much easier.
* New: If your Facebook event has an end date then it will now be displayed after the start date
* New: Hashtags in the post descriptions are now also linked
* New: Added a 'Settings' link to the plugin on the Plugins page
* New: Added a license expiration notice and link which displays on the plugin page when your license is close to expiration
* New: Added a field to the Misc settings page which allows users to enter their Facebook App ID in order to remove a couple of browser console warnings caused by the Facebook Like box widget
* New: Tested and approved for the upcoming WordPress 4.0 release
* Tweak: Added informative error handling and an [Error Message reference](https://smashballoon.com/custom-facebook-feed/docs/errors/) to the website to make troubleshooting easier
* Tweak: If the Facebook API can't be reached by the plugin for some reason then it no longer caches the empty response and instead keeps trying to retrieve the posts from Facebook until it is successful
* Tweak: Removed the lines between comments
* Tweak: Reduced the size of the author avatar from 50px to 40px to match Facebook
* Tweak: Changed the title of the non-embedded video links to not be the file name
* Tweak: Added a checkbox to the Access Token field to select whether to use your Access Token or not
* Tweak: If there are comments then the comments box is now displayed at full-width
* Tweak: The link description text is now 12px in size by default
* Tweak: Added the 'Buy Tickets' link back to events
* Fix: Fixed an issue with Vimeo embed codes not working correctly when using shortened URLs
* Fix: The post author link is no longer the full width of the post and is only wrapped around the author image and name which helps prevent inadvertently clicking on the post author
* Fix: Now added alt tags to all photos
* Fix: Fixed an issue with some video thumbnails not being displayed
* Fix: Facebook offers now display images again
* Fix: Added the trim() function to the 'Test connection to Facebook API' function to improve reliability
* Fix: Fixed an occasional JavaScript error which occurred when the post text was hidden
* Fix: Fixed the 'View on Facebook' link for posts displayed using the [Featured posts](https://smashballoon.com/extensions/featured-post/) extension
* Fix: Added a fb-root element to the Like box to prevent a browser console warning
* Fix: When linking the post text to the Facebook post then linked hashtags no longer cause an issue
* Fix: When linking the post text to the Facebook post the correct text color is now applied
* Fix: Removed some unnecessary line breaks in Facebook offer posts
* Fix: Now open all event links in a new browser tab
* Fix: Removed some redundant inline CSS used on the posts
* Fix: Fixed an Internet Explorer 9 bug where link images were being displayed at too large of a size
* Fix: Removed some stray PHP notices which were being displayed on the plugin settings page

= 1.9.1.1 =
* Fix: Fixed an issue with hashtags in inline CSS being linked inadvertently

= 1.9.1 =
* New: Added support for the new 'Album' extension, which allows you to embed an album and display its photos
* New: Added a Facebook icon to the admin menu
* New: When only displaying the albums post type you can now choose whether to display albums from your timeline or Photos page
* Tweak: Featured Post extension - You can now use the 'type' shortcode option to set the type of the post you are featuring
* Fix: Fixed an issue with hashtags with punctuation immediately following them not being linked
* Fix: Corrected the left side margin on the "Like" box so that it aligns with posts

= 1.9.0 =
* New: Display a list of your albums directly from your Facebook Albums page
* New: Display albums in a single column or in a grid
* New: Hashtags in your posts are now linked to the hashtag search on Facebook. This can be disabled in the 'Post Text' section on the Typography setting page.
* Tweak: Added an HTML wrapper element around the feed
* Tweak: Added a few stricter CSS styles to help minimize the chance of theme stylesheets distorting post formatting
* Tweak: Vertically centered the header text
* Tweak: Added a span to the header text to allow CSS to be applied
* Tweak: Updated the license key activation script to be more reliable
* Fix: Fixed an issue with some photos displaying at a small size due to a change in Facebook's API
* Fix: Fixed an occasional issue affecting the thumbnail and half-width layouts
* Fix: Fixed an issue with link colors not being applied to all links
* Fix: Fixed a rare connection issue when trying to retrieve the number of likes and comments for posts
* Fix: Corrected an occasional issue with shared link information not being displayed
* Fix: Fixed an issue with a generic function name which was occasionally causing an error

= 1.8.3 =
* Fix: If a Vimeo link doesn't have an embedable video accompanying it then don't show the 'Sorry video is not available text'

= 1.8.2 =
* Fix: Fixed a bug with the post author text bumping down below the author image in the Firefox browser

= 1.8.1 =
* New: Added an option to set a height on the Like box. This allows you to display more faces of your fans if you have that option selected.
* Fix: Automatically strips the 'autoplay' parameter from the end of YouTube videos so that they don't autoplay in the feed
* Fix: Fixed a minor issue with post author text width in IE8

= 1.8.0 =
* New: You can now use the Filter feature to exclude posts containing a certain string or hashtag
* New: Added an option to display the photo/video above the post text when using the Full-width layout
* New: Added background and border styling options to shared links
* New: The post layout now defaults to Full-width in narrow columns or on mobile. This can be disabled on the Post Layout tab.
* Tweak: Embedded videos now use the same layout as non-embedded videos
* Tweak: Improved the reliability of the post tags linking
* Tweak: Changed the CSS clearing method to be more reliable
* Tweak: The Filter feature now only strips whitespace from the beginning of strings to allow you to add a space to the end of words
* Tweak: Reduced the clickable area of the post author
* Fix: Added title and alt tags to post author image
* Fix: Fixed issue with &amp; and &quot; symbols
* Fix: Fixed an issue with line breaks not being respected in IE8
* Fix: Fixed an issue with some video titles not appearing when post text is linked
* Fix: Corrected a bug where icon fonts were sometimes rendered italicized
* Compatible with WordPress 3.9

= 1.7.0.2 =
* Fix: Fixed a bug with post text sometimes being duplicated when linked
* Fix: Now adds a 'http' protocol to links starting with 'www'

= 1.7.0.1 =
* Fix: Fixed an issue with likes and comment counts loading in 1.7.0

= 1.7.0 =
* New: Added the ability to change the text size and color of the post author
* New: Define the format, size and color of the shared link title
* New: You can now define the color of the links in your post text, descriptions and events
* Tweak: The icon that appears on album photos now contains the number of photos in the album
* Tweak: Changed the loader for the like and comment counts
* Tweak: Improved the likes, share and comment icons to work better with different background colors
* Tweak: Moved the Feed Header options to the Typography page
* Tweak: Moved the Ajax setting to the Misc page
* Tweak: Now removes any query strings attached to the Page ID
* Tweak: The plugin now uses a built-in shared Access Token
* Fix: Fixed an issue with HTML characters not rendering correctly when linking the post text
* Fix: Fixed an issue with some themes causing the clear element to prevent links being clickable
* Fix: The photo in an album post now links to the album post again. Accommodates the change in Facebook's photo link structure.

= 1.6.2 =
* New: Added support for the 'music' post type
* Fix: Fixed minor issue with link replacement method introduced in 1.6.1

= 1.6.1 =
* Tweak: Event timeline images are now higher quality and the same size as thumbnail photos
* Tweak: Now display the video name above the post text when displaying non-embedded video posts
* Tweak: Changed the method used for link replacement in posts
* Tweak: Changed author and event timeline images back to loading via PHP rather than JavaScript due to issues with certain WordPress themes
* Fix: Disabled post tag linking when the post text is linked to the Facebook post
* Fix: Use a fallback JSON string if unable to find the cached version in the database

= 1.6.0 =
* New: Now supports post tags - creates links when using the @ symbol to tag other people or pages in your posts
* New: Added an 'exclude' shortcode option to allow you to easily exclude specific parts of the post
* New: Timeline events are now cached to help reduce page load time
* New: Added a new post type option for 'album' posts
* New: Choose to show the full event image or the square cropped version when displaying only events
* New: Added an option for when the WordPress theme is loading the feed via AJAX so that the JavaScript runs after the feed has been loaded into the page
* New: Added an 'accesstoken' shortcode option
* Tweak: Timeline event images are now loaded in via JavaScript after page load
* Tweak: The Filter option now also applies to events displayed from the Events page
* Tweak: Improvements to the show/hide option for customizing events from the Events page
* Tweak: Made the 'Link to Facebook video post' the default action for non-embedded video
* Tweak: Featured Post extension now utilizes caching
* Tweak: Featured Post extension improvements to photo posts
* Fix: Added a fix for the Facebook API 'Ticket URL' bug. Ticket URLs have been removed from events.
* Fix: Fixed a color picker JavaScript conflict that was occuring on rare occasions
* Fix: Reset the timezone after the shortcode has run
* Fix: When dark icons are selected then they now also apply to the icons within the dropdown comments box
* Fix: Fixed an issue with the shared link descriptions not being hidden when specified
* Fix: Fixed a rare issue with the 'textlink' shortcode option
* Fix: Added a WPAUTOP fix for album posts
* Fix: Fixed some minor IE quirks mode bugs

= 1.5.0 =
* New: Added a built-in color picker
* New: Added an Extensions page which displays available extensions for the plugin
* New: Added integration with the 'Multifeed' extension
* New: Added integration with the 'Date Range' extension
* New: Added integration with the 'Featured Post' extension
* Tweak: Now automatically set the post limit based on the number of posts to be displayed
* Tweak: Added class to posts based on the author so allow for independent styling
* Tweak: Now loads the author avatar image in using JavaScript to help speed up load times
* Tweak: Links in the post text now open in a new tab by default
* Tweak: Improved the Post Layout UI
* Tweak: Moved the License page to a tab on the Settings page
* Tweak: Created a Support tab on the Settings page
* Tweak: Improved the 'Test connection to Facebook API' function
* Tweak: Core improvements to the way posts are output
* Fix: Fixed an issue with photo captions not displaying under some circumstances

= 1.4.3 =
* New: Choose to display events from your Events page for up to 1 week after the start time has passed
* Tweak: Changed 'Layout & Style' page name to 'Customize'
* Fix: Added CSS box-sizing property to feed header so that padding doesn't increase its width
* Fix: Fixed showheader=false and headeroutside=false shortcode options
* Fix: Fixed include=author shortcode option
* Fix: More robust method for stripping the URL when user enters Facebook page URL instead of their Page ID
* Fix: Encode URLs so that they pass HTML validation

= 1.4.2 =
* New: Set your timezone so that dates/times are displayed in your local time
* Tweak: Description character limit now also applies to embedded video descriptions
* Fix: Fixed issue with linking the post text to the Facebook post
* Fix: Comments box styling now applies to the 'View previous comments' and 'Comment on Facebook' links
* Fix: Fixed the 'showauthor' shortcode option
* Fix: Added the ability to show or hide the author to the 'include' shortcode option
* Fix: Fixed issue with the comments box not expanding when there were no comments
* Fix: Now using HTML encoding to parse any raw HTML tags in the post text, descriptions or comments
* Fix: Fixed date width issue in IE7
* Fix: Added http protocol to the beginning of links which don't include it
* Fix: Fixed an issue with the venue link when showing events from the Events page
* Fix: Removed stray PHP notices
* Fix: Numerous other minor bug fixes

= 1.4.1 =
* Fix: Fixed some minor bugs introduced in 1.4.0
* Fix: Fixed issue with album names not always displaying
* Fix: Added cURL option to handle gzip compression

= 1.4.0 =
* New: Redesigned comment area to better match Facebook
* New: Now displays the number of likes a comment has
* New: Now shows 4 most recent comments and add a 'View older comments' button to show more
* New: Shows the names of who likes the post at the top of the comments section
* New: Added a 'Comment on Facebook' button at the bottom of the comments section
* New: Can now choose to show posts only by other people
* New: Added ability to add a customizable header to your feed
* New: Added a 'Custom Text / Translate' tab to house all customizable text
* New: Added an icon and CSS class to posts with multiple images
* New: When posting multiple images it states the number of photos after the post text
* New: When sharing photos or links it now states who you shared them from
* Tweak: String/hastag filtering now also applies to the description
* Tweak: Updated video play button to display more consistently across video sizes
* Tweak: Events will now still appear for 6 hours after their start time has passed
* Tweak: Added a button to test the connection to Facebook's API for easier troubleshooting
* Tweak: Plugin now detects whether the page is using SSL and pulls https resources
* Tweak: Post with multiple images now link to the album instead of the individual photo
* Tweak: WordPress 3.8 UI updates
* Fix: Fixed Vimeo embed issue
* Fix: Fixed issue with some event links due to a Facebook API change
* Fix: Fixed an issue with certain photos not displaying correctly

= 1.3.8 =
* New: Added a 'Custom JavaScript' section to allow you to add your own custom JavaScript or jQuery scripts

= 1.3.7.2 =
* Tweak: Changed site_url to plugins_url
* Fix: Fixed issue with enqueueing JavaScript file

= 1.3.7.1 =
* Tweak: Added option to remove border from the Like box when showing faces
* Tweak: Added ability to manually translate the '2 weeks ago' text
* Tweak: Checks whether the Access Token is inputted in the correct format
* Tweak: Replaced 'View Link' with 'View on Facebook' so that shared links now link to the Facebook post
* Fix: Fixed issue with certain embedded YouTube videos not playing correctly
* Fix: Fixed bug in the 'Show posts on my page by others' option

= 1.3.7 =
* New: Improved shared link and shared video layouts
* New: When only showing events you can now choose to display them from your Events page or timeline
* New: Set "Like" box text color to either blue or white
* Tweak: Displays image caption if no description is available
* Tweak: "Like" box is now responsive
* Tweak: Vertically center multi-line author names rather than bumping them down below the avatar
* Tweak: Various CSS formatting improvements
* Fix: If displaying a group then automatically hide the "Like" box
* Fix: 'others=false' shortcode option now working correctly
* Fix: Fixed formatting issue for videos without poster images
* Fix: Strip any white space characters from beginning or end of Access Token and Page ID

= 1.3.6 =
* Tweak: Embedded videos are now completely responsive
* Tweak: Now displays loading gif while loading in likes and comments counts
* Tweak: Improved documentation within the plugin
* Tweak: Changed order of methods used to retrieve feed data
* Fix: Corrected bug which caused the loading of likes and comments counts to sometimes fail

= 1.3.5 =
* New: Feed is now fully translatable into any language - added i18n support for date translation
* New: Now works with groups
* New: Added support for group events
* Fix: Resolved jQuery UI draggable bug which was causing issues in certain cases with drag and drop
* Fix: Fixed full-width event layout bug
* Fix: Fixed video play button positioning on videos with small poster images

= 1.3.4 =
* New: Added localization support. Full support for various languages coming soon.
* Fix: Fixed an issue regarding statuses linking to the wrong page ID

= 1.3.3 =
* New: Post filtering by string: Ability to display posts based on whether they contain a particular string or #hashtag
* New: Option to link statuses to either the status post itself or the directly to the page/timeline
* New: Added CSS classes to different post types to allow for different styling based on post type
* New: Added option to added thumbnail faces of fans to the Like box
* New: Define your own width for the Like box
* Tweak: Added separate classes to 'View on Facebook' and 'View Link' links so that they can be targeted with CSS
* Tweak: Prefixed every CSS class to prevent styling conflicts with theme stylesheets
* Tweak: Automatically deactivates license key when plugin is uninstalled

= 1.3.2 =
* New: Added support for Facebook 'Offers'
* Fix: Fixes an issue with the 'others' shortcode caused by caching introduced in 1.3.1
* Fix: Prefixed the 'clear' class to prevent conflicts

= 1.3.1 =
* New: Post caching now temporarily stores your post data in your WordPress database to allow for super quick load times
* New: Define your own caching time. Check for new posts every few seconds, minutes, hours or days. You decide.
* New: Display events directly from your Events page
* New: Display event image, customize the date, link to a map of the event location and show a 'Buy tickets' link
* Tweak: Improved layout of admin pages for easier customization
* Fix: Provided a fix for the Facebook API duplicate post bug

= 1.3.0 =
* New: Define your own custom text for the 'See More' and 'See Less' buttons
* New: Add your own CSS class to your feeds with the new shortcode 'class' option
* New: Show actual number of comments when there is more than 25, rather than just '25+'
* New: Define a post limit which is higher or lower than the default 25
* New: Include the Like box inside or outside of the feed's container
* Tweak: Made changes to the plugin to accomodate the October Facebook API changes
* Fix: Fixed bug which ocurred when multiple feeds are displayed on the same page with different text lengths defined

= 1.2.9 =
* New: Added a 'See More' link to expand any text which is longer than the character limit defined
* New: Choose to show posts by other people in your feed
* New: Option to show the post author's profile picture and name above each post
* New: Specify the format of the Event date
* Tweak: Default date format is less specific and better mimics Facebook's - credit Mark Bebbington
* Fix: When a photo album is shared it now links to the album itself and not just the cover photo
* Fix: Fixed issue with hyperlinks in post text which don't have a space before them not being converted to links
* Minor fixes

= 1.2.8 =
* Tweak: Added links to statuses which link to the Facebook page
* Tweak: Added classes to event date, location and description to allow custom styling
* Tweak: Removed 'Where' and 'When' text from events and made bold instead
* Tweak: Added custom stripos function for users who aren't running PHP5+

= 1.2.7 =
* Fix: Fixes the ability to hide the 'View on Facebook/View Link' text displayed with posts

= 1.2.6 =
* Fix: Prevents the WordPress wpautop bug from breaking some of the post layouts
* Fix: Event timezone fix when timezone migration is enabled

= 1.2.5 =
* Tweak: Replaced jQuery 'on' function with jQuery 'click' function to allow for compatibilty with older jQuery versions
* Minor bug fix regarding hyperlinking the post text

= 1.2.4 =
* New: Added a ton more shortcode options
* New: Added options to customize and format the date
* New: Add your own text before and after the date and in place of the 'View on Facebook' and 'View Link' links
* New: If there are no comments on a post then choose whether to hide the comment box or use your own custom text
* Tweak: Separated the video/photo descriptions and link descriptions into separate checkboxes in the Post Layout section
* Tweak: Changed the layout of the Typography section to allow for the additional options
* Tweak: Added a System Info section to the Settings page to allow for simpler debugging of issues related to PHP settings

= 1.2.3 =
* New: Choose to only show certain types of posts (eg. events, photos, videos, links)
* New: Add your own custom CSS to allow for even deeper customization
* New: Optionally link your post text to the Facebook post
* New: Optionally link your event title to the Facebook event page
* Fix: Only show the name of a photo or video if there is no accompanying text
* Some minor modifications

= 1.2.2 =
* Fix: Set all parts of the feed to display by default

= 1.2.1 =
* Select whether to hide or show certain parts of the posts
* Minor bug fixes

= 1.2.0 =
* Major Update!
* New: Loads of customization, layout and styling options for your feed
* New: Define feed width, height, padding and background color
* New: Choose from 3 preset post layouts; thumbnail, half-width, and full-width
* New: Change the font-size, font-weight and color of the post text, description, date, links and event details
* New: Style the comments text and background color
* New: Choose from light or dark icons
* New: Select whether the Like box is shown at the top of bottom of the feed
* New: Choose Like box background color
* New: Define the height of the video (if required)

= 1.1.1 =
* New: Shared events now display event details (name, location, date/time, description) directly in the feed

= 1.1.0 =
* New: Added embedded video support for youtu.be URLs
* New: Email addresses within the post text are now hyperlinked
* Fix: Links beginning with 'www' are now also hyperlinked

= 1.0.9 =
* Bug fixes

= 1.0.8 =
* New: Most recent comments are displayed directly below each post using the 'View Comments' button
* New: Added support for events - display the event details (name, location, date/time, description) directly in the feed
* Fix: Links within the post text are now hyperlinked

= 1.0.7 =
* Fix: Fixed issue with certain statuses not displaying correctly
* Fix: Now using the built-in WordPress HTTP API to get retrieve the Facebook data

= 1.0.6 =
* Fix: Now using cURL instead of file_get_contents to prevent issues with php.ini configuration on some web servers

= 1.0.5 =
* Fix: Fixed bug caused in previous update when specifying the number of posts to display

= 1.0.4 =
* Tweak: Prevented likes and comments by the page author showing up in the feed

= 1.0.3 =
* Tweak: Open links to Facebook in a new tab/window by default
* Fix: Added clear fix
* Fix: CSS image sizing fix

= 1.0.2 =
* New: Added ability to set a maximum length on both title and body text either on the plugin settings screen or directly in the shortcode

= 1.0.1 =
* Fix: Minor bug fixes.

= 1.0 =
* Launch!