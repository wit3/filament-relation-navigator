# Recettes

Des réponses courtes aux configurations courantes. Chacune n'utilise que l'API publique.

## Une page pour certaines relations seulement

Laissez la ressource telle quelle et activez les pages sur les relation managers de votre choix :

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Des pages à la place des onglets

Gardez les relations sur leurs propres pages et retirez les onglets de la page d'affichage. Les anciens liens vers les onglets (`?relation=orders`) arrivent sur les pages :

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

Le menu, « Ouvrir comme page » et la recherche globale continuent de lister les pages de relation : ils lisent le `getRelations()` de la ressource.

## Les détails de l'enregistrement au-dessus de chaque relation

Réutilisez l'infolist de la ressource, ou une partie :

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Une ressource sans page d'affichage

Les pages de relation fonctionnent aussi avec une simple page de liste. Liez-les depuis la table :

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Elles exigent la policy `view` de l'enregistrement, puisqu'il n'y a pas de page d'affichage dont elles tiendraient lieu.

## Une page d'affichage sous une autre clé

`'details' => ViewCustomer::route('/{record}')` est trouvée par sa classe. Avec votre propre classe de page de relation, vous pouvez aussi la nommer : `protected static ?string $contextPage = 'details';`.

## Un lien vers une page de relation depuis n'importe où

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Dans un tenant, un cluster ou une ressource imbriquée, Filament complète les paramètres manquants à partir de la requête courante, comme pour toute URL de ressource.

## Un menu discret

Le menu de la 1.2 : le fil d'Ariane ouvre une liste simple, sans recherche, sans badges, sans raccourci.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Réservé à l'équipe

Masquez une page de relation par enregistrement, ou pour certains utilisateurs, sur la ressource :

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Plusieurs panels ou tenants

Rien à faire : les routes sont enregistrées pour chaque panel qui a la ressource, dans son tenant et son cluster, et précédent/suivant mémorisent une liste par panel, tenant et ressource.
