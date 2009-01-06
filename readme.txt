=== Core Control ===
Contributors: dd32
Tags: 2.7, admin, filesystem, manager
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 0.7

Core Control is a set of plugin modules which can be used to control certain aspects of the WordPress Core.
Currently, Core Control features modules for managing Filesystem Access, Managing plugin/theme/core updates, Managing HTTP Transports & External HTTP Request logging.

== Description ==

Please Note: Core Control is mainly a Developers plugin, However it can be used by end users alike, Just realise, That novice users are not the initial target audience, and as such, this plugin (and its modules) may be more technical aimed.

Core Control is a set of plugin modules which can be used to control certain aspects of the WordPress control.
Currently, Core Control features modules for managing Filesystem Access, Managing plugin/theme/core updates, Managing HTTP Transports & External HTTP Request logging

Please Note: Internationalization is not currently.. there :) Future releases will 

= Filesystem Module =
As of WordPress 2.5, WordPress has included a Filesystem abstraction method which allows The Plugin upgrader, Plugin Installer, Core upgrader, Theme upgrader, and soon to be, Theme Installer, the ability to modify files on the server which WordPress lies via a few methods, Direct Filesystem access (Only available to few), SSH2 (To a select few who have installed a PHP Extension) and 2 FTP methods.

This module allows You to view which Method WordPress is using, and to disable problematic methods. It also provides a small bit of path debug information, which will be expanded upon in future releases.

= Upgrades Module =
This module is rather simple, and ugly, In short, It allows you to Disable/Enable Core, Plugin, and Theme update checking, It also allows you to force an update to occur instantly, Useful for when you're sure a new version has been released, but WordPress hasnt taken notice of it yet.

= HTTP Module =
As of WordPress 2.7, WordPress has included a new HTTP API, This simplifies the various splatterings of code used previously, everythings wrapped up together and it means that hopefully, all of WordPress's External HTTP bugs can be located in one easy to find place..

This module Allows you to view which transports are used for what purposes (GET/POST requests), Allows you to test the transport (It requests a file hosted on my web server, and checks the response is correct), And if a module is found to not work as expected, Allows you to disable a transport.

= HTTP Logging Module =
This module requires either WordPress trunk(2.8) or WordPress 2.7.1 (or the 2.7 branch until thats released)

The purpose of this module is to log all outgoing connections WordPress makes, It allows you to view the resulting data, as well as to view the time it has taken for each of the requests to be made.

= Cron Module =
This module is designed to allow you to view the WordPress Cron tasks which are currently scheduled to occur.

The Module allows you to run any task by clicking a link, and allows the cancelation of Once-Off scheduled tasks,  However it is not recomended unless you are sure of what you are doing.

Future revisions of this Module will most likely allow you to configure custom tasks as well for testing purposes.

== Frequently Asked Questions ==

= Who is this plugin aimed at? =
This plugin is primarily aimed at Developers, However, Its just as useable by novice users, just as long as you realise that the plugin -will- contain technical terms, and will not explain everything 100%, Its sort of a "If you know what this does, Here it is for you to use it, If not, leave it alone"

= Why are there no Questions here? =
Because no-one has asked me.. Ask me some questions! wordpress@dd32.id.au

== Change log ==

= 0.5 =
 * Original Alpha releases

= 0.6 =
 * Initial Public Release

= 0.7 =
 * HTTP Logger: Support Request Failures instead of erroring out..
 * Introduce the Cron Tasks Module

== Future Modules ==
These are only ideas, If you've got one you'd like added to the list, Get in touch with me :) wordpress@dd32.id.au

 * Cron Module, to manage cron tasks (Much like a more basic crontool)
 * Constants module, To manage the various optional constants that you can define to control parts of WordPress (Such as revisions, etc)
 * Role module, Well.. I'm just sick of the Role Manager plugin, Considering the idea of rolling a basic manager.

== Screenshots ==

1. Core Control main page
2. The Filesystem module
3. The Updates Module
4. The HTTP Module
5. The HTTP Logging module
6. The Cron Module