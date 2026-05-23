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

    var PeriodChooseForm = ui_vue3.defineComponent({
      name: "PeriodChooseForm",
      setup: function setup() {
        var modal = useModalStore();
        var app = useAppStore();
        return {
          modal: modal,
          app: app
        };
      },
      template: "\n        <div class=\"popup-lockform\">\n            \u042D\u0442\u0430 \u0444\u043E\u0440\u043C\u0430 \u0431\u0443\u0434\u0435\u0442 \u043E\u0442\u043A\u0440\u044B\u0432\u0430\u0442\u044C\u0441\u044F \u0432 \u043C\u043E\u0434\u0430\u043B\u044C\u043D\u043E\u043C \u043E\u043A\u043D\u0435\n        </div>\n    "
    });

    var Home = ui_vue3.defineComponent({
      name: "Home",
      setup: function setup() {
        var modal = useModalStore();
        var app = useAppStore();
        var openChangePeriodLockForm = function openChangePeriodLockForm() {
          modal.open(PeriodChooseForm, {});
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
                alert('hi there');
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
