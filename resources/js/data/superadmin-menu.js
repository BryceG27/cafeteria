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
      group : 'dashboard',
      icon: "si si-speedometer",
    },
    {
      name : "Clienti",
      to : route('customers.index'),
      group : 'customers',
      icon : "fa fa-hand-holding-dollar"
    },
    {
      name: "Ordini Clienti",
      to: route('orders.index'),
      group: 'orders',
      icon: "fa fa-shopping-cart"
    },
    {
      name: "Configurazione",
      heading: true,
    },
    {
      name : "Utenti",
      to : route('users.index'),
      group : 'users',
      icon : "fa fa-users"
    },
    /* {
      name : "Categorie Prodotti",
      to : route('categories.index'),
      group : 'categories',
      icon : "fa fa-tags"
    }, */
    {
      name : "Prodotti",
      to : route('products.index'),
      group : 'products',
      icon : "fa fa-box-open"
    },
    {
      name : "Menu Settimanale",
      to : route('menus.index'),
      group : 'menus',
      icon : "fa fa-calendar-alt"
    },
    {
      name : "Menu Extra",
      to : route('special-menus.index'),
      group : 'special-menus',
      icon : "fa fa-table-list"
    },
    {
      name : "Pagamenti da sistemare",
      to : route('payments.to-check'),
      group : '',
      icon : "fa fa-list",
      user_id : 1
    }
    /* {
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
    }, */
  ],
};
