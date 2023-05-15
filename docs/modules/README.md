# Configuration

## css/
I'm massively using CSS "Custom Properties", also for the most configuration. That's good. :)~
Some are located in the 'config{,.responsive}.css', other (less important ones) in the 'variables.css',
the rest is spread over all the 'css/*.css'..

## config.json
This is the configuration which can't be integrated into css/.. mostly because the 'css.js' and 'extensions.js' first
need to be require()d. BUT some config variables may still be ported into the css/.. we'll see.

# Filesystem structure
This is the base filesystem structure which is really required (can reside in your web root and even a sub directory;
this is tested, and it works great).

After the following overview I'm going to explain you everything. And by the way: the **bold** names are real
directories or files, the *italic* ones are just symlinks ('symbolic links' ;).

## '[.]/'

* ./**cgi-bin**/
* ./**counter**/
* ./**css**/
* ./**fonts**/
* ./**home**/
* ./**img**/
* ./**js**/
* ./**json**/
* ./**scripts**/
* ./**status**/
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

### 'css/*****'
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
`location.hash` like `#~home`. My advice to you is: link them this way, or even with trailing '/' character, so they're
are loaded with 'DirectoryIndex' in mind.. so you can choose better, instead of directly linking to files with extensions..

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
See the whole 'JavaScript modules' section below, in the 'API Documentation' area.

## '**json**/'
See the whole 'JSON modules' section on the bottom, below the whole 'JavaScript modules' section, all in the
'API Documentation' section.

## '**scripts**/'
At the moment there are also the following scripts (which you could delete), but the most important should be the
'update.sh' for 'status/update.now', which is used to show a 'Last Update' info in the status bar (on the bottom).

### 'scripts/**update.sh**'
Described above (in this 'scripts/' sub section).

### 'scripts/**counter.php**'
Can be ignored or deleted. Just a copy of 'cgi-bin/php/counter.php'; this file here won't be really used, it's just
for a quick review for you, as github.com doesn't manage to show symlink contents (you just see the link target itself).

### 'scripts/**prompt.sh**'
I've created a linux/bash '$PS1' prompt which seems nice for me. You can use it, if you want! :)~
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

# API Documentation

## JavaScript modules
Here all the modules in 'js/' will be explained, or rather I'll write down here their exports.

'export' just means the public functions or properties for the globally accessable names. Real 'module.exports' can
be reached via the `library()` function(s) in 'main.js', but this is untested an NOT THAT EFFICIENT (if used without
callback, which makes async loading possible).

So here follows an overview of functions etc., which are nearly described in their own (.md) files (also linked below).

### [animation](animation.js.md)
TODO

### [area](area.js.md)
TODO

### [array](array.js.md)
TODO

### [bionic](bionic.js.md)
TODO

### [box](box.js.md)
TODO

### [box.context](box.context.js.md)
TODO

### [box.dialog](box.dialog.js.md)
TODO

### [box.grid](box.grid.js.md)
TODO

### [box.menu](box.menu.js.md)
TODO

### [box.osd](box.osd.js.md)
TODO

### [box.popup](box.popup.js.md)
TODO

### [box.progress](box.progress.js.md)
TODO

### [camel](camel.js.md)
TODO

### [code](code.js.md)
TODO

### [color](color.js.md)
TODO

### [css](css.js.md)
TODO

### [css.matrix](css.matrix.js.md)
TODO

### [date](date.js.md)
TODO

### [document](document.js.md)
TODO

### [dynamic](dynamic.js.md)
TODO

### [dynamic.module](dynamic.module.js.md)
TODO

### [event](event.js.md)
TODO

### [extensions](extensions.js.md)
TODO

### [favicon](favicon.js.md)
TODO

### [geo](geo.js.md)
TODO

### [html](html.js.md)
TODO

### [id](id.js.md)
TODO

### [init](init.js.md)
TODO

### [intl](intl.js.md)
TODO

### [levenshtein](levenshtein.js.md)
TODO

### [location](location.js.md)
TODO

### [main](main.js.md)
TODO

### [math](math.js.md)
TODO

### [math.unit](math.unit.js.md)
TODO

### [navigator](navigator.js.md)
TODO

### [network](network.js.md)
TODO

### [numeric](numeric.js.md)
TODO

### [object](object.js.md)
TODO

### [page](page.js.md)
TODO

### [path](path.js.md)
TODO

### [radix](radix.js.md)
TODO

### [scrolling](scrolling.js.md)
TODO

### [sort](sort.js.md)
TODO

### [string](string.js.md)
TODO

### [timing](timing.js.md)
TODO

### [title](title.js.md)
TODO

### [uniform](uniform.js.md)
TODO

### [uptime](uptime.js.md)
TODO

### [url](url.js.md)
TODO

### [window](window.js.md)
TODO


## JSON modules

### autoload.json
TODO

### color.json
TODO

### config.json
TODO

### entities.json
See the link [https://html.spec.whatwg.org/entities.json] (downloaded here), for `String.entities`, used
mainly by `String.prototype.text`.
TODO

### menu.json
See 'js/box.menu.js'..
TODO

### context.json
Same as in the '**img**/' directory: my default context menu (future(!) 'box.context.js') will be exactly
like the main menu, with links to all my 'home/' pages.. so this is just a symlink right now (if it'll be
replaced or not).

### version.json
TODO

