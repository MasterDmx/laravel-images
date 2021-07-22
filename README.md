## Установка

1. Скачивание плагина
```
composer require masterdmx/laravel-relinking
```

2. Подключение провайдера в config app.php раздел providers
```php
MasterDmx\LaravelRelinking\RelinkingServiceProvider::class
```

3. Публикация конфига
```
php artisan vendor:publish --provider="MasterDmx\LaravelRelinking\RelinkingServiceProvider" --tag="config"
```

4. Публикация миграций
```
php artisan vendor:publish --provider="MasterDmx\LaravelRelinking\RelinkingServiceProvider" --tag="migrations"
```

5. Миграция
```
php artisan migrate
```

## Использование

### Подключение линкуемых моделей в конфиге `relinking.php`

```php
return [
    'linkable' => [
        App\Models\Post::class
    ],
];
```
Указание моделей в конфиге необходимо для работоспособности методов автоматической генерации перелинковки

### Подготовка модели, участвующей в перелинковке

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterDmx\LaravelRelinking\Contracts\Linkable;
use MasterDmx\LaravelRelinking\Traits\HasRelinking;

class Post extends Model implements Linkable
{
    use HasRelinking;

    public function linkableSearchText(): string
    {
        return $this->content;
    }
}
```

Метод `linkableSearchText` возвращает текст для полнотекстового поиска. Весь текст будет автоматически приведен к нижнему регистру + будет убрано все, кроме букв, цифр и пробелов.

