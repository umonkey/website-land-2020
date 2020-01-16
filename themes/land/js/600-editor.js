jQuery(function ($) {
    $(document).on('click', '.btn.fullscreen', function (e) {
        e.preventDefault();
        var f = $(this).closest('.form-group');
        if (document.fullscreen) {
            document.exitFullscreen();
        } else {
            f[0].requestFullscreen();
        }
    });
});
