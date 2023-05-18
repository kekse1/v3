# url.js
This 'URL' extensions are already available in the `[window.]location` object (all but '.isKnownProtocol',
which is really not necessary there ;).

Current TODO:
* .args
* .argv

.args and .argv were already there, but I'm thinking 'bout using `new URLSearchParams(..)`. We'll see.

## URL.[prototype.]render()
Will render your URLs to a nice looking HTML code.. optionally with _options object, and if not every
options is defined there (if specified at all), they're defined by reading the CSS custom properties
in 'css/url.css'. ;)~

## URL.prototype.args
TODO (w/ or w/o 'URLSearchParams'??)

## URL.prototype.argv
TODO (w/ or w/o 'URLSearchParams'??)

## URL.prototype.base
The .href without .param (see below).

## URL.prototype.param
Both .hash and .search in one string.

## URL.resolve(_href)
Just a `new URL(_href, location.href)`.

## URL.create(_href, _resolve = false)
And this is without resolving current path, so `new URL(_href, location.origin)` (if not _resolve).

## URL.knownProtocols
Used for '.isKnownProtocol' (below): [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:', 'ftps:', 'ws:', 'wss:' ];

## URL.prototype.isKnownProtocol
See 'URL.knownProtocols' (above);

## URL.prototype.isLocalhost
See 'js/**network**.js'

## URL.prototype.isIP
See 'js/**network**.js'

## URL.prototype.isIPv4
See 'js/**network**.js'

## URL.prototype.isIPv6
See 'js/**network**.js'

