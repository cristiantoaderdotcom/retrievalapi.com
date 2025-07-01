import "./bootstrap";

import intersect from '@alpinejs/intersect'
import sort from '@alpinejs/sort';
import '@wotz/livewire-sortablejs';

import { livewire_hot_reload } from "virtual:livewire-hot-reload";
livewire_hot_reload();

import 'highlight.js/styles/github.css';
import hljs from 'highlight.js/lib/core';
import javascript from 'highlight.js/lib/languages/javascript';
hljs.registerLanguage('javascript', javascript);

document.addEventListener("alpine:init", () => {
	Alpine.plugin(intersect);
	Alpine.plugin(sort);

	Alpine.magic('addHttps', () => (input) => {
		if (!input || typeof input !== 'string')
			return '';

		if (!input.startsWith('https://'))
			return 'https://' + input;

		return input;
	});

	Alpine.store('screen', {
		md: window.matchMedia('(min-width: 768px)').matches,
		lg: window.matchMedia('(min-width: 1024px)').matches,

		init() {
			const updateMediaQuery = () => {
				this.md = window.matchMedia('(min-width: 768px)').matches;
				this.lg = window.matchMedia('(min-width: 1024px)').matches;
			};
			window.addEventListener('resize', updateMediaQuery);

			return () => window.removeEventListener('resize', updateMediaQuery);
		},
	});


	Alpine.data('consent', () => ({
		open: false,
		init() {
			if (localStorage.getItem('cookies_consent') === null)
				this.open = true;
		},
		agree() {
			this.grant();
			localStorage.setItem('cookies_consent', true);
			this.open = false;
		},
		disagree() {
			localStorage.setItem('cookies_consent', false);
			this.open = false;
		},
		grant() {
			if (typeof gtag === 'function') {
				gtag('consent', 'update', {
					ad_user_data: 'granted',
					ad_personalization: 'granted',
					ad_storage: 'granted',
					analytics_storage: 'granted'
				});
			}
		}
	}));
});

document.addEventListener('livewire:navigated', () => {
	document.querySelectorAll('pre code').forEach((block) => {
		hljs.highlightElement(block);
	});
});
