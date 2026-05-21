/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mycompany = this.BX.Mycompany || {};
(function (exports,ui_vue3) {
	'use strict';

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
	        data: function data() {
	          return {
	            arResult: context.options.arResult || {},
	            arParams: context.options.arParams || {}
	          };
	        },
	        created: function created() {
	          this.$bitrix.Application.set(context);
	        },
	        template: "\n\t\t\t\t<div class=\"periodlock-app\">\u041C\u043E\u0435 \u043F\u0440\u0438\u043B\u043E\u0436\u0435\u043D\u0438\u0435 \u0434\u043B\u044F \u044D\u0442\u043E\u0433\u043E</div>\n\t\t\t"
	      }));
	      babelHelpers.classPrivateFieldGet(this, _application).mount(this.rootNode);
	    }
	  }]);
	  return App;
	}();

	exports.App = App;

}((this.BX.Mycompany.Periodlock = this.BX.Mycompany.Periodlock || {}),BX.Vue3));
//# sourceMappingURL=app.bundle.js.map
