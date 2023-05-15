
TODO

# page.js

My pages are lying in the 'home/' directory and will be loaded (by the menu atm) by using a hash
'#~page'. This will show the contents of .txt and .html (animated, if wished) in the #MAIN element.

## 'home/' directory

Your sites/pages, mainly '.html' and '.txt'. These should be your contents itself, which are being loaded via the
`location.hash` like `#~home`. My advice to you is: link them this way, or even with trailing '/' character, so they're
are loaded with 'DirectoryIndex' in mind.. so you can choose better, instead of directly linking to files with extensions..

So, I created a directory for every site in here, and the content itself as 'home.{txt,html}' - whereas 'home' is configured
as first 'DirectoryIndex' in my '.htaccess' (which you can create/modify also in this directory, maybe even the root/base dir).

## Extraction (of <link>, <style> and <script>)

`<link> <script> <style>` elements will be extracted and created as DOM nodes; as it wasn't possible
for me to embed `<script>` (won't be executed, but '<' escaped). Such ressources can reside under the
'home/$page/' directory, and being embedded with '$CWD' (I'm altering the path for this relativity,
see also 'path.js' [verified path.normalize(), and it also works with URLs and their .pathname]);

Beware: only local scripts/styles can be loaded this way. Remote doesn't do anything (filtered out
this elements..) due to potential *CORS attacks*. :)~

## Insertion of LOCAL links

BTW: All LOCAL links will be inserted into the #MAIN element, without reloading the whole page (so
your pages should be only the `<body>` part ;)~ .. remote links are already opened in new tabs, as
`.target` attribute will automatically be set to `_blank`. AND if `.target` is defined, on the other
hand, the link will be loaded there nevertheless.. that's important if local sites with own header,
etc., or just outside the design. :)~

Utilizes my `ajax()` function.

