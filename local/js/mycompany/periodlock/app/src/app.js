import { BitrixVue } from "ui.vue3";
import Home from "./components/Home";

import "./assets/global.css";

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
			components: {
				Home
			},
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
				<div>
					<Home />
				</div>
			`
		});

		this.#application.mount(this.rootNode);

	}

}

export { App };