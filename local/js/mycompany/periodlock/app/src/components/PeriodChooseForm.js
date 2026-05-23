import { defineComponent } from "ui.vue3";
import { useModalStore } from "../store/modal";
import { useAppStore } from "../store/app";

export default defineComponent({
    name: "PeriodChooseForm",

    setup() {
        const modal = useModalStore();
        const app = useAppStore();

        return {
            modal,
            app
        }
    },

    template: `
        <div class="popup-lockform">
            Эта форма будет открываться в модальном окне
        </div>
    `
});