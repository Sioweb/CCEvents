# CCEvent (Contao Composer Events)

Dieses Modul lässt Module nach der Installation und nach dem Update ausführen. Das kann vor allem in Entwicklungsumgebungen sinnvoll sein, um direkt nach der Installation ein Git-Repo einzurichten, um Vendor-Module zu bearbeiten.

Es ist nun möglich, bei der Installation einmalig eine Funktion auszuführen. Beispielsweise eine Anpassung in der Datenbank, verschieben von Dateien, leeren von eigenen Caches.

## Warum?

Meine Module liegen alle in einem privaten Repository, welches sich immer dann aktualisiert, wenn ich einen neuen Git-Tag pushe. Dadurch sind alle meine Plugins sauber installierbar, versioniert und sind überall erreichbar. Damit ich diese auch leicht entwickeln kann, brauche ich eine Funktion die mir in allen Plugins das Git-Repo installieren. So kann ich im Vendor-Verzeichnis arbeiten und danach ohne viel Aufwand in mein privates Repo pushen.

## How to use?

*Console*

    composer req sioweb/ccevent

*composer.json*

    "scripts": {
        "post-install-contao": [
            "Your\\Vendor\\Composer\\ScriptHandler::install"
            "ls -la"
        ],
    }

## Gibt es ein Beispiel?

Na klar, https://github.com/Sioweb/CCEventsExample ist ein fertiges Contao-Modul ohne funktion. Die Konsole gibt bei der Installation detailierte Informationen zu dem CCEvents-Beispiel aus. Um das Modul zu testen, reicht es einfach in Konsole das Paket zu installieren:

    composer req sioweb/cceventsexample

**Hinweis:** Die Ausgabe ist sehr lang und leicht chaotisch. Am besten wird die Ausgabe in einen Editor kopiert. Alle Funktionsausgaben sind mit zwei Tabs eingerückt und beginnen mit einem Minus (-)