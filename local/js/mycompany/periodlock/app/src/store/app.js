import { defineStore } from 'ui.vue3.pinia';

export const useAppStore = defineStore('app', {
    state: () => ({
        arResult: {},
        arParams: {},

        // ответ контроллера getStates
        isLocked: null,
        entityCode: null,
        lockDate: null,

        loading: false,
        error: null
    }),
    actions: {
        setArResult(data) {
            Object.assign(this.arResult, data || {});
        },
        setArParams(data) {
            Object.assign(this.arParams, data || {});
        },
        fetchLockStates() {
            this.loading = true;
            this.error = null;

            const entityCode = this.arParams.entityCode || 'milestones';

            return BX.ajax.runAction('otus:taskmanager.periodLock.getStatus', {
                data: { entityCode },
            })
                .then((response) => {
                    const data = response.data;
                    this.isLocked = data.isLocked;
                    this.entityCode = data.entityCode;
                    this.lockDate = data.date;
                    this.loading = false;
                })
                .catch((response) => {
                    this.error = response.errors || response;
                    this.loading = false;
                });
        },
    }
});