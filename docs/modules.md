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

## [box.context.js](box.context.js.md)
TODO

## [box.grid.js](box.grid.js.md)
TODO

## [box.js](box.js.md)
TODO

## [box.menu.js](box.menu.js.md)
TODO

## [box.osd.js](box.osd.js.md)
TODO

## [box.popup.js](box.popup.js.md)
TODO

## [box.progress.js](box.progress.js.md)
TODO

## [camel.js](camel.js.md)
TODO

## [code.js](code.js.md)
TODO

## [color.js](color.js.md)
TODO

## [css.js](css.js.md)
TODO

## [css.matrix.js](css.matrix.js.md)
TODO

## [date.js](date.js.md)
TODO

## [document.js](document.js.md)
TODO

## [dynamic.js](dynamic.js.md)
TODO

## [dynamic.module.js](dynamic.module.js.md)
TODO

## [event.js](event.js.md)
TODO

## [extensions.js](extensions.js.md)
TODO

## [favicon.js](favicon.js.md)
TODO

## [geo.js](geo.js.md)
TODO

## [html.js](html.js.md)
TODO

## [id.js](id.js.md)
TODO

## [init.js](init.js.md)
TODO

## [intl.js](intl.js.md)
TODO

## [levenshtein.js](levenshtein.js.md)
TODO

## [location.js](location.js.md)
TODO

## [main.js](main.js.md)
TODO

## [math.js](math.js.md)
TODO

## [math.unit.js](math.unit.js.md)
TODO

## [navigator.js](navigator.js.md)
TODO

## [network.js](network.js.md)
TODO

## [numeric.js](numeric.js.md)
TODO

## [object.js](object.js.md)
TODO

## [page.js](page.js.md)
TODO

## [path.js](path.js.md)
TODO

## [radix.js](radix.js.md)
TODO

## [scrolling.js](scrolling.js.md)
TODO

## [sort.js](sort.js.md)
TODO

## [string.js](string.js.md)
TODO

## [timing.js](timing.js.md)
TODO

## [title.js](title.js.md)
TODO

## [uniform.js](uniform.js.md)
TODO

## [uptime.js](uptime.js.md)
TODO

## [url.js](url.js.md)
TODO

## [window.js](window.js.md)
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

