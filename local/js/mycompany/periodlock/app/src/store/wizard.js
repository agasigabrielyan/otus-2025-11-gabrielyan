import { defineStore } from "ui.vue3.pinia";

export const useWizardStore = defineStore("wizard", {
    state: () => ({
        year: new Date().getFullYear(),
        periods: [{ dateFrom: "", dateTo: "" }],
        roleIds: [],
    }),
    actions: {
        reset() {
            this.year = new Date().getFullYear();
            this.periods = [{ dateFrom: "", dateTo: "" }];
            this.roleIds = [];
        },
        addPeriod() {
            this.periods.push({ dateFrom: "", dateTo: "" });
        },
        removePeriod(index) {
            if (this.periods.length > 1) {
                this.periods.splice(index, 1);
            }
        },
    },
});
