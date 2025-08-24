/*
 * Main and demo navigation arrays
 *
 * 'to' attribute points to the route name, not the path url
 */

export default {
  main: [
    {
      name: "Ordini",
      to: route("orders.index"),
      icon: "si si-speedometer",
    },
  ]
};
