# CCEvent (Contao Composer Events)

Dieses Modul lässt Module nach der Installation und nach dem Update ausführen. Das kann vor allem in Entwicklungsumgebungen sinnvoll sein, um direkt nach der Installation ein Git-Repo einzurichten, um Vendor-Module zu bearbeiten.

Es ist nun möglich, bei der Installation einmalig eine Funktion auszuführen. Beispielsweise eine Anpassung in der Datenbank, verschieben von Dateien, leeren von eigenen Caches.

## How to use?

*Console*

    composer req sioweb/ccevent

*composer.json*

    "scripts": {
        "post-install-contao": [
            "Your\\Vendor\\Composer\\ScriptHandler::install"
            "ls -la"
        ],
        "post-update-contao": [
            "Your\\Vendor\\Composer\\ScriptHandler::update"
            "ls -la"
        ]
    }

