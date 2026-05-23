import { defineComponent } from "ui.vue3";
import { useModalStore } from "../store/modal";
import { useAppStore } from "../store/app";
import PeriodChooseForm from "./PeriodChooseForm";

export default defineComponent({
    name: "Home",

    setup() {
        const modal = useModalStore();
        const app = useAppStore();

        const openChangePeriodLockForm = () => {
            modal.open(PeriodChooseForm,{});
        }

        return {
            openChangePeriodLockForm,
            modal,
            app
        }
    },

    template: `
        <div>
            <div class="period-lock__wrapper">
                <span>Тут замочек будет открываться или закрываться</span>
                <button @click="openChangePeriodLockForm">
                    Закрыть период
                </button>                     
            </div>
        </div>
    `
});