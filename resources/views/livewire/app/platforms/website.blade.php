<div>
    <flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="adjustments-vertical" name="integration">How to connect in your website</flux:tab>
                <flux:tab icon="cog-6-tooth" name="chat_interface">Chat Interface</flux:tab>    
                <flux:tab icon="sparkles" name="styling">Styling</flux:tab>           
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="integration">
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl mb-2 font-medium">Add Full Card in your website</h3>
                        <p class="mt-1 text-sm">Copy and paste this code into your website where you want the chatbot to appear.</p>
                    </div>

                    <div x-data="{ copied: false }" class="relative">
                        <flux:textarea readonly rows="8" x-ref="embedCode" class="font-mono text-sm">
                            {{ $this->getDefaultEmbedCode() }}</flux:textarea>

                        <div class="absolute right-4 top-4">
                            <flux:button
                                size="sm"
                                icon="clipboard"
                                variant="primary"
                                x-on:click=" $refs.embedCode.select();
                                        window.navigator.clipboard.writeText($refs.embedCode.value);
                                        copied = true;
                                        setTimeout(() => copied = false, 2000)
                                    ">
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </flux:button>
                        </div>
                    </div>

                    {{-- <div class="rounded-lg bg-amber-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <flux:icon name="light-bulb" class="h-5 w-5 text-amber-400" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Integration Tips</h3>
                                <div class="mt-2 text-sm text-amber-700">
                                    <ul class="list-disc space-y-1 pl-5">
                                        <li>Place the iframe code anywhere in your HTML where you want the chat interface to appear</li>
                                        <li>The iframe will adapt to its container's width while maintaining a 600px height</li>
                                        <li>You can adjust the iframe's dimensions by modifying the width and height attributes</li>
                                        <li>Use the configurator below to customize the appearance and behavior</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <flux:separator text="or" />

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl mb-4 font-medium">Add Widget in your website using PageWidgets.com</h3>
                        <p class="mt-1 mb-2 text-sm">If you want to use a widget, you can use PageWidgets.com to embed the chatbot on your website as a widget. PageWidgets has a free plan and is very easy to use and setup.</p>
                        <flux:button size="sm"  variant="primary" href="https://pagewidgets.com/" target="_blank">Create your free account here</flux:button>
                        <p class="mt-2 mb-2 text-sm">Once you have created your account, create a new widget and add ReplyElf Block and add the following code:</p>
                        <flux:input disabled readonly rows="8" value="{{ $this->workspace->uuid }}" class="font-mono text-sm"> </flux:input>
                        <p class="mt-2 text-sm text-gray-500">Then copy their widget code and paste it into your website.</p>
                        
                    </div>

                    <div x-data="{ copied: false }" class="relative">
                        
                    </div>
                </div>
    
            </flux:tab.panel>

            <flux:tab.panel class="space-y-6" name="chat_interface">
                <form class="space-y-6" wire:submit="storeChatInterface">
                    <flux:input description="This message will be displayed when the chat interface is first opened." label="Welcome Message"
                        wire:model="platform_website.welcome_message" />

                    <flux:input
                        description="This message will be shown whenever the AI is currently processing a request and is temporarily unavailable or when an unexpected error occurs during its operation."
                        label="Fallback Message" wire:model="platform_website.fallback_message" />

                    <flux:input description="This text will be displayed in the chat input field." label="Message Placeholder"
                        wire:model="platform_website.message_placeholder" />

                    <flux:textarea description="These messages will be displayed as buttons for the user to click on. You can add multiple messages by separating them with a new line."
                        label="Suggested Messages" placeholder="What is your favorite color?" rows="4" wire:model="platform_website.suggested_messages" />

                    <flux:switch description="Controls how the AI personalizes the conversation experience by remembering user details. When enabled, the AI will recognize returning users and address them by name if available, creating a more engaging and personalized interaction."
                            label="Remember User Information"
                            wire:model="platform_website.user_recognition" />

                    <flux:switch description="Allows the AI to maintain continuity in conversations by referencing previous messages when relevant. When enabled, the AI will provide more contextual and coherent responses based on prior interactions."
                        label="Maintain Conversation Continuity"
                        wire:model="platform_website.conversation_continuity" />

                    <flux:switch description="Allow users to reset the conversation." label="Reset Button" wire:model="platform_website.reset_button" />

                    <flux:switch description="Allow users to send messages by pressing Enter" label="Send Message on Enter" wire:model="platform_website.send_on_enter" />

                    <flux:button icon="check" type="submit" variant="primary">Save Changes</flux:button>
                
                </form>
            </flux:tab.panel>

            <flux:tab.panel class="space-y-6" name="styling">
                <form class="space-y-6" wire:submit="storeStyling">
					<flux:field>
                            <flux:label>Choose a theme</flux:label>
                            <flux:description>Styling the chat interface is just a click away.</flux:description>

                            <div class="mt-2 flex gap-4 overflow-x-auto pb-3">
                                @foreach ($themePresets as $key => $preset)
                                    <div @class([
                                        'relative flex-shrink-0 w-40 border border-zinc-200 rounded-lg p-3 transition-all hover:shadow-md',
                                        'opacity-100 border-zinc-300 shadow-md' => data_get($styling, 'theme') === $key,
                                        'opacity-50' => data_get($styling, 'theme') !== $key,
                                        'cursor-pointer' => $loop->index < 2 || auth()->user()->pro,
                                        'cursor-not-allowed' => $loop->index >= 2 && !auth()->user()->pro,
                                    ])>

                                        @if ($loop->index < 2 || auth()->user()->pro)
                                            <button class="absolute inset-0" type="button"wire:click="applyTheme('{{ $key }}')"></button>
                                        @endif

                                        @if ($loop->index >= 2 && !auth()->user()->pro)
                                            <p class="mb-1 text-xs text-red-600">
                                                Upgrade to Pro to unlock more themes
                                            </p>
                                        @endif

                                        <div class="flex flex-col space-y-2">
                                            <div class="mb-1 text-sm font-medium">{{ $preset['name'] }}</div>
                                            <div class="h-6 w-full" style="background-color: {{ $preset['colors']['primary'] }}"></div>
                                            <div class="flex space-x-1">
                                                <div class="h-4 w-full rounded" style="background-color: {{ $preset['colors']['chat_bubble_user'] }}"></div>
                                                <div class="h-4 w-full rounded" style="background-color: {{ $preset['colors']['chat_bubble_assistant'] }}"></div>
                                            </div>
                                            <div class="h-6 w-full" style="background-color: {{ $preset['colors']['background'] }}; border: 1px solid #e5e7eb;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </flux:field>

                        <flux:select label="Font Size" wire:model.live="styling.font_size">
                            <option value="12px">12px</option>
                            <option value="14px">14px</option>
                            <option value="16px">16px</option>
                            <option value="18px">18px</option>
                            <option value="20px">20px</option>
                            <option value="22px">22px</option>
                            <option value="24px">24px</option>
                            <option value="26px">26px</option>
                            <option value="28px">28px</option>
                            <option value="30px">30px</option>
                            <option value="32px">32px</option>
                            <option value="34px">34px</option>
                            <option value="36px">36px</option>
                        </flux:select>

                        <flux:select label="Font Family" wire:model.live="styling.font_family">
                            @foreach ($fontFamilies as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        @livewire('app.platforms.font-preview', ['fontFamily' => $styling['font_family']], key('font-preview-' . $styling['font_family']))

                
                        <flux:button icon="check" type="submit" variant="primary">Save Changes</flux:button>

                </form>
            </flux:tab.panel>
        </flux:tab.group>
    </flux:card>

    <script>
		document.addEventListener('livewire:initialized', function() {
			@this.watch('styling.font_family', value => {
				const fontFamily = value;
				@this.$refresh();
			});
		});
	</script>

</div>
