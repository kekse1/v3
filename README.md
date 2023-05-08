# kekse.biz
v3

.. that's the base version, as I guess my **kekse.biz** had at least(!) two prior versions before.
The real/full version of all the JavaScript's is available in the
[version.json](https://raw.githubusercontent.com/kekse1/kekse.biz/main/version.json).

## Preview
* https://kekse.biz/v3/test.html
* https://kekse.biz/v3/

## scripts/

### prompt.sh
![prompt.sh](docs/prompt.sh.png)

## Next steps

#### Responsible Design
That's how I want to design everything, to get it more 'responsible', too. Automatically align/size
everything, also for small, mobile web browsers..

The plan was to use a '*box*.js' (which is already there, btw), but planned to be used like regular
windows on your PC.. so that's not the final plan, as it's not so beautiful on mobile browsers. And
currently there are, btw, also '\*responsible\*.css', but they are going to be optimized as well.

#### Radix Sort and Radix conversions

#### Sort
As in JavaScript there's usually everything being sorted with the help of pure Integers (only real
floating point numbers - but they'll work here as well, due to their encoding..), the plan is to
use the 'best' sorting algorithm.

* https://codercorner.com/RadixSortRevisited.htm

#### Conversions
As already integrated in my [https://libjs.de/](lib[rary].js), the regular 36 radix maximum isn't
enough for me. It's even really useful that I'm supporting the 256 byte-code-radix (using all
the one-byte-characters)! :D~

Here's my current implementation (which is going to be ported to here!):
* https://libjs.de/lib/lib.js/ext/numeric.js

## Features

### Contents / 'page.js'/'menu.js'..
The menu (`js/box.menu.js`) is using `js/page.js` to load the ressources under './**home**/' (see
also the `json/config.json` - which is, btw, going to be completely replaced by CSS custom properties!

... utilizing the '#' hash/fragment of the URL, there's no necessity of reloading everything to
navigate through all the pages.

I also didn't forgot about `.txt` files beneath `.html`.. for the last doc-type, it wasn't possible
to embed `<script>` or `<style>` tags, so I manually filtered them out, to bring them to live on my
own (against cross-site-attacs or smth. like this it's only allowed for HTTP requests to the current
`location.host[name?]` ;)~ ..

#### Relativity
Now also supporting relative URLs.. just `<.. src/href="../test.css"...>` to load `home/test.css` (in
case you loaded a page via 'page.js', e.g. from the menu bar. :)~

## Modules

#### CSS
See `css.js` and `css.matrix.js`.. the first one is implementing the base, to handle CSS on my own
(I'll be using this as some 'pre-processor' or so in the future); it became necessary due to some
other extensions, like `..getVariable()` etc..

Whereas the `css.matrix.js` (to be finished, too) is there to help with the `transform()`-CSS-styles
and their encoding of the CSS functional styles like `scale{,X,Y}()` or `rotate{X,Y,Z}()`, etc..
atm the most important part is to recognize whether `rotate*` or `scale*` is enabled in the `matrix`
and `matrix3d` encoding value string of the `tranform` style..

I just did it by trying out and comparing the resulting matrices after changing these styles, .. in
the future there's going to be a real class for this, to handle any `transform` style with an
instance, so you can switch on/off them, etc.. ^_^

#### Scrolling
This is covered by the `box.osd.js`, so you gonna see the scrolling progress via showing up some on-screen-display a short time.

#### OSD (On Screen Display)
...

#### Web Animations API
Extended the base functionality massively, and fixed some things that seem like bugs! See 'animation.js' (and it's still in progress).

#### Date
Extensions ready. .. including own format strings, and more. :)~

#### Cookie
Some extensions to handle cookies better than via 'document.cookie' (manually coded)..

#### Popups
See 'box.popup.js'. Just set a string to the `.dataset.popup` of any HTML-Element, and it will magically be animated in/out/..
Including freeze mode/pause, better positioning, .. all (in the whole system!) with the modern 'Pointer Events API'.

#### Timing(.js)
Synchronized seconds, so many clocks (e.g.) will tick at the same time (so within a 1000ms threshold). You can manage multiple
timing elements, with some 'modulo'-argument to switch, e.g., every (%60) seconds. etc.

## Documentation...
It's too bad, but I didn't take care of neither a real documentation nor good comments in the code,
but if you want to use all this at your own site (..really? ^_^), feel free to comment or extend it
for yourself - I'd be happy about your commits here! :)~

## Configuration
A bit is still in a 'config.json' file, but the most parts are already integrated as "Custom CSS Properties" (which I'm accessing
via my `{document,element}.{get,set,has,*}Variable()` extensions. Take a look at the 'css/' path in here. :)~

## Links

### Library.js / lib.js
(Now) especially for the server-side, optimized to work with [Node.js](https://nodejs.org/).

* https://libjs.de/

See this web site with it's [Screenshots](https://libjs.de/#screenshots); you can also see there the
extensive use of ANSI Escape Sequences, they've got their own module [there](https://libjs.de/lib/lib.js/tty/ansi.js)
(whereas this is extended to the 'String' (atm also for the browser..));

BROWSER usage was integrated there, but I've came to the conclusion It'd be better do reduce it to
the server side. **This** here is the browser part, which I'll be using for my private website, soon. ^_^

For **one** reason: the library's _own_ `require()` is NOT async, as we need to wait for `module.exports`;
in the browser I already worked that way, but we had to preload the modules step by step.. this caused an
enormous latency! NOW I'm using just `<script>`-tags w/ `refer` enabled, so everything is loaded async;
thus: nearly NO delay! ;D~

### Bionic Reading
As this is just integrated in *my own implementation*, this is just to read a bit 'bout it (you can
see it when you click on a menu item, where the 'lorem ipsum' text appears. :)~

* https://www.heise.de/news/Bionic-Reading-Wie-eine-typografische-Methode-das-Web-lesbarer-machen-soll-7140358.html
* https://bionic-reading.com/

