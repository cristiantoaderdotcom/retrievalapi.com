<?php

namespace App\Traits\Livewire\App\Widgets;

use Illuminate\Support\Arr;

trait HasTemplates {
	protected function getTemplate($type, $link = null): ?array {
		return match ($type) {
			'heading' => [[
				'key' => 'content',
				'value' => [
					'heading' => [
						'text' => 'Hello! Your text goes here...',
						'size' => 'pw:text-sm',
						'weight' => 'pw:font-normal',
					],
					'subheading' => [
						'text' => '',
						'size' => 'pw:text-sm',
						'weight' => 'pw:font-normal'
					],
				]
			]],
			'contact-form' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'text' => 'Contact us',
						'align' => 'pw:justify-start',
						'icon' => Arr::random([
							'https://cdn-icons-png.freepik.com/32/693/693886.png',
							'https://cdn-icons-png.freepik.com/32/719/719713.png'
						]),
						'background' => [
							'type' => 'solid',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#070707',
								'via' => '',
								'to' => '#070707'
							]
						],
						'color' => '#f4f4f5',
					]
				], [
					'key' => 'content',
					'value' => [
						'heading' => [
							'text' => Arr::random([
								'We’d love to hear from you',
								'Get in touch',
								'Send us a message'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-black',
						],
						'subheading' => [
							'text' => Arr::random([
								'We\'re here to answer your questions and provide assistance.',
								'Fill out the form below, and we\'ll respond promptly.',
								'Send us your queries, feedback, or requests—we’re just a click away. Let me know if you\'d like more options or if you have a specific tone in mind!'
							]),
							'size' => 'pw:text-sm',
						],
					]
				]
			],
			'discount-code' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'icon' => 'https://cdn-icons-png.freepik.com/32/2273/2273865.png',
						'text' => 'Get Discount Now',
						'align' => 'pw:justify-start',
						'background' => [
							'type' => 'solid',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#070707',
								'via' => '',
								'to' => '#070707'
							]
						],
						'color' => '#f4f4f5',
					]
				], [
					'key' => 'content',
					'value' => [
						'heading' => [
							'text' => Arr::random([
								'Unlock your exclusive discount today!',
								'Get 10% off your first purchase',
								'Claim your discount code now!',
								'Save more, shop more!',
								'Special offer just for you!'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-black',
						],
						'subheading' => [
							'text' => Arr::random([
								'Enter your email below to receive a special coupon for your next purchase.',
								'Sign up with your email and grab your exclusive discount instantly.',
								'Enter your email to claim your limited-time discount coupon.',
								'Provide your email to unlock savings on your next purchase.',
								'Get your discount code by entering your email below.'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-normal',
						],
					]
				], [
					'key' => 'coupon',
					'value' => [
						'heading' => [
							'text' => Arr::random([
								'Congratulations!',
								'You\'ve unlocked a special offer!',
								'Your exclusive discount code is here!',
								'Your discount code is ready!',
								'Claim your discount now!'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-black',
						],
						'subheading' => [
							'text' => Arr::random([
								'Use the code below to get 10% off your next purchase.',
								'Copy the code below and apply it at checkout to save 10%.',
								'Enjoy 10% off your next purchase with the code below.',
								'Use the code below to get a 10% discount on your next order.',
								'Apply the code below to save 10% on your next purchase.'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-normal',
						],
						'name' => '10% Discount',
						'code' => 'WELCOME10_2025',
					]
				]
			],
			'newsletter' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'icon' => 'https://cdn-icons-png.freepik.com/64/6957/6957020.png',
						'text' => 'Newsletter',
						'align' => 'pw:justify-start',
						'background' => [
							'type' => 'solid',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#070707',
								'via' => '',
								'to' => '#070707'
							]
						],
						'color' => '#f4f4f5',
					]
				], [
					'key' => 'content',
					'value' => [
						'heading' => [
							'text' => Arr::random([
								'Stay updated with our latest news and offers!',
								'Get the latest updates and promotions by subscribing to our newsletter.',
								'Join our newsletter to stay informed about our latest news and offers.',
								'Subscribe to our newsletter to get the latest news and offers.'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-black',
						],
						'subheading' => [
							'text' => Arr::random([
								'Sign up for our newsletter to receive the latest news and offers.',
								'Get exclusive updates and promotions by subscribing to our newsletter.',
								'Join our newsletter to stay informed about our latest news and offers.',
								'Subscribe to our newsletter to get the latest news and offers.'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-normal',
						],
					]
				]
			],
			'chatbot-ai' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'icon' => asset('assets/icons/chatbot-ai.gif'),
						'text' => 'Chatbot AI',
						'align' => 'pw:justify-start',
						'background' => [
							'type' => 'solid',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#070707',
								'via' => '',
								'to' => '#070707'
							]
						],
						'color' => '#f4f4f5',
					]
				]
			],

			'facebook' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://www.facebook.com/',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504903.png',
					'text' => 'Facebook',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#0072ff',
							'via' => '',
							'to' => '#00c6ff'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'instagram' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://www.instagram.com/',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504918.png',
					'text' => 'Instagram',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#833ab4',
							'via' => '#fd1d1d',
							'to' => '#fcb045'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'tiktok' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://tiktok.com',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504942.png',
					'text' => 'TikTok',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#333333',
							'via' => '',
							'to' => '#070707'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'x' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://twitter.com',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/256/5968/5968830.png',
					'text' => 'Twitter (X)',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'youtube' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://youtube.com',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504965.png',
					'text' => 'YouTube',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#e52d27',
							'via' => '',
							'to' => '#b31217'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'linkedin' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://linkedin.com',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504923.png',
					'text' => 'LinkedIn',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#2867b2',
							'via' => '',
							'to' => '#1E63B2'
						]
					],
					'color' => '#f4f4f5',
				]
			]],

			'phone-call' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => 'tel:+your_phone_number',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/16076/16076069.png',
					'text' => 'Call us',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#10b981',
							'via' => '',
							'to' => '#064e3b'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'email' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => 'mailto:email@example.com',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/16152/16152077.png',
					'text' => 'Email us',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
					'color' => '#f4f4f5',
				]
			]],

			'discord' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://discord.com/channels/',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504896.png',
					'text' => 'Chat on WhatsApp',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#8ca0f5',
							'via' => '',
							'to' => '#5a6ec3'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'fb-messenger' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://m.me/username_or_page_id',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/2504/2504926.png',
					'text' => 'Chat on Messenger',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#006dd2',
							'via' => '',
							'to' => '#0f56d2'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'skype' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'skype:your_username?chat',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/1409/1409949.png',
					'text' => 'Chat on Skype',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#1c92d2',
							'via' => '',
							'to' => '#507bb4'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'telegram' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://t.me/your_username',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/15015/15015947.png',
					'text' => 'Chat on Telegram',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#1c92d2',
							'via' => '',
							'to' => '#507bb4'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'viber' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'viber://chat?number=your_phone_number',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/32/4096/4096283.png',
					'text' => 'Chat on Viber',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#5322d2',
							'via' => '',
							'to' => '#342ed2'
						]
					],
					'color' => '#f4f4f5',
				]
			]],
			'wechat' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'weixin://dl/chat?your_id',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504955.png',
					'text' => 'Chat on WeChat',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#00f073',
							'via' => '',
							'to' => '#00d75a'
						]
					],
					'color' => '#00471e',
				]
			]],
			'whatsapp' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://wa.me/your_phone_number',
					'target' => '_blank',
					'icon' => 'https://cdn-icons-png.freepik.com/64/2504/2504957.png',
					'text' => 'Chat on WhatsApp',
					'align' => 'pw:justify-start',
					'background' => [
						'type' => 'gradient',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#25D366',
							'via' => '',
							'to' => '#128C7E'
						]
					],
					'color' => '#f4f4f5',
				]
			]],

			'calendly' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'text' => 'Schedule a meeting',
						'align' => 'pw:justify-start',
						'icon' => Arr::random([
							'https://cdn-icons-png.freepik.com/32/9894/9894298.png',
							'https://cdn-icons-png.freepik.com/32/8587/8587295.png'
						]),
						'background' => [
							'type' => 'gradient',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#a51403',
								'via' => '',
								'to' => '#e21a03'
							]
						],
						'color' => '#FCFCFD',
					]
				]
			],
			'faq' => [
				[
					'key' => 'button',
					'value' => [
						'tag' => 'button',
						'text' => 'View FAQs',
						'align' => 'pw:justify-start',
						'icon' => Arr::random([
							'https://cdn-icons-png.freepik.com/256/16799/16799228.png',
						]),
						'background' => [
							'type' => 'gradient',
							'solid' => ['color' => '#070707'],
							'gradient' => [
								'direction' => 'to right',
								'from' => '#a51403',
								'via' => '',
								'to' => '#e21a03'
							]
						],
						'color' => '#FCFCFD',
					],
				], [
					'key' => 'content',
					'value' => [
						'heading' => [
							'text' => Arr::random([
								'Got questions? We have answers!',
								'Commonly asked questions',
								'Find answers to your queries here',
								'Your questions, answered!'
							]),
							'size' => 'pw:text-sm',
							'weight' => 'pw:font-black',
						],
						'subheading' => [
							'text' => Arr::random([
								'Check out our FAQs to find answers to common questions.',
								'Get answers to frequently asked questions about our products and services.',
								'Find answers to common queries about our products and services.',
								'Explore our FAQs to find answers to your questions.'
							]),
							'size' => 'pw:text-sm',
						],
					]
				], [
					'key' => 'faqs',
					'value' => [
						[
							'question' => 'What is your return policy?',
							'answer' => 'We offer a 30-day return policy for all products. If you are not satisfied with your purchase, you can return it within 30 days for a full refund.',
						], [
							'question' => 'How do I track my order?',
							'answer' => 'You can track your order by visiting the "Order Status" page on our website. Enter your order number and email address to view the status of your order.',
						], [
							'question' => 'Do you offer international shipping?',
							'answer' => 'Yes, we offer international shipping to select countries. Shipping rates and delivery times may vary depending on your location.',
						]
					]
				]
			],

			'buy-me-a-coffee' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://buymeacoffee.com/your_username',
					'target' => '_blank',
					'icon' => asset('assets/icons/5804296.png'),
					'text' => 'Buy Me a Coffee',
					'align' => 'pw:justify-start',
					'color' => '#f4f4f5',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#fd0',
							'via' => '',
							'to' => '#fd0'
						]
					],
				]
			]],
			'patreon' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://patreon.com/your_username',
					'target' => '_blank',
					'icon' => asset('https://cdn-icons-png.freepik.com/64/5968/5968747.png'),
					'text' => 'Support on Patreon',
					'align' => 'pw:justify-start',
					'color' => '#f4f4f5',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
				]
			]],
			'ko-fi' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://ko-fi.com/your_username',
					'target' => '_blank',
					'icon' => asset('assets/icons/3509624.png'),
					'text' => 'Buy Me a Ko-fi',
					'align' => 'pw:justify-start',
					'color' => '#f4f4f5',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
				]
			]],
			'tipeee' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://tipeee.com/your_username',
					'target' => '_blank',
					'icon' => asset('assets/icons/7829174.png'),
					'text' => 'Support on Tipeee',
					'align' => 'pw:justify-start',
					'color' => '#f4f4f5',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
				]
			]],
			'coindrop' => [[
				'key' => 'button',
				'value' => [
					'tag' => 'a',
					'href' => $link ?? 'https://coindrop.to/your_username',
					'target' => '_blank',
					'icon' => asset('assets/icons/2304296.png'),
					'text' => 'Drop a Coin',
					'align' => 'pw:justify-start',
					'color' => '#f4f4f5',
					'background' => [
						'type' => 'solid',
						'solid' => ['color' => '#070707'],
						'gradient' => [
							'direction' => 'to right',
							'from' => '#070707',
							'via' => '',
							'to' => '#070707'
						]
					],
				]
			]],

			'cookies' => [[
				'key' => 'content',
				'value' => [
					'text' => '<p>For purposes such as displaying personalized content, we use cookie modules or similar technologies. By clicking the "<strong>I agree</strong>" button, you consent to allowing the collection of information through cookies or similar technologies. <a target="_blank" rel="noopener noreferrer nofollow" href="https://allaboutcookies.org"><u>Learn more about cookies</u>.</a></p>',
				]
			]],

			default => null,
		};
	}
}
