/**
 * Принудительное обновление счётчика при внутренней навигации.
 *
 * Инструкция здесь: https://yandex.ru/support/metrica/code/ajax-flash.html
 **/
jQuery(function ($) {
    $(document).on('ufw:reload', function () {
        ym(14608519, 'hit', window.location.href);
    });
});
