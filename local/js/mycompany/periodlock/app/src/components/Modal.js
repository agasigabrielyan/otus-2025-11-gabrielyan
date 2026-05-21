import { defineComponent } from "ui.vue3";
import { useModalStore } from "../store/modal";

export default defineComponent({
    name: "Modal",
    setup() {
        const modal = useModalStore();
        return { modal };
    },
    template: `
        <transition name="fade">
            <div v-if="modal.isOpen" class="modal-overlay" @click.self="modal.close">
                <div class="modal-box">
                    <button type="button" class="modal-box__close" @click="modal.close">×</button>
                    <component :is="modal.component" v-bind="modal.props" />
                </div>
            </div>
        </transition>
    `
});