import { defineStore } from "ui.vue3.pinia";
import { markRaw } from "ui.vue3";

export const useModalStore = defineStore('modal', {
    state: () => ({
        isOpen: false,
        component: null,
        props: {},
    }),

    actions: {
        open(component, props={}) {
            this.component = markRaw(component);
            this.props = props;
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
            this.component = null;
            this.props = {};
        }
    }
});