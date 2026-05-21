import { BitrixVue } from "ui.vue3";

class App {
	#application;

	constructor(rootNode, options={}) {
		this.rootNode = document.querySelector(rootNode);
		this.options = options;
	}

	start() {
		this.initVue();
	}

	initVue() {
		const context = this;

		this.#application = BitrixVue.createApp({
			data() {
				return {
					arResult: context.options.arResult || {},
					arParams: context.options.arParams || {},
				}
			},
			created() {
				this.$bitrix.Application.set(context)
			},
			template: `
				<div class="periodlock-app">Мое приложение для этого</div>
			`
		});

		this.#application.mount(this.rootNode);

	}

}

export { App };