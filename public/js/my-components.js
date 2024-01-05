var myComponents = {
    maintainTabs: function(tabName) {
        $('a[data-toggle=tab]').on('shown.bs.tab', function(e) {
            var $href = $(e.target).attr('href')
            //save the latest tab; use cookies if you like 'em better:
            localStorage.setItem(tabName, $href);
        });

        //go to the latest tab, if it exists:
        var selectedTab = localStorage.getItem(tabName);
        if (selectedTab) {
            console.log(selectedTab);
            $('a[href="' + selectedTab + '"]').tab('show');
        }
    }
}