import { defineComponent, ref, computed, onMounted } from "ui.vue3";
import { useModalStore } from "../store/modal";
import { useAppStore } from "../store/app";
import { useWizardStore } from "../store/wizard";

export default defineComponent({
    name: "PeriodLockWizard",

    setup() {
        const modal = useModalStore();
        const app = useAppStore();
        const wizard = useWizardStore();

        const currentStep = ref(0);
        const direction = ref("forward");
        const validationError = ref("");

        const yearOptions = computed(() => {
            const current = new Date().getFullYear();
            const years = [];
            for (let y = current - 2; y <= current + 5; y++) {
                years.push(y);
            }
            return years;
        });

        const transitionName = computed(() =>
            direction.value === "forward"
                ? "wizard-slide-forward"
                : "wizard-slide-back"
        );

        const dateMin = computed(() => `${wizard.year}-01-01`);
        const dateMax = computed(() => `${wizard.year}-12-31`);

        const availableRoles = [
            { id: 1, name: "Менеджер" },
            { id: 2, name: "Аналитик" },
            { id: 3, name: "Администратор" },
        ];

        onMounted(() => {
            wizard.reset();
        });

        function validateStep(step) {
            validationError.value = "";

            if (step === 1) {
                for (let i = 0; i < wizard.periods.length; i++) {
                    const period = wizard.periods[i];
                    if (!period.dateFrom || !period.dateTo) {
                        validationError.value = `Заполните даты в периоде ${i + 1}`;
                        return false;
                    }
                    if (period.dateFrom > period.dateTo) {
                        validationError.value = `В периоде ${i + 1}: дата начала позже даты окончания`;
                        return false;
                    }
                    if (period.dateFrom < dateMin.value || period.dateTo > dateMax.value) {
                        validationError.value = `В периоде ${i + 1}: даты должны быть в ${wizard.year} году`;
                        return false;
                    }
                }
            }

            if (step === 2) {
                if (wizard.roleIds.length === 0) {
                    validationError.value = "Выберите хотя бы одну роль";
                    return false;
                }
            }

            return true;
        }

        function next() {
            if (!validateStep(currentStep.value)) {
                return;
            }
            if (currentStep.value < 2) {
                direction.value = "forward";
                currentStep.value++;
            }
        }

        function prev() {
            validationError.value = "";
            if (currentStep.value > 0) {
                direction.value = "back";
                currentStep.value--;
            }
        }

        function toggleRole(roleId) {
            const index = wizard.roleIds.indexOf(roleId);
            if (index === -1) {
                wizard.roleIds.push(roleId);
            } else {
                wizard.roleIds.splice(index, 1);
            }
        }

        function isRoleSelected(roleId) {
            return wizard.roleIds.includes(roleId);
        }

        function save() {
            if (!validateStep(2)) {
                return;
            }

            const payload = {
                entityCode: app.entityCode || app.arParams?.entityCode || "milestones",
                year: wizard.year,
                periods: wizard.periods.map((p) => ({ ...p })),
                roleIds: [...wizard.roleIds],
            };

            console.log("Period lock save:", payload);
            modal.close();
        }

        return {
            wizard,
            currentStep,
            validationError,
            yearOptions,
            transitionName,
            dateMin,
            dateMax,
            availableRoles,
            next,
            prev,
            toggleRole,
            isRoleSelected,
            save,
        };
    },

    template: `
        <div class="popup-lockform period-lock-wizard">
            <h3 class="period-lock-wizard__title">Закрытие периода</h3>

            <div class="period-lock-wizard__viewport">
                <transition :name="transitionName" mode="out-in">
                    <div :key="currentStep" class="period-lock-wizard__step">
                        <div v-if="currentStep === 0">
                            <p class="period-lock-wizard__label">Выберите год</p>
                            <select v-model.number="wizard.year" class="period-lock-wizard__select">
                                <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>

                        <div v-else-if="currentStep === 1">
                            <p class="period-lock-wizard__label">Периоды блокировки в {{ wizard.year }} году</p>
                            <div
                                v-for="(period, index) in wizard.periods"
                                :key="index"
                                class="period-lock-wizard__period-row"
                            >
                                <div class="period-lock-wizard__period-fields">
                                    <label>
                                        <span>Начало</span>
                                        <input
                                            v-model="period.dateFrom"
                                            type="date"
                                            :min="dateMin"
                                            :max="dateMax"
                                        />
                                    </label>
                                    <label>
                                        <span>Окончание</span>
                                        <input
                                            v-model="period.dateTo"
                                            type="date"
                                            :min="dateMin"
                                            :max="dateMax"
                                        />
                                    </label>
                                </div>
                                <button
                                    v-if="wizard.periods.length > 1"
                                    type="button"
                                    class="period-lock-wizard__btn period-lock-wizard__btn--text"
                                    @click="wizard.removePeriod(index)"
                                >
                                    Удалить
                                </button>
                            </div>
                            <button
                                type="button"
                                class="period-lock-wizard__btn period-lock-wizard__btn--text"
                                @click="wizard.addPeriod()"
                            >
                                + Добавить период
                            </button>
                        </div>

                        <div v-else>
                            <p class="period-lock-wizard__label">Роли без права редактирования</p>
                            <label
                                v-for="role in availableRoles"
                                :key="role.id"
                                class="period-lock-wizard__role"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isRoleSelected(role.id)"
                                    @change="toggleRole(role.id)"
                                />
                                {{ role.name }}
                            </label>
                        </div>
                    </div>
                </transition>
            </div>

            <p v-if="validationError" class="period-lock-wizard__error">{{ validationError }}</p>

            <div class="period-lock-wizard__actions">
                <button
                    v-if="currentStep > 0"
                    type="button"
                    class="period-lock-wizard__btn"
                    @click="prev"
                >
                    Назад
                </button>
                <div class="period-lock-wizard__actions-spacer"></div>
                <button
                    v-if="currentStep < 2"
                    type="button"
                    class="period-lock-wizard__btn period-lock-wizard__btn--primary"
                    @click="next"
                >
                    Далее
                </button>
                <button
                    v-else
                    type="button"
                    class="period-lock-wizard__btn period-lock-wizard__btn--primary"
                    @click="save"
                >
                    Сохранить
                </button>
            </div>
        </div>
    `,
});
