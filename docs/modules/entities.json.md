
TODO

# entities.json
See the link [https://html.spec.whatwg.org/entities.json] (downloaded here), for `String.entities`, used
mainly by `String.prototype.text`. Will be asynchronously `require()`'d in 'js/string.js'; if not found
or smth. errornous happened, first the real URL will be tried, and if again an error (or the URL is not
defined in 'DEFAULT_ENTITIES[_URL]'), the system will use a very reduced version of the five 'pre-defined
entities' [ '&', '<', '>', '"', "'" ]..
