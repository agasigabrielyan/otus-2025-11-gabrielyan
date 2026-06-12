/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mycompany = this.BX.Mycompany || {};
(function (exports,ui_vue3_pinia,ui_vue3) {
    'use strict';

    var useModalStore = ui_vue3_pinia.defineStore('modal', {
      state: function state() {
        return {
          isOpen: false,
          component: null,
          props: {}
        };
      },
      actions: {
        open: function open(component) {
          var props = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
          this.component = ui_vue3.markRaw(component);
          this.props = props;
          this.isOpen = true;
        },
        close: function close() {
          this.isOpen = false;
          this.component = null;
          this.props = {};
        }
      }
    });

    var useAppStore = ui_vue3_pinia.defineStore('app', {
      state: function state() {
        return {
          arResult: {},
          arParams: {},
          // ответ контроллера getStates
          isLocked: null,
          entityCode: null,
          lockDate: null,
          loading: false,
          error: null
        };
      },
      actions: {
        setArResult: function setArResult(data) {
          Object.assign(this.arResult, data || {});
        },
        setArParams: function setArParams(data) {
          Object.assign(this.arParams, data || {});
        },
        fetchLockStates: function fetchLockStates() {
          var _this = this;
          this.loading = true;
          this.error = null;
          var entityCode = this.arParams.entityCode || 'milestones';
          return BX.ajax.runAction('otus:taskmanager.periodLock.getStatus', {
            data: {
              entityCode: entityCode
            }
          }).then(function (response) {
            var data = response.data;
            _this.isLocked = data.isLocked;
            _this.entityCode = data.entityCode;
            _this.lockDate = data.date;
            _this.loading = false;
          })["catch"](function (response) {
            _this.error = response.errors || response;
            _this.loading = false;
          });
        }
      }
    });

    var useWizardStore = ui_vue3_pinia.defineStore("wizard", {
      state: function state() {
        return {
          year: new Date().getFullYear(),
          periods: [{
            dateFrom: "",
            dateTo: ""
          }],
          roleIds: []
        };
      },
      actions: {
        reset: function reset() {
          this.year = new Date().getFullYear();
          this.periods = [{
            dateFrom: "",
            dateTo: ""
          }];
          this.roleIds = [];
        },
        addPeriod: function addPeriod() {
          this.periods.push({
            dateFrom: "",
            dateTo: ""
          });
        },
        removePeriod: function removePeriod(index) {
          if (this.periods.length > 1) {
            this.periods.splice(index, 1);
          }
        }
      }
    });

    function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
    function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
    var PeriodLockWizard = ui_vue3.defineComponent({
      name: "PeriodLockWizard",
      setup: function setup() {
        var modal = useModalStore();
        var app = useAppStore();
        var wizard = useWizardStore();
        var currentStep = ui_vue3.ref(0);
        var direction = ui_vue3.ref("forward");
        var validationError = ui_vue3.ref("");
        var yearOptions = ui_vue3.computed(function () {
          var current = new Date().getFullYear();
          var years = [];
          for (var y = current - 2; y <= current + 5; y++) {
            years.push(y);
          }
          return years;
        });
        var transitionName = ui_vue3.computed(function () {
          return direction.value === "forward" ? "wizard-slide-forward" : "wizard-slide-back";
        });
        var dateMin = ui_vue3.computed(function () {
          return "".concat(wizard.year, "-01-01");
        });
        var dateMax = ui_vue3.computed(function () {
          return "".concat(wizard.year, "-12-31");
        });
        var availableRoles = [{
          id: 1,
          name: "Менеджер"
        }, {
          id: 2,
          name: "Аналитик"
        }, {
          id: 3,
          name: "Администратор"
        }];
        ui_vue3.onMounted(function () {
          wizard.reset();
        });
        function validateStep(step) {
          validationError.value = "";
          if (step === 1) {
            for (var i = 0; i < wizard.periods.length; i++) {
              var period = wizard.periods[i];
              if (!period.dateFrom || !period.dateTo) {
                validationError.value = "\u0417\u0430\u043F\u043E\u043B\u043D\u0438\u0442\u0435 \u0434\u0430\u0442\u044B \u0432 \u043F\u0435\u0440\u0438\u043E\u0434\u0435 ".concat(i + 1);
                return false;
              }
              if (period.dateFrom > period.dateTo) {
                validationError.value = "\u0412 \u043F\u0435\u0440\u0438\u043E\u0434\u0435 ".concat(i + 1, ": \u0434\u0430\u0442\u0430 \u043D\u0430\u0447\u0430\u043B\u0430 \u043F\u043E\u0437\u0436\u0435 \u0434\u0430\u0442\u044B \u043E\u043A\u043E\u043D\u0447\u0430\u043D\u0438\u044F");
                return false;
              }
              if (period.dateFrom < dateMin.value || period.dateTo > dateMax.value) {
                validationError.value = "\u0412 \u043F\u0435\u0440\u0438\u043E\u0434\u0435 ".concat(i + 1, ": \u0434\u0430\u0442\u044B \u0434\u043E\u043B\u0436\u043D\u044B \u0431\u044B\u0442\u044C \u0432 ").concat(wizard.year, " \u0433\u043E\u0434\u0443");
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
          var index = wizard.roleIds.indexOf(roleId);
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
          var _app$arParams;
          if (!validateStep(2)) {
            return;
          }
          var payload = {
            entityCode: app.entityCode || ((_app$arParams = app.arParams) === null || _app$arParams === void 0 ? void 0 : _app$arParams.entityCode) || "milestones",
            year: wizard.year,
            periods: wizard.periods.map(function (p) {
              return _objectSpread({}, p);
            }),
            roleIds: babelHelpers.toConsumableArray(wizard.roleIds)
          };
          console.log("Period lock save:", payload);
          modal.close();
        }
        return {
          wizard: wizard,
          currentStep: currentStep,
          validationError: validationError,
          yearOptions: yearOptions,
          transitionName: transitionName,
          dateMin: dateMin,
          dateMax: dateMax,
          availableRoles: availableRoles,
          next: next,
          prev: prev,
          toggleRole: toggleRole,
          isRoleSelected: isRoleSelected,
          save: save
        };
      },
      template: "\n        <div class=\"popup-lockform period-lock-wizard\">\n            <h3 class=\"period-lock-wizard__title\">\u0417\u0430\u043A\u0440\u044B\u0442\u0438\u0435 \u043F\u0435\u0440\u0438\u043E\u0434\u0430</h3>\n\n            <div class=\"period-lock-wizard__viewport\">\n                <transition :name=\"transitionName\" mode=\"out-in\">\n                    <div :key=\"currentStep\" class=\"period-lock-wizard__step\">\n                        <div v-if=\"currentStep === 0\">\n                            <p class=\"period-lock-wizard__label\">\u0412\u044B\u0431\u0435\u0440\u0438\u0442\u0435 \u0433\u043E\u0434</p>\n                            <select v-model.number=\"wizard.year\" class=\"period-lock-wizard__select\">\n                                <option v-for=\"y in yearOptions\" :key=\"y\" :value=\"y\">{{ y }}</option>\n                            </select>\n                        </div>\n\n                        <div v-else-if=\"currentStep === 1\">\n                            <p class=\"period-lock-wizard__label\">\u041F\u0435\u0440\u0438\u043E\u0434\u044B \u0431\u043B\u043E\u043A\u0438\u0440\u043E\u0432\u043A\u0438 \u0432 {{ wizard.year }} \u0433\u043E\u0434\u0443</p>\n                            <div\n                                v-for=\"(period, index) in wizard.periods\"\n                                :key=\"index\"\n                                class=\"period-lock-wizard__period-row\"\n                            >\n                                <div class=\"period-lock-wizard__period-fields\">\n                                    <label>\n                                        <span>\u041D\u0430\u0447\u0430\u043B\u043E</span>\n                                        <input\n                                            v-model=\"period.dateFrom\"\n                                            type=\"date\"\n                                            :min=\"dateMin\"\n                                            :max=\"dateMax\"\n                                        />\n                                    </label>\n                                    <label>\n                                        <span>\u041E\u043A\u043E\u043D\u0447\u0430\u043D\u0438\u0435</span>\n                                        <input\n                                            v-model=\"period.dateTo\"\n                                            type=\"date\"\n                                            :min=\"dateMin\"\n                                            :max=\"dateMax\"\n                                        />\n                                    </label>\n                                </div>\n                                <button\n                                    v-if=\"wizard.periods.length > 1\"\n                                    type=\"button\"\n                                    class=\"period-lock-wizard__btn period-lock-wizard__btn--text\"\n                                    @click=\"wizard.removePeriod(index)\"\n                                >\n                                    \u0423\u0434\u0430\u043B\u0438\u0442\u044C\n                                </button>\n                            </div>\n                            <button\n                                type=\"button\"\n                                class=\"period-lock-wizard__btn period-lock-wizard__btn--text\"\n                                @click=\"wizard.addPeriod()\"\n                            >\n                                + \u0414\u043E\u0431\u0430\u0432\u0438\u0442\u044C \u043F\u0435\u0440\u0438\u043E\u0434\n                            </button>\n                        </div>\n\n                        <div v-else>\n                            <p class=\"period-lock-wizard__label\">\u0420\u043E\u043B\u0438 \u0431\u0435\u0437 \u043F\u0440\u0430\u0432\u0430 \u0440\u0435\u0434\u0430\u043A\u0442\u0438\u0440\u043E\u0432\u0430\u043D\u0438\u044F</p>\n                            <label\n                                v-for=\"role in availableRoles\"\n                                :key=\"role.id\"\n                                class=\"period-lock-wizard__role\"\n                            >\n                                <input\n                                    type=\"checkbox\"\n                                    :checked=\"isRoleSelected(role.id)\"\n                                    @change=\"toggleRole(role.id)\"\n                                />\n                                {{ role.name }}\n                            </label>\n                        </div>\n                    </div>\n                </transition>\n            </div>\n\n            <p v-if=\"validationError\" class=\"period-lock-wizard__error\">{{ validationError }}</p>\n\n            <div class=\"period-lock-wizard__actions\">\n                <button\n                    v-if=\"currentStep > 0\"\n                    type=\"button\"\n                    class=\"period-lock-wizard__btn\"\n                    @click=\"prev\"\n                >\n                    \u041D\u0430\u0437\u0430\u0434\n                </button>\n                <div class=\"period-lock-wizard__actions-spacer\"></div>\n                <button\n                    v-if=\"currentStep < 2\"\n                    type=\"button\"\n                    class=\"period-lock-wizard__btn period-lock-wizard__btn--primary\"\n                    @click=\"next\"\n                >\n                    \u0414\u0430\u043B\u0435\u0435\n                </button>\n                <button\n                    v-else\n                    type=\"button\"\n                    class=\"period-lock-wizard__btn period-lock-wizard__btn--primary\"\n                    @click=\"save\"\n                >\n                    \u0421\u043E\u0445\u0440\u0430\u043D\u0438\u0442\u044C\n                </button>\n            </div>\n        </div>\n    "
    });

    var Home = ui_vue3.defineComponent({
      name: "Home",
      setup: function setup() {
        var modal = useModalStore();
        var app = useAppStore();
        var openChangePeriodLockForm = function openChangePeriodLockForm() {
          modal.open(PeriodLockWizard, {});
        };
        return {
          openChangePeriodLockForm: openChangePeriodLockForm,
          modal: modal,
          app: app
        };
      },
      template: "\n        <div>\n            <div class=\"period-lock__wrapper\">\n                <span>\u0422\u0443\u0442 \u0437\u0430\u043C\u043E\u0447\u0435\u043A \u0431\u0443\u0434\u0435\u0442 \u043E\u0442\u043A\u0440\u044B\u0432\u0430\u0442\u044C\u0441\u044F \u0438\u043B\u0438 \u0437\u0430\u043A\u0440\u044B\u0432\u0430\u0442\u044C\u0441\u044F</span>\n                <button @click=\"openChangePeriodLockForm\">\n                    \u0417\u0430\u043A\u0440\u044B\u0442\u044C \u043F\u0435\u0440\u0438\u043E\u0434\n                </button>                     \n            </div>\n        </div>\n    "
    });

    var Modal = ui_vue3.defineComponent({
      name: "Modal",
      setup: function setup() {
        var modal = useModalStore();
        return {
          modal: modal
        };
      },
      template: "\n        <transition name=\"fade\">\n            <div v-if=\"modal.isOpen\" class=\"modal-overlay\" @click.self=\"modal.close\">\n                <div class=\"modal-box\">\n                    <button type=\"button\" class=\"modal-box__close\" @click=\"modal.close\">\xD7</button>\n                    <component :is=\"modal.component\" v-bind=\"modal.props\" />\n                </div>\n            </div>\n        </transition>\n    "
    });

    function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
    function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
    var _application = /*#__PURE__*/new WeakMap();
    var App = /*#__PURE__*/function () {
      function App(rootNode) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        babelHelpers.classCallCheck(this, App);
        _classPrivateFieldInitSpec(this, _application, {
          writable: true,
          value: void 0
        });
        this.rootNode = document.querySelector(rootNode);
        this.options = options;
      }
      babelHelpers.createClass(App, [{
        key: "start",
        value: function start() {
          this.initVue();
        }
      }, {
        key: "initVue",
        value: function initVue() {
          var context = this;
          var pinia = ui_vue3_pinia.createPinia();
          babelHelpers.classPrivateFieldSet(this, _application, ui_vue3.BitrixVue.createApp({
            components: {
              Home: Home,
              Modal: Modal
            },
            data: function data() {
              return {
                arResult: context.options.arResult || {},
                arParams: context.options.arParams || {}
              };
            },
            created: function created() {
              this.$bitrix.Application.set(context);
              var appStore = useAppStore();
              appStore.setArResult(this.arResult);
              appStore.setArParams(this.arParams);
              appStore.fetchLockStates();
            },
            methods: {
              openTestAlert: function openTestAlert() {
                //alert('hi there');
              }
            },
            template: "\n\t\t\t\t<div>\n\t\t\t\t\t<Home />\n\t\t\t\t\t<Modal />\n\t\t\t\t</div>\n\t\t\t"
          }));
          babelHelpers.classPrivateFieldGet(this, _application).use(pinia);
          babelHelpers.classPrivateFieldGet(this, _application).mount(this.rootNode);
        }
      }]);
      return App;
    }();

    exports.App = App;

}((this.BX.Mycompany.Periodlock = this.BX.Mycompany.Periodlock || {}),BX.Vue3.Pinia,BX.Vue3));
//# sourceMappingURL=app.bundle.js.map
