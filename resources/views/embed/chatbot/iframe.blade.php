{{-- @php($settings = $workspace->setting->chat_interface ?? []) --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1" name="viewport">
		<title>{{ $workspace->name ?? 'Chatbot' }}</title>

		<!-- Libraries -->
		<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/md5-js@0.0.3/md5.min.js"></script>

		<!-- Custom Fonts -->
		@if (!in_array(data_get($settings, 'font_family', 'system-ui'), ['system-ui']))
			<link href="https://fonts.googleapis.com" rel="preconnect">
			<link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
			<link href="https://fonts.googleapis.com/css2?family={{ str(data_get($settings, 'font_family'))->replace('-', '+') }}:wght@300;400;500;600;700&display=swap&_={{ time() }}" rel="stylesheet">
		@endif

		@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
			@vite(['resources/css/app.css', 'resources/js/embed/embed.js'])
		@endif


		<style>
			:root {
				--primary-color: {{ $settings['custom_colors']['primary'] }};
				--secondary-color: {{ $settings['custom_colors']['secondary'] }};
				--background-color: {{ $settings['custom_colors']['background'] }};
				--text-color: {{ $settings['custom_colors']['text'] }};
				--chat-bubble-user: {{ $settings['custom_colors']['chat_bubble_user'] }};
				--chat-bubble-assistant: {{ $settings['custom_colors']['chat_bubble_assistant'] }};
				--chat-text-user: {{ $settings['custom_colors']['chat_text_user'] }};
				--chat-text-assistant: {{ $settings['custom_colors']['chat_text_assistant'] }};
				--font-family: {{ $settings['font_family'] }};
			}

			html {
				width: 100%;
				height: 100%;
				margin: 0;
				padding: 0;
				overflow-x: hidden;
				overflow-y: auto;
				scroll-behavior: smooth;
			}

			body {
				width: 100%;
				min-height: 100vh;
				margin: 0;
				padding: 0;
				overflow-x: hidden;
				overflow-y: auto;
				background: transparent;
				font-family: var(--font-family);
				scroll-behavior: smooth;
			}

			.chat-bubble-radius {
				border-radius: 1rem;
			}

			.chat-bubble-user {
				background-color: var(--chat-bubble-user);
				color: var(--chat-text-user);
			}

			.chat-bubble-assistant {
				background-color: var(--chat-bubble-assistant);
				color: var(--chat-text-assistant);
			}

			.chat-button {
				border-radius: 0.25rem;
				background-color: var(--primary-color);
				color: white;
			}

			.suggested-button {
				border-radius: 1rem;
			}

			.chat-container {
				width: 100%;
				min-height: 100vh;
				max-width: 100%;
				box-shadow: none;
				border-radius: 0;
				font-family: var(--font-family);
				display: flex;
				flex-direction: column;
				padding-bottom: 20px; /* Add some bottom padding for better UX */
			}

			.chat-messages-container {
				flex: 1;
				overflow: visible; /* Let content flow naturally */
				display: flex;
				flex-direction: column;
			}

			.chat-input-container {
				position: sticky;
				bottom: 0;
				background-color: var(--background-color);
				z-index: 10;
				flex-shrink: 0;
				margin-top: auto; /* Push to bottom */
			}

			.fade-in {
				animation: fadeIn 0.3s ease-in-out;
			}

			@keyframes fadeIn {
				from {
					opacity: 0;
				}

				to {
					opacity: 1;
				}
			}

			/* Ensure smooth scrolling for all scrollable elements */
			* {
				scroll-behavior: smooth;
			}

			/* Improve scrolling performance */
			.chat-messages-container {
				-webkit-overflow-scrolling: touch;
				scrollbar-width: thin;
			}

			{{ data_get($settings, 'custom_css') ?? '' }}

			.chat-header,
			.chat-messages,
			.chat-input,
			.chat-bubble-user,
			.chat-bubble-assistant,
			.chat-button,
			.suggested-button,
			input,
			textarea,
			button {
				font-family: var(--font-family);
			}

			.powered-by {
				font-size: 12px;
				color: var(--text-color);
				opacity: 0.7;
			}
			
			.powered-by a {
				color: var(--text-color);
				text-decoration: none;
			}
			
			.powered-by a:hover {
				text-decoration: underline;
			}
		</style>
	</head>

	<body class="flex h-full flex-col antialiased">
		<div class="chat-container fade-in bg-white" style="background-color: var(--background-color);">
			<div class="flex items-center justify-between px-3" style="color: var(--text-color); border-color: rgba(0,0,0,0.1);">
				@if ($settings['reset_button'] ?? true)
					<button id="chatbot-reset" class="flex size-8 items-center justify-center rounded-lg bg-transparent hover:bg-black/5" style="color: var(--text-color); display: none;"
						title="Reset Chat">
						<svg class="size-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
							<path d="M3 3v5h5"></path>
						</svg>
					</button>
				@else
					<div></div>
				@endif
				
				<div class="powered-by text-center">
					<a href="https://replyelf.com" target="_blank" rel="noopener"><img src="{{ asset('assets/images/logo/powered-by.png') }}" alt="ReplyElf Logo" class="h-8"></a>
				</div>
			</div>

			<div class="chat-messages-container">
				<div id="chatbot-messages" class="flex-1 space-y-4 px-4 py-6"></div>
				<div id="chatbot-suggested" class="px-4 py-3"></div>
			</div>

			<div class="chat-input-container border-t p-1" style="border-color: rgba(0,0,0,0.1);">
				<form id="chatbot-form" class="relative">
					<input 
						id="chatbot-input"
						class="w-full rounded-2xl border-2 px-4 py-3 pr-20 text-sm focus:border-transparent focus:outline-none focus:ring-2"
						placeholder="{{ $settings['message_placeholder'] ?? 'Type your message here...' }}"
						required
						style="border-color: var(--chat-bubble-assistant); color: var(--chat-text-assistant);" 
						type="text" 
					/>
					<div class="absolute right-4 top-0 flex h-full items-center">
						<button class="chat-button flex size-8 items-center justify-center disabled:opacity-50" type="submit">
							<svg class="size-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="m22 2-7 20-4-9-9-4Z"></path>
								<path d="M22 2 11 13"></path>
							</svg>
						</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Configuration script -->
		<script>
			window.chatbotConfig = {
				uuid: @js($workspace->uuid),
				settings: @json($settings),
				storageId: @js(md5($workspace->uuid)),
				baseUrl: @js(url('/')),
			};
		</script>
	</body>
</html>
