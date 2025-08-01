import { defineStore } from "pinia";

// Main Pinia Store
export const useAuthStore = defineStore({
  id: "auth",
  state: () => ({
    // App vital details
   user : null
  }),
  actions: {
    // Sets the layout, useful for setting different layouts (under layouts/variations/)
    setUser(payload) {
      this.user = payload;
    },
  },
});
