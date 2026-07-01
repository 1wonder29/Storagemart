// Call the datatables jQuery plugin
// #asset table is initialized by admin-assets-directory.js on the modern assets page.
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
// #account table is initialized by admin-accounts.js on the modern accounts page.
//ColumnControl for Aseset Inventory
// #asset_inventory table is initialized by admin-asset-inventory.js on the modern inventory page.
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
// #employee-table is initialized by admin-employees.js on the modern employees page.
// Legacy demo tables — admin ticket lists use page-specific scripts (e.g. admin-tickets.js).
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