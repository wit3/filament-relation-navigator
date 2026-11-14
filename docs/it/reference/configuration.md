# Configurazione

Pubblica il file di config per cambiare i valori predefiniti di tutte le resource:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Ogni opzione del menu si può impostare anche su una resource (un metodo statico) o su una pagina (un metodo): vedi [Pagina, resource o config](/it/guide/menu-presets#pagina-resource-o-config). L'azione «Apri come pagina» si spegne anche per resource e per relation manager: vedi [Apri come pagina](/it/guide/open-as-page).

Ecco il file completo, come arriva con la versione attuale (i commenti sono in inglese):

<<< ../../../config/filament-relation-navigator.php
