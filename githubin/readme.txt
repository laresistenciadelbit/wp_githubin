=== embed-githubin ===
Contributors: laresistenciadelbit
Tags: github embed
Requires at least: 2.7.0
Tested up to: 5.2.1
Requires PHP: 5.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Put github content in your wordpress site easily. Either in a post or a widget you can get a file list, repositories or a single file from github.

== Description ==
This plugin creates a shortcode and two buttons in the post editor 

For better embed visualization it automatically gets the mobile version content of github.

The buttons in the editor just print the shortcode in this format:

>[github_box url=\"PUT_GITHUB_URL_HERE\" border=\"radius\" style=\"box\" x=\"300\" y=\"300\" fgcolor=\"#333\" bgcolor=\"#fafafa\" disable_images=\"false\"]

>[github_box url=\"PUT_GITHUB_URL_HERE\" border=\"false\" style=\"none\" fgcolor=\"none\" bgcolor=\"none\" disable_images=\"false\"]

The only difference between one and the other is that the first (github_box) 
put the github content into a scrollable box and the github_content button leave the content
without the box.


The variables:

> url=\"PUT_GITHUB_URL_HERE\"

	Here you can put either a github user url, a repository url, a readme url or a file url.
	They are threated in different ways; user url will show its repositories;
	repository url will show its main files, readme and files will show its contents.
	

> border=\"radius\" OR border=\"true\"
	
	If we use one of this options will create a border with the content.

> style=\"box\"

	It will create a scrollable box with the content
	
> x=\"300\" y=\"300\"

	Those options are the dimensions of the box
	
> fgcolor=\"#333\" bgcolor=\"#fafafa\"

	Set the text color and background color
	
> disable_images=\"true\"

	Remove all images from the content
	
> disable_fav=\"true\"
	
	Remove the fav parragraphs from an user repositories
	
> id=\"IDNAME\"
	
	Creates a cached file of the content. This setting is very recomended to use.
	But remember: if you change later the attributes of the shortcode it won\'t update
	until the cachetime has passed or unless you delete the cached file (in plugin\'s directory).
	
> cachetime=\"10800\"	

	Number of seconds before it rebuilds the cache file (default is 10800seconds=3hours)

== Installation ==
just install it from wordpress.org plugins list or upload the plugin to the plugins folder.

== Screenshots ==
1. embed repositories list
2. embed repository file list
3. embed readme with or without images

== Changelog ==
* -remove coments

* -using internal http request API from wordpress instead CURL:
https://developer.wordpress.org/reference/classes/WP_Http/request/

* cleaning code

* -updated support to embed github files

* little change

* arreglos por cambio de fuentes en github

* avoid the rebuild cache time for localhost or some ip in $myserverip

* comment ps

* prevents loading cache file with size = 0

* -forgot to update a variable name

* readme: cache warning

* -added content caching:
  &  attributes for save cache as githubin_ with  seconds to rebuild the cache.

* evitamos m�s cajas de [github_box] dentro


* -fix: comparing file name with a larger extension name is disabled
-fix: plugin doesnt get content if no url has passed to it. (before, it got crazy with plugins like elementor which make a previous view of the post each few seconds)

* image resize

* readme changes

* image change