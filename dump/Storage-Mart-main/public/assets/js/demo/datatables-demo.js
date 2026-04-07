// Call the datatables jQuery plugin
new DataTable("#asset", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [1, 2],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#assetUser", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [0, 2],
      columnControl: [
        "order",
        ["search", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [1],
      columnControl: [
        "order",
        ["searchList","spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#account", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [1, 2],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
//ColumnControl for Aseset Inventory
new DataTable("#asset_inventory", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [1, 2, 3, 4, 5],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#asst-history", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [2, 3,4],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0,1],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#tickets", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [3, 4, 6],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [1,2,7],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#asset-ticket", {
  fixedHeader: { header: true },
  columnDefs: [
    {
      targets: [2, 3],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0,1],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#employee-table", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [ 5, 6, 7, 10],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0,1,2,3,4,8,9],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#pendings", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [ 2,3,5, 6,9],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0,1,7,8],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#ticketTables", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [2, 3, 5, 6],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0, 1, 7],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#IT-TicketDatables", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [2, 3, 5, 6],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});
new DataTable("#resolvedTable", {
  fixedHeader: { header: true },
  order: [],
  columnDefs: [
    {
      targets: [2, 3, 5, 6],
      columnControl: [
        "order",
        ["searchList", "spacer", "orderAsc", "orderDesc", "orderClear"],
      ],
    },
    {
      targets: [0, 1, 7],
      columnControl: [
        "order",
        ["search"],
      ],
    },
  ],
  ordering: {
    indicators: false,
    handler: false,
  },
});