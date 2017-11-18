(function() {
    var timezone = moment.tz.guess();
    if (timezone) {
        Cookies.set('tz', moment.tz.guess());
    } else {
        Cookies.remove('tz');
    }
})();
