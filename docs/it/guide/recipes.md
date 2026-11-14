# Ricette

Risposte brevi ai casi più comuni. Ognuna usa solo l'API pubblica.

## Una pagina solo per alcune relazioni

Lascia stare la resource e attiva le pagine sui relation manager che vuoi:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Pagine al posto delle schede

Tieni le relazioni nelle loro pagine e togli le schede dalla pagina di visualizzazione. I vecchi link alle schede (`?relation=orders`) portano alle pagine:

```php
// ViewCustomer
public function getRelationManagers(): array
{
    return [];
}
```

```php
// config/filament-relation-navigator.php
'redirect_relation_tabs' => true,
```

Menu, «Apri come pagina» e ricerca globale continuano a elencare le pagine delle relazioni: leggono `getRelations()` della resource.

## I dettagli del record sopra ogni relazione

Riusa l'infolist della resource, o una sua parte:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Una resource senza pagina di visualizzazione

Le pagine delle relazioni funzionano anche con la sola lista. Linkale dalla tabella:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Richiedono la policy `view` del record, perché non c'è una pagina di visualizzazione da rappresentare.

## Una pagina di visualizzazione con un'altra chiave

`'details' => ViewCustomer::route('/{record}')` viene trovata dalla sua classe. Con una tua classe pagina di relazione puoi anche indicarla: `protected static ?string $contextPage = 'details';`.

## Un link a una pagina di relazione da qualsiasi punto

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Dentro un tenant, un cluster o una resource annidata, Filament completa i parametri mancanti dalla richiesta corrente, come per qualsiasi URL di resource.

## Un menu essenziale

Il menu della 1.2: la breadcrumb apre una lista semplice, senza ricerca, badge né scorciatoia.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Solo per lo staff

Nascondi una pagina di relazione per record, o per alcuni utenti, sulla resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Più panel o tenant

Niente da fare: le rotte vengono registrate per ogni panel che ha la resource, dentro il suo tenant e cluster, e precedente/successivo ricordano una lista per panel, tenant e resource.
