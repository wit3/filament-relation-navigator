# Receitas

Respostas curtas para configurações comuns. Cada uma usa só a API pública.

## Uma página só para algumas relações

Deixe o resource como está e ative as páginas nos relation managers que você quiser:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Páginas em vez de abas

Mantenha as relações nas próprias páginas e tire as abas da página de visualização. Links antigos para as abas (`?relation=orders`) levam às páginas:

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

O menu, "Abrir como página" e a pesquisa global continuam listando as páginas das relações: eles leem o `getRelations()` do resource.

## Os detalhes do registro acima de cada relação

Reaproveite o infolist do resource, ou uma parte dele:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Um resource sem página de visualização

As páginas das relações funcionam só com a página de lista. Linke-as a partir da tabela:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Elas exigem a policy `view` do registro, já que não há uma página de visualização para representar.

## Uma página de visualização com outra chave

`'details' => ViewCustomer::route('/{record}')` é encontrada pela classe. Com uma classe de página de relação sua, você também pode indicá-la: `protected static ?string $contextPage = 'details';`.

## Um link para uma página de relação de qualquer lugar

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Dentro de um tenant, de um cluster ou de um resource aninhado, o Filament preenche os parâmetros que faltam a partir da requisição atual, como em qualquer URL de resource.

## Um menu discreto

O menu da 1.2: o breadcrumb abre uma lista simples, sem pesquisa, sem badges, sem atalho.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Só para a equipe

Esconda uma página de relação por registro, ou para alguns usuários, no resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Vários painéis ou tenants

Nada a fazer: as rotas são registradas para cada painel que tem o resource, dentro do tenant e do cluster dele, e anterior/próximo guardam uma lista por painel, tenant e resource.
