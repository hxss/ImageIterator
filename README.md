# ImageIterator
Модуль для обхода изображений моделей для [phact-cmf](https://github.com/phact-cmf)

## Использование

На данный момент модуль умеет только ре-генерировать изображения под заданные в модели размеры:

`php www/index.php ImageIterator regen`

Чтобы сэкономить время можно указать фильтры по модулю/модели/ключу/полю, для которых необходимо выполнить ре-генерацию:

`php www/index.php ImageIterator regen run 'Main\Car[5]::image'`

В данном синтексисе обязательным является только значение модели(`Car` в примере выше). Любое другое значение(модуль, ключ, поле) могут быть опущены:

`php www/index.php ImageIterator regen run 'Car'`

`php www/index.php ImageIterator regen run 'Car[5]::image'`

`php www/index.php ImageIterator regen run 'Car::image'`

`php www/index.php ImageIterator regen run 'Car[5]'`
