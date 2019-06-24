# Bitrix model


## Установка

```bash
    composer require alex19pov31/bitrix-model
```

### Объявление модели для инфоблока, пример:

```php
    use Alex19pov31\BitrixModel\BaseIblockModel;

    class News extends BaseIblockModel
    {
        const TTL_MINUTES = 180;

        protected static function getIblockId(): int
        {
            return getIblockId('news', null, static::TTL_MINUTES);
        }

        protected static function getCacheMinutes(): int
        {
            return static::TTL_MINUTES;
        }

    }
```

### Объявление модели для раделов инфоблока, пример:

```php
    use Alex19pov31\BitrixModel\BaseSectionModel;

    class News extends BaseSectionModel
    {
        const TTL_MINUTES = 180;

        protected static function getIblockId(): int
        {
            return getIblockId('news', null, static::TTL_MINUTES);
        }

        protected static function getElementClass()
        {
            return News::class;
        }

        protected static function getCacheMinutes(): int
        {
            return static::TTL_MINUTES;
        }

    }
```

### Объявление модели для hl блока, пример:

```php
    use Alex19pov31\BitrixModel\BaseHlModel;

    class Log extends BaseHlModel
    {
        const TABLE_NAME = 'log';
        const TTL_MINUTES = 180;

        protected static function getTableName(): string
        {
            return static::TABLE_NAME;
        }

        protected static function getCacheMinutes(): int
        {
            return static::TTL_MINUTES;
        }
    }
```

### Объявление модели для произвольной таблицы, пример:

```php
    use Alex19pov31\BitrixModel\TableModel;

    class CustomTable extends TableModel
    {
        const TABLE_NAME = 'custom_table';
        const TTL_MINUTES = 180;

        protected static function getTableName(): string
        {
            return static::TABLE_NAME;
        }

        protected static function getCacheMinutes(): int
        {
            return static::TTL_MINUTES;
        }
    }
```

### Объявление модели для пользователей, пример:

```php
    use Alex19pov31\BitrixModel\UserModel;

    class User extends UserModel
    {
        /**
         * Методы необходимые для конкретного проекта
         ** /
    }
```

## Работа с коллекциями:

```php
    $newsCollection = News::getListCollection([
        'filter' => [
            'ACTIVE' => 'Y',
            'PROPERTY_USER_ID' => 1,
        ],

        $newsCollection->first(); // первый элемент из коллекции 

        $newCollection->last(); // последний элемент из поллекции

        $newCollection->keyBy('ID'); // пересоздает коллекцию указываю в качестве ключей идентификаторы элемента
        
        $newCollection->select(['ID', 'NAME', 'ACTIVE', 'USER_ID']); // выборка полей

        $newCollection->where('NAME', 'test'); // фильтрация коллекции по значению поля

        $newCollection->whereCallback(function(BaseModel $item){
            return $item->getId() % 2 === 0;
        });

        $newCollection->sort('ID', false); // сортировка колелекции относительно идентификаторов элементов по-убыванию

        $newCollection->sort('ID', true); // сортировка колелекции относительно идентификаторов элементов по-возрастанию

        $newCollection->column('ID'); // вернет массив со значениями указанного поля [2,4,6]

        $newCollection->limit(3, 2); // ограничение списка записей возвращает 3 элемента начиная с 3 элемента в списке

        $newCollection->addField('CALC_FILED', function(BaseModel $item) {
            return '['.$item->getId() .'] '. $item['NAME'];
        }); // вычисляемое поле

        $newCollection->sum('ID'); // возвращает сумму всех значений указанного поля

        $newCollection->min('ID'); // вощвращает элемент с минимальным значением указанного поля

        $newCollection->max('ID'); // вощвращает элемент с максимальным значением указанного поля

        $newCollection->groupBy('PROPERTY_USER_ID'); // вернет массив коллекций сгруппированный по указанному полю

        $newCollection->map(function(BaseModel $item){
            $item['TITLE'] = $item->getId(). ': '. $item['NAME'];

            return $item;
        });

        $newCollection->mapToArray(function(BaseModel $item){
            return [
                'id' => $item->getId(),
                'title' => $item->getId(). ': '. $item['NAME'],
            ]
        }, 'ID'); // возвращает массив с указанной структурой данных

        /**
         * Пример работы с коллекцией 
         **/
        $newCollection
            ->where('NAME', 'test')
            ->sort('ID', true)
            ->addField('CALC_FILED', function(BaseModel $item) {
                return '['.$item->getId() .'] '. $item['NAME'];
            });
            ->column('CALC_FILED');
    ]);
```