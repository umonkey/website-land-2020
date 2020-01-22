# Блог о жизни в деревне

Исходный код [нашего семейного блога][1].  Сейчас это простая вики, с ограниченным доступом к редактированию.  Собрана на основе [Slim Framework][2] и [ufw1][3] (набор собственных заготовок для Slim).  Всё максимально простое и быстое.

База данных -- SQLite, работает в целом в режиме read-only, отлично себя показывает в этой роли.  Комментарии внешние, через Disqus.

Файлы хранятся в [Яндекс.Облаке][4], выгрузка по [протоколу S3][5]; это дёшево и существенно снижает требования к хостингу (фотографий загружено примерно на 600 МБ).  Выгрузка отложенная, фоновая, через примитивную самодельную очередь задач.

Скриптов минимум.  Есть [небольшой скрипт][7] для ускорения локальной навигации: вместо перезагрузки страницы со всеми связями подтягивает только содержимое страницы, через XHR.

Когда-то это был статический сайт на [Poole][6].  Он был классный, невероятно быстрый, но редактировать его было очень неудобно.  Однажды хочу научить эту вики выгружать страницы в статику, чтобы получить прежнюю скорость и отказоустойчивость.  Впрочем, скорость и сейчас отличная.

[1]: https://land.umonkey.net/
[2]: https://www.slimframework.com/
[3]: https://github.com/umonkey/ufw1
[4]: https://cloud.yandex.ru/services/storage "Yandex Object Storage"
[5]: https://cloud.yandex.ru/docs/storage/s3/
[6]: https://hg.sr.ht/~obensonne/poole
[7]: https://github.com/umonkey/ufw1/blob/master/assets/spa.js
