/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mycompany = this.BX.Mycompany || {};
(function (exports,ui_vue3) {
    'use strict';

    var Home = ui_vue3.defineComponent({
      name: "Home",
      setup: function setup() {},
      template: "\n        <div>\n            \u042D\u0442\u043E HOME\n        </div>\n    "
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
          babelHelpers.classPrivateFieldSet(this, _application, ui_vue3.BitrixVue.createApp({
            components: {
              Home: Home
            },
            data: function data() {
              return {
                arResult: context.options.arResult || {},
                arParams: context.options.arParams || {}
              };
            },
            created: function created() {
              this.$bitrix.Application.set(context);
            },
            template: "\n\t\t\t\t<div>\n\t\t\t\t\t<Home />\n\t\t\t\t</div>\n\t\t\t"
          }));
          babelHelpers.classPrivateFieldGet(this, _application).mount(this.rootNode);
        }
      }]);
      return App;
    }();

    exports.App = App;

}((this.BX.Mycompany.Periodlock = this.BX.Mycompany.Periodlock || {}),BX.Vue3));
//# sourceMappingURL=app.bundle.js.map
