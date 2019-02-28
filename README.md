# CCEvent (<del>Contao</del> Composer Events)

Dieses Modul lässt Module nach der Installation und nach dem Update ausführen. Das kann vor allem in Entwicklungsumgebungen sinnvoll sein, um direkt nach der Installation ein Git-Repo einzurichten, um Vendor-Module zu bearbeiten.

Es ist nun möglich, bei der Installation einmalig eine Funktion auszuführen. Beispielsweise eine Anpassung in der Datenbank, verschieben von Dateien, leeren von eigenen Caches.

## Warum?

Meine Module liegen alle in einem privaten Repository, welches sich immer dann aktualisiert, wenn ich einen neuen Git-Tag pushe. Dadurch sind alle meine Plugins sauber installierbar, versioniert und von überall erreichbar. Damit ich diese auch leicht weiterentwickeln kann, brauche ich eine Funktion die mir in allen Plugins das Git-Repo installieren. So kann ich im Vendor-Verzeichnis arbeiten und danach ohne viel Aufwand in mein privates Repo pushen.

Kurz, mit diesem Plugin, in Verbindung mit [Apply Environment für Contao](https://github.com/Sioweb/ApplyEnvironment), kann ich bequem im Vendor-Verzeichnis arbeiten.

## How to use?

*Console*

    composer req sioweb/ccevent

*composer.json*

    "scripts": {
        "package-scripts": [
            "Your\\Vendor\\Composer\\CLASS_NAME::CLASS_METHOD"
            "ls -la"
        ],
    }
 
### Argumente übergeben

Es ist zwar unüblich, allerdings können den aufgerufenen Klassen Argumente übergeben werden. Dazu einfach wie folgt notieren in der composer.json:

    Your\\Vendor\\Composer\\ScriptHandler::install --ein-argument=foo --noch-eins=test
    
Die Argumente werden dann als Array übergeben und können dann wie in der unteren [Beispiel-Erweiterung](https://github.com/Sioweb/CCEventsExample) in ein besser nutzbares Array umformatiert werden. 

## Gibt es ein Beispiel?

Na klar, https://github.com/Sioweb/CCEventsExample ist ein fertiges Contao-Modul ohne funktion. Die Konsole gibt bei der Installation detailierte Informationen zu dem CCEvents-Beispiel aus. Um das Modul zu testen, reicht es einfach in Konsole das Paket zu installieren:

    composer req sioweb/cceventsexample

**Hinweis:** Die Ausgabe ist sehr lang und leicht chaotisch. Am besten wird die Ausgabe in einen Editor kopiert. Alle Funktionsausgaben sind mit zwei Tabs eingerückt und beginnen mit einem Minus (-)

## Können Scripte auch nur auf "localhost" oder "dev" beschränkt werden?

Ja. CCEvent geht alle Scripts der Reihe nach durch und prüft ob diese eine Art IF-Condition enthalten. Ist die Condition falsch, werden alle nachfolgenden Scripts ignoriert. Um ein Script als Condition zu markieren, muss es nach folgendem Muster aufgebaut werden: 

    @config.PARAMETER_NAME == 1
    @config.PARAMETER_NAME == true // wird später in 1 umgewandelt
    @config.PARAMETER_NAME >= 1 && config.PARAMETER_NAME <= 10 // Das zweite config benötigt kein @
    @config.PARAMETER_NAME == localhost

Unterstützt werden die Operatoren `[>, <, >=, <=, ==, !=]`

Die Conditions können mehrfach genutzt werden:

    "scripts": {
        "package-scripts": [
            "@config.PARAMETER_NAME > 10",
            "Your\\Vendor\\Composer\\CLASS_NAME::CLASS_METHOD"
            "@config.PARAMETER_NAME < 20",
            "ls -la"
        ],
    }
    
**Anmerkung:** Die Condition zieht sich die Daten aus der Composer `config.json`. Geplant ist, in Zukunft auch eine Environment-Variable prüfen zu können: `@environment.PARAMETER_NAME`. Verschachtelungen wie `@config.extra.PARAMETER_NAME` sind ebenfalls noch nicht möglich.

### Config.json

Die Datei liegt unter `$COMPOSER_HOME/config.json`, oder muss zumindest dort angelegt werden. Dort können nun Parameter notiert werden, welche in den Conditions verwendet werden sollen.

*Beispiel*
   
    {
        "config": {
            "localhost": true,
            "whatEnvironmentIsThis": "localhost"
            "WhatEver": 10
        }
    }
    
*Script Beispiel*

    @config.localhost == 1 && config.whatEnvironmentIsThis == "localhost" && config.WhatEver > 5

