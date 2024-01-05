function showNotification(from, align, type, message) {
    $.notify({
        icon: "add_alert",
        message: message

    }, {
        type: type,
        timer: 1000,
        placement: {
            from: from,
            align: align
        }
    });
}