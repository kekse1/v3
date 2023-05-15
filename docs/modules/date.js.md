
TODO

# date.js

Extensions ready. .. including own format strings, and more. :)~
Has some templates for nice time/date formats, but also support '%*' modifiers to declare a format by hand.

##### ..format();

###### Single char modifiers
Beneath some more functions, these are only the real '%' modifiers for format strings.
I tried to make them one single char only, .. otherwise we could extend the list, but no..

| Modifier | Time component / value |
| :------: | ---------------------: |
| %D | .dayInYear() |
| %y | .getFullYear() |
| %m |  month |
| %d |  day |
| %k | .weekInYear |
| %H |  hours |
| %h |  hours (%12) |
| %M |  minutes |
| %S |  seconds |
| %s |  milliseconds |
| %X |  unix timestamp (ms/1000) |
| %t |  'am'/'pm' |
| %T |  'AM'/'PM' |
| %N |  Name of month |
| %n |  Short name of month |
| %W |  Name of weekday |
| %w |  Short name of weekday |

###### Pre-defined formats (see 'css/date.css');

```css
	--date-now: '%H:%M:%S.%s';
	--date-time: '%H:%M:%S';
	--date-date: '%y-%m-%d';
	--date-default: '%y-%m-%d (%H:%M:%S)';
	--date-best: '%y-%m-%d (%H:%M:%S.%s)';
	--date-console: '%y-%m-%d %H:%M:%S.%s';
	--date-full: '%W, %y-%m-%d (%H:%M:%S)';
	--date-text: '%W, %d. %N %y (%H:%M:%S)';
	--date-text-full: '%W, %d. %N %y (%H:%M:%S.%s)';
	--date-year: '%D (%w)';
	--date-ms: '%x';
	--date-unix: '%X';
	--date-gmt: auto;
```
