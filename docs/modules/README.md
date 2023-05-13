# Filesystem structure
(explain it..)

# Configuration

## css/
I'm massively using CSS "Custom Properties", also for the most configuration. That's good. :)~

## config.json
This is the configuration which can't be integrated into css/.. mostly because the 'css.js' and 'extensions.js' first
need to be require()d. BUT some config variables may still be ported into the css/.. we'll see.

# API Documentation / Modules
Here I'll explain you the JavaScript modules. Respective it's accessable functions/props/.. etc. via their global
name export, *not* the private members that can't be reached outside the module/file..

This are on the one hand the direct '=' exports, on the other hand all the 'Object.defineProperty()' to these global
modules (I'm using this many times, especially for the global module extensions (String, Number, etc..) - and btw.,
here I'm never using any 'Reflection...' methods or so, jfyi..).

Additionally, of course, some description of the modules will also been written down here. :)~

## [animation.js](animation.js.md)
TODO

## [area.js](area.js.md)
TODO

## [array.js](array.js.md)
TODO

## [bionic.js](bionic.js.md)
TODO

## [box.context.js](box.context.js)
TODO

## [box.grid.js](box.grid.js)
TODO

## [box.js](box.js)
TODO

## [box.menu.js](box.menu.js)
TODO

## [box.osd.js](box.osd.js)
TODO

## [box.popup.js](box.popup.js)
TODO

## [box.progress.js](box.progress.js)
TODO

## [camel.js](camel.js)
TODO

## [code.js](code.js)
TODO

## [color.js](color.js)
TODO

## [css.js](css.js)
TODO

## [css.matrix.js](css.matrix.js)
TODO

## [date.js](date.js)
TODO

## [document.js](document.js)
TODO

## [dynamic.js](dynamic.js)
TODO

## [dynamic.module.js](dynamic.module.js)
TODO

## [event.js](event.js)
TODO

## [extensions.js](extensions.js)
TODO

## [favicon.js](favicon.js)
TODO

## [geo.js](geo.js)
TODO

## [html.js](html.js)
TODO

## [id.js](id.js)
TODO

## [init.js](init.js)
TODO

## [intl.js](intl.js)
TODO

## [levenshtein.js](levenshtein.js)
TODO

## [location.js](location.js)
TODO

## [main.js](main.js)
TODO

## [math.js](math.js)
TODO

## [math.unit.js](math.unit.js)
TODO

## [navigator.js](navigator.js)
TODO

## [network.js](network.js)
TODO

## [numeric.js](numeric.js)
TODO

## [object.js](object.js)
TODO

## [page.js](page.js)
TODO

## [path.js](path.js)
TODO

## [radix.js](radix.js)
TODO

## [scrolling.js](scrolling.js)
TODO

## [sort.js](sort.js)
TODO

## [string.js](string.js)
TODO

## [timing.js](timing.js)
TODO

## [title.js](title.js)
TODO

## [uniform.js](uniform.js)
TODO

## [uptime.js](uptime.js)
TODO

## [url.js](url.js)
TODO

## [window.js](window.js)
TODO




### ChatGPT recommendations for Markdown (.md) documentations

#### Markdown-Struktur
Verwenden Sie eine klare Struktur, um die Dokumentation leicht lesbar und navigierbar zu machen. Verwenden Sie Überschriften, Absätze, Aufzählungen und Codeblöcke, um den Text übersichtlich zu gestalten.

#### Codebeispiele
Fügen Sie Codebeispiele hinzu, um die Verwendung der Bibliothek zu veranschaulichen. Verwenden Sie geeignete Formatierung für den Code, z. B. Einrückungen und Syntax-Hervorhebung, um ihn vom normalen Text abzuheben.

#### Installation und Anforderungen
Geben Sie klare Anweisungen zur Installation der Bibliothek sowie zu den erforderlichen Abhängigkeiten und Versionen an.

#### API-Dokumentation
Wenn Ihre Bibliothek eine API bereitstellt, dokumentieren Sie die verfügbaren Funktionen, ihre Parameter und Rückgabewerte. Erklären Sie auch die Verwendungszwecke und Besonderheiten jeder Funktion.

#### Beispielprojekte
Bieten Sie Beispielprojekte oder Tutorials an, um den Benutzern zu zeigen, wie sie Ihre Bibliothek in der Praxis einsetzen können.

#### Dokumentation der Konfigurationsoptionen
Wenn Ihre Bibliothek konfigurierbar ist, dokumentieren Sie die verfügbaren Optionen und ihre Auswirkungen. Geben Sie Beispiele für die Konfigurationsdateien oder -objekte an.

#### FAQ oder häufig gestellte Fragen
Sammeln Sie häufig gestellte Fragen und deren Antworten, um den Benutzern bei Problemen oder Unklarheiten zu helfen.

#### Links und Ressourcen
Fügen Sie Links zu relevanten Ressourcen hinzu, wie z. B. GitHub-Repository, Issue-Tracker, Support-Kanäle oder andere nützliche Artikel oder Tutorials.

#### Versionskontrolle
Wenn Ihre Bibliothek verschiedene Versionen hat, stellen Sie sicher, dass die Dokumentation für jede Version verfügbar ist. Geben Sie Anweisungen zum Wechseln zwischen den Versionen oder zum Anzeigen der Dokumentation für eine bestimmte Version an.

#### Stil und Formatierung
Verwenden Sie einen konsistenten Stil und eine kohärente Formatierung für die gesamte Dokumentation. Dies erleichtert das Lesen und Verstehen. Betonen Sie wichtige Informationen und verwenden Sie geeignete Absätze oder Aufzählungen, um den Text zu strukturieren.

