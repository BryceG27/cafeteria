/*
 * Main and demo navigation arrays
 *
 * 'to' attribute points to the route name, not the path url
 */

export default {
  main: [
    {
      name: "Dashboard",
      to: route('dashboard'),
      icon: "si si-speedometer",
    },
    {
      name: "Configurazione",
      heading: true,
    },
    {
      name : "Clienti",
      to : route('customers.index'),
      icon : "fa fa-hand-holding-dollar"
    },
    {
      name : "Prodotti",
      to : route('products.index'),
      icon : "fa fa-box-open"
    },
    {
      name: "Blocks",
      icon: "si si-energy",
      subActivePaths: "/backend/blocks",
      sub: [
        {
          name: "Styles",
          to: "backend-blocks-styles",
        },
        {
          name: "Options",
          to: "backend-blocks-options",
        },
        {
          name: "Forms",
          to: "backend-blocks-forms",
        },
        {
          name: "Themed",
          to: "backend-blocks-themed",
        },
        {
          name: "API",
          to: "backend-blocks-api",
        },
      ],
    },
  ],
};
