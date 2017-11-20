(function() {
    var timezone = false;
    try {
        timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (e) {
        //
    }

    if (timezone) {
        Cookies.set('tz', timezone, { expires: 365 });
    } else {
        Cookies.remove('tz');
    }
})();
