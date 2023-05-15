# Index
* [Configuration](#Configuration)
* [Filesystem structure](#filesystem-structure)
* [API documentation](#api-documentation)

# Configuration

## css/
I'm massively using 'CSS Custom Properties', also for the most configuration. That's good. :)~
Some are located in the 'config{,.responsive}.css', other (less important ones) in the 'variables.css',
the rest is spread over all the 'css/*.css'..

It uses, btw, **my own** `...{get,set,has,*}Variable(..)` extensions (of 'js/extensions.js'), to handle
these 'CSS Custom Properties', and also the 'css.parse()' etc. of 'js/css.js' (which is going to be re-
written from scratch, very soon!).

## config.json
This is the configuration which can't be integrated into css/.. mostly because the 'css.js' and 'extensions.js' first
need to be require()d. BUT some config variables may still be ported into the css/.. we'll see.

# Filesystem structure
This is the base filesystem structure which is really required (can reside in your web root and even a sub directory;
this is tested, and it works great).

After the following overview I'm going to explain you everything. And by the way: the **bold** names are real
directories or files, the *italic* ones are just symlinks ('symbolic links' ;).

## '[.]/'

* ./**[cgi-bin](#cgi-bin)**/
* ./**[counter](#counter)**/
* ./**[css](#css-1)**/
* ./**[fonts](#fonts)**/
* ./**[home](#home)**/
* ./**[img](#img)**/
* ./**[js](#js)**/
* ./**[json](#json)**/
* ./**[scripts](#scripts)**/
* ./**[status](#status)**/
* ./*favicon.ico*
* ./*favicon.png*
* ./*main.css*
* ./**main.html**
* ./*main.js*
* ./*cursor.png*

## '**cgi-bin**/'
With './php/', to which we also could symlink in the base directory, if wanted.
Most important file in there is the 'cgi-bin/php/*counter.php*'!

## '**counter**/'
You better `chmod 1777` it. There must be a symlink 'cgi-bin/php/counter' to here.
Every hostname/domain will use on file in there. The .php script is 'cgi-bin/php/*counter.php*'.

## '**css**/'
Maybe of interest: for a 'responsive design' (so mobile browser access, etc.) there should be only special files,
which are for the regular '.css' files itself, which describe something, and a '*.responsive.css' version with
e.g. `@media only screen and (max-width: 767px)` etc.

### 'css/**main.css**'
This is symlinked in the base/root directory, and utilizes the `@import` 'at rule'. So there's only one
`<link rel="stylesheet" ...>` necessary in the 'main.html'.

### 'css/**font{,s}.css**'
See also the section about the 'fonts/' directory. With pre-configured my three favorite web fonts.

### 'css/**cursor.css**'
Shortly described in the 'img/' section below. My favorite default cursor.

It's worth to mention that every element (css `*`) is configured with `cursor: inherit;`, so the default cursor is
inherited by default, BUT if another default cursor needs to be used (resize, move, grab, etc.), it also *will* be
used then. So not everywhere! ;)~

### 'css/**system.css**'
Most basic design for this library and sites, etc.

### 'css/**date.css**'
This is especially for my 'js/date.js' extensions, right now with own `Date..format()` etc.
So here are atm the default pre-defined formats and the format which is used as default one (used when no argument(s)
defined at `.format()`).

### 'css/**scrollbars.css**'
My pre-configured favorite look.

### 'css/*'
Just look for yourself which files are also here. Partially described on top in the 'Configuration' section.
OK, here's the list of my *current* configuration:

* box.css
* box.responsive.css
* config.css
* config.responsive.css
* cursor.css
* date.css
* font.css
* fonts.css
* local.css
* **main.css** (symlinked in the base/root directory, and the only one to be `<link>`'d in the '/**main.html**'!)
* osd.css
* page.css
* scrollbars.css
* system.css
* system.responsive.css
* variables.css

## '**fonts**/'
Holding my most favorite web fonts (found them on Google Fonts). As there were CORS errors when loading them from the
Google servers, I've decided to download them, etc.. so I've extraced all the '.css' for them, and put them into one
'css/fonts.css' file.

## '**home**/'
Your sites/pages, mainly '.html' and '.txt'. These should be your contents itself, which are being loaded via the
`location.hash` plus '~' (for $HOME ;) .. like `#~home`. My advice to you is: link them this way, or even with trailing
'/' character, so they're are loaded with 'DirectoryIndex' in mind.. so you can choose better, instead of directly
linking to files with extensions..

So, I created a directory for every site in here, and the content itself as 'home.{txt,html}' - whereas 'home' is configured
as first 'DirectoryIndex' in my '.htaccess' (which you can create/modify also in this directory, maybe even the root/base dir).

## '**img**/'
A place for all your images. Feel free (or rather forced ;) to create sub directories for your reasons, or even outside
the library a whole new root/base directory for your own. BUT it's not recommended to create it in 'home/', which would
be aside your sites.. the 'home/' is intended for mainly '.html' and '.txt'..

### 'img/**favicon.{ico,png}**'
Should be present for *any* website. I recommend the size of 256x256px. And there should also be symlinks to 'img/..'
in the root and base path.

### 'img/**cursor.png**'
My favorite default cursor, looks beauty (referenced in 'css/cursor.css': `cursor: url('../cursor.png') 12 8, auto;`).

### 'img/**menu**/'
A directory for all the menu bar icons (see 'json/menu.json' and 'js/box.menu.js').

I've created them in a size of 512x512px, so I can detail them if necessary. My recommendation for the 'menu[.json]' is
the 128x128px version - that reduces the initial loading time!!

### 'img/**context**'
A symlink to the './**menu**/' directory. There'll be a '**box.context.js**' in the future, for own *Context Menu*s,
and the default context menu will base on the default menu bar. This is why there's this symlink. ^_^

## '**js**/'
See the whole '**[JavaScript modules](#javascript-modules)**' section below, in the
'**[API Documentation](#api-documentation)**' area.

## '**json**/'
See the whole '**JSON modules**' section on the bottom, below the whole
'**[JSON modules](#json-modules)**' section, all in the '**[API Documentation](#api-documentation)**' section..

## '**scripts**/'
At the moment there are also the following scripts (which you could delete), but the most important should be the
'update.sh' for 'status/update.now', which is used to show a 'Last Update' info in the status bar (on the bottom).

### 'scripts/**update.sh**'
Described above (in this 'scripts/' sub section).

### 'scripts/**counter.php**'
Can be ignored or deleted. Just a copy of 'cgi-bin/php/counter.php'; this file here won't be really used, it's just
for a quick review for you, as github.com doesn't manage to show symlink contents (you just see the link target itself).

### 'scripts/**prompt.sh**'
I've created a linux/bash `**$PS1**` prompt which seems nice for me. You can use it, if you want! :)~
I really like this one!

### 'scripts/**tree.sh**'
More for myself, as I'm managing a '~/git/hardware/' archive of my possession (for drivers, manuals, or just a
list and the prices, etc.). For this reason I've created my own './home/hardware/' page, with text/plain output
of `tree (..)`.

### 'scripts/**up2date.sh**'
This is for Debian, Gentoo and Termux linux. All in one script for `emerge` and `apt` updates, etc.

### 'scripts/**update.sh**'
**Important for *you*!** See below: 'status/update.now' (this script created this file).

## '**status**/'
The files in here are periodically handled by the 'js/dynamic{,.module}.js'. As follows..

### 'status/**counter**/'
A symlink to the '../counter/' directory (see above).

### 'status/**update.now**'
This is just for an info in the status bar (on the bottom), when the last update has been commited.
A script is also ready for this (currently my 'git' shell scripts do it): 'scripts/update.sh'.

### 'status/**version.json**'
A symlink to '../json/version.json'. You can imagine why this is 'important'. ^_^

# API documentation

## JavaScript modules
Here will the modules in 'js/' be explained (TODO), or rather I'll write down here their exports.

'export' just means the public functions or properties for the globally accessable names. Real 'module.exports', like
in [Node.js](https://nodejs.org/) can be used via the `library()` function(s) in 'main.js' (using a parameter to base
`require()`), but this is yet UNTESTED an not that efficient (if used without callback, which makes async loading possible).

So here follows an overview of functions etc., which are nearly described in their own (.md) files (also linked below),
whereas every module created it's own (globally accessable) namespace.

### [animation](animation.js.md).js
TODO

### [area](area.js.md).js
TODO

### [array](array.js.md).js
TODO

### [bionic](bionic.js.md).js
TODO

### [box](box.js.md).js
TODO

### [box.context](box.context.js.md).js
TODO

### [box.dialog](box.dialog.js.md).js
TODO

### [box.grid](box.grid.js.md).js
TODO

### [box.menu](box.menu.js.md).js
TODO

### [box.osd](box.osd.js.md).js
TODO

### [box.popup](box.popup.js.md).js
TODO

### [box.progress](box.progress.js.md).js
TODO

### [camel](camel.js.md).js
TODO

### [code](code.js.md).js
TODO

### [color](color.js.md).js
TODO

### [css](css.js.md).js
TODO

### [css.matrix](css.matrix.js.md).js
TODO

### [date](date.js.md).js
TODO

### [document](document.js.md).js
TODO

### [dynamic](dynamic.js.md).js
TODO

### [dynamic.module](dynamic.module.js.md).js
TODO

### [event](event.js.md).js
TODO

### [extensions](extensions.js.md).js
TODO

### [favicon](favicon.js.md).js
TODO

### [geo](geo.js.md).js
TODO

### [html](html.js.md).js
TODO

### [id](id.js.md).js
TODO

### [init](init.js.md).js
TODO

### [intl](intl.js.md).js
TODO

### [levenshtein](levenshtein.js.md).js
TODO

### [location](location.js.md).js
TODO

### [main](main.js.md).js
TODO

### [math](math.js.md).js
TODO

### [math.unit](math.unit.js.md).js
TODO

### [navigator](navigator.js.md).js
TODO

### [network](network.js.md).js
TODO

### [numeric](numeric.js.md).js
TODO

### [object](object.js.md).js
TODO

### [page](page.js.md).js
TODO

### [path](path.js.md).js
TODO

### [radix](radix.js.md).js
TODO

### [scrolling](scrolling.js.md).js
TODO

### [sort](sort.js.md).js
TODO

### [string](string.js.md).js
TODO

### [timing](timing.js.md).js
TODO

### [title](title.js.md).js
TODO

### [uniform](uniform.js.md).js
TODO

### [uptime](uptime.js.md).js
TODO

### [url](url.js.md).js
TODO

### [window](window.js.md).js
TODO


## JSON modules
Some helping hands..

### [autoload](autoload.json.md).json
TODO

### [color](color.json.md).json
TODO

### [config](config.json.md).json
Look at the '**Configuration**' section, on top of *this* file!
TODO

### [entities](entities.json.md).json
See the link [https://html.spec.whatwg.org/entities.json] (downloaded here), for `String.entities`, used
mainly by `String.prototype.text`. Will be asynchronously `require()`'d in 'js/string.js'; if not found
or smth. errornous happened, first the real URL will be tried, and if again an error (or the URL is not
defined in 'DEFAULT_ENTITIES[_URL]'), the system will use a very reduced version of the five 'pre-defined
entities' [ '&', '<', '>', '"', "'" ]..

### [menu](menu.json.md).json
See also 'js/box.menu.js'.. etc.
TODO

### [context](context.json.md).json
Same as in the '**img**/' directory: my default context menu (future(!) 'box.context.js') will be exactly
like the main menu, with links to all my 'home/' pages.. so this is just a symlink right now (if it'll be
replaced or not).

### [version](version.json.md).json
TODO

