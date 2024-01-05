var DtConfig = {
    processing: true, // loading icon
    serverSide: true, // this means the datatable is no longer client side
    "pagingType": "full_numbers",
    "lengthMenu": [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
    ],
    responsive: true,
    language: {
        search: "_INPUT_",
        searchPlaceholder: "Search records",
    },
    "order": [[0, "desc"]]
}