jQuery(function ($) {
    var w = window;

    var start = function () {
        if ($('#disqus_thread').length > 0) {
            w.disqus_shortname = 'umonkey-land';

            var did = w.jsdata.disqus_id || null;
            var durl = w.jsdata.disqus_url || null;

            if (did) {
                w.disqus_identifier = did;
            } else if (durl) {
                w.disqus_url = durl;
            }

            $.getScript('https://umonkey-land.disqus.com/embed.js', function () {
                console && console.log('disqus comments loaded');
            });
        } else {
            console && console.log('disqus comments disabled ');
        }
    };

    start();
    $(document).on('ufw:reload', start);
});
