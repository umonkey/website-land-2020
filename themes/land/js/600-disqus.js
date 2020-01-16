jQuery(function ($) {
    var w = window;

    if ('jsdata' in w && 'disqus_url' in w.jsdata) {
        w.disqus_shortname = 'umonkey-land';
        w.disqus_url = w.jsdata.disqus_url;
        w.disqus_identifier = w.jsdata.disqus_id;

        if (w.disqus_identifier.substr(-1) == '') {
            w.disqus_identifier += 'index.html';
        }

        $.getScript('https://umonkey-land.disqus.com/embed.js', function () {
            console && console.log('disqus comments loaded');
        });
    }
});
