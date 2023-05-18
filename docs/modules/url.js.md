# url.js
TODO:
* .render()
* .args
* .argv

The .render() is a relict of old 'uniform.js', to **style** a link, etc.
.args and .argv were already there, but I'm thinking 'bout using `new URLSearchParams(..)`. We'll see.

## URL.prototype.base
The .href without .param (see below).

## URL.prototype.param
Both .hash and .search in one string.

## URL.resolve(_href)
Just a `new URL(_href, location.href)`.

## URL.create(_href, _resolve = false)
And this is without resolving current path, so `new URL(_href, location.origin)` (if not _resolve).

## URL.protocols
Used for '.isKnownProtocol' (below): [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:', 'ftps:', 'ws:', 'wss:' ];

## URL.prototype.isKnownProtocol
See 'URL.protocols' (above);

## URL.prototype.isLocalhost
See 'js/**network**.js'

## URL.prototype.isIP
See 'js/**network**.js'

## URL.prototype.isIPv4
See 'js/**network**.js'

## URL.prototype.isIPv6
See 'js/**network**.js'

