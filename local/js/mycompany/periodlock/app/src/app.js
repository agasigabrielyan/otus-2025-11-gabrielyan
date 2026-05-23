import { BitrixVue } from "ui.vue3";
import Home from "./components/Home";
import { createPinia } from 'ui.vue3.pinia';
import Modal from './components/Modal';
import { useModalStore } from './store/modal';
import { useAppStore } from "./store/app";

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

		const pinia = createPinia();

		this.#application = BitrixVue.createApp({
			components: {
				Home,
				Modal
			},
			data() {
				return {
					arResult: context.options.arResult || {},
					arParams: context.options.arParams || {},
				}
			},
			created() {
				this.$bitrix.Application.set(context);
				const appStore = useAppStore();
				appStore.setArResult(this.arResult);
				appStore.setArParams(this.arParams);
				appStore.fetchLockStates();
			},
			methods: {
				openTestAlert() {
					alert('hi there');
				}
			},
			template: `
				<div>
					<Home />
					<Modal />
				</div>
			`
		});

		this.#application.use(pinia);
		this.#application.mount(this.rootNode);

	}

}

export { App };