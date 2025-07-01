<div>
   
	<flux:card>
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab icon="adjustments-vertical" name="general">General</flux:tab>
                <flux:tab icon="information-circle" name="business">Business Information</flux:tab>
                <flux:tab icon="sparkles" name="import_content">Import Content</flux:tab>
            </flux:tabs>

            <flux:tab.panel class="space-y-6" name="general">
                <form class="space-y-6" wire:submit="saveGeneral">
                    <flux:field x-data="{ length: $wire.general.instructions?.length || 0, maxLength: 1500 }">
						<flux:label class="flex items-center justify-between">
							Primary Instructions

							<flux:badge icon="document-text" size="sm">
								<div x-text="maxLength - length"></div> &nbsp; characters remaining
							</flux:badge>
						</flux:label>
						<flux:description>
							These are the main instructions that guide how the AI responds. Be specific about your requirements.
						</flux:description>

						<flux:textarea maxlength="1500"
							placeholder="Instructions for the AI to follow when responding to the user."
							rows="auto"
							wire:model="general.instructions"
							x-on:input="length = $event.target.value.length" />
					</flux:field>

                    <flux:field x-data="{ length: $wire.general.custom_rules?.length || 0, maxLength: 1000 }">
						<flux:label class="flex items-center justify-between">
							Custom Conversation Rules

							<flux:badge icon="document-text" class="mx-2" size="sm">
								<div x-text="maxLength - length"></div> &nbsp; characters remaining
							</flux:badge>
						</flux:label>
						<flux:description>
							Specific rules that the AI should follow in all conversations. These are separate from the main instructions.
						</flux:description>

						<flux:textarea maxlength="1000"
							placeholder="Additional rules for the AI to follow (e.g., 'Always mention our return policy when discussing products')"
							rows="auto"
							wire:model="general.custom_rules"
							x-on:input="length = $event.target.value.length" />
					</flux:field>

					<flux:textarea description="The message that will be used when the AI doesn't know how to respond."
						label="Fallback Response"
						placeholder="Example: I don't have enough information to answer that question. Please contact our support team at support@example.com for assistance."
						rows="auto"
						wire:model="general.fallback_response" />

					<flux:radio.group description="Controls how predictable the AI's responses are. Lower values produce more consistent and focused responses, while higher values allow more creativity and variation."
						label="Temperature"
						size="sm"
						variant="segmented"
						wire:model="general.temperature">
						{{-- blade-formatter-disable --}}
						<flux:radio icon="adjustments-vertical" label="Precise" value="0.1" />
						<flux:radio icon="scale" label="Balanced" value="0.5" />
						<flux:radio icon="bolt" label="Dynamic" value="0.7" />
						<flux:radio icon="light-bulb" label="Creative" value="1" />
                        {{-- blade-formatter-enable --}}
					</flux:radio.group>

					<flux:radio.group description="Defines the overall tone of the AI's conversation style with users."
						label="Conversation Tone"
						size="sm"
						variant="segmented"
						wire:model="general.tone">
						{{-- blade-formatter-disable --}}
                        <flux:radio icon="briefcase" label="Professional" value="professional" />
                        <flux:radio icon="face-smile" label="Friendly" value="friendly" />
                        <flux:radio icon="chat-bubble-left-right" label="Casual" value="casual" />
                        <flux:radio icon="academic-cap" label="Formal" value="formal" />
                        {{-- blade-formatter-enable --}}
					</flux:radio.group>

					<flux:radio.group description="Controls how long or detailed the AI's responses will be."
						label="Response Length"
						size="sm"
						variant="segmented"
						wire:model="general.response_length">
						{{-- blade-formatter-disable --}}
                        <flux:radio icon="minus" label="Concise" value="concise" />
                        <flux:radio icon="equals" label="Moderate" value="moderate" />
                        <flux:radio icon="plus" label="Detailed" value="detailed" />
                        {{-- blade-formatter-enable --}}
					</flux:radio.group>

					<flux:radio.group description="Determines how the AI structures its responses and interacts with users."
						label="Message Style"
						size="sm"
						variant="segmented"
						wire:model="general.message_style">
						{{-- blade-formatter-disable --}}
                        <flux:radio icon="arrow-right" label="Direct" value="direct" />
                        <flux:radio icon="chat-bubble-oval-left-ellipsis" label="Conversational" value="conversational" />
                        <flux:radio icon="academic-cap" label="Educational" value="educational" />
                        {{-- blade-formatter-enable --}}
					</flux:radio.group>

                    <flux:radio.group description="Controls how much context the AI should remember from previous messages."
						label="Conversation Memory"
						size="sm"
						variant="segmented"
						wire:model="general.conversation_memory">
						<flux:radio label="Minimal" value="1" />
						<flux:radio label="Low" value="2" />
						<flux:radio label="Medium" value="3" />
						<flux:radio label="High" value="4" />
						<flux:radio label="Maximum" value="5" />
					</flux:radio.group>

                    <flux:field x-data="{ tokens: $wire.general.max_tokens || 0 }">
						<flux:label class="flex items-center justify-between">
							Max Response Tokens

							<flux:badge icon="cpu-chip" size="sm">
								<div x-text="tokens"></div>
							</flux:badge>
						</flux:label>
						<flux:description>
							The maximum number of tokens (roughly words) the AI can use in a response. Higher numbers allow for longer answers but may consume more resources.
						</flux:description>

						<input class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-zinc-200 dark:bg-zinc-700"
							max="500"
							min="50"
							step="50"
							type="range"
							wire:model="general.max_tokens"
							x-on:input="tokens = $event.target.value" />
					</flux:field>

					<flux:separator variant="subtle" />

					<flux:switch description="When enabled, the AI will explicitly state when it doesn't know something or when information might be outdated."
						label="Acknowledge Knowledge Limitations"
						wire:model="general.knowledge_limitations" />

					<flux:button icon="check"
						type="submit"
						variant="primary">Save Changes</flux:button>
                </form>
            </flux:tab.panel>

            <flux:tab.panel class="space-y-6" name="import_content">
                <form class="space-y-6" wire:submit="saveTraining">
                    <flux:field x-data="{ length: $wire.training.instructions?.length || 0, maxLength: 500 }">
                        <flux:label badge="Optional" class="flex items-center justify-between">
                            Training Instructions

                            <flux:badge icon="document-text" class="mx-2" size="sm">
                                <div x-text="maxLength - length"></div> &nbsp; characters remaining
                            </flux:badge>
                        </flux:label>
                        <flux:description>
                            These are the main instructions that guide how the AI should process the sources. Those instructions will be applied after our system prompt.
                        </flux:description>

                        <flux:textarea maxlength="500"
                                placeholder="Instructions for the AI to follow when processing the sources."
                                rows="auto"
                                wire:model="training.instructions"
                                x-on:input="length = $event.target.value.length" />
                        </flux:field>

                        <flux:field x-data="{ length: $wire.training.rules?.length || 0, maxLength: 500 }">
                        <flux:label badge="Optional" class="flex items-center justify-between">
                            Training Rules

                            <flux:badge icon="document-text" class="mx-2" size="sm">
                                <div x-text="maxLength - length"></div> &nbsp; characters remaining
                            </flux:badge>
                        </flux:label>
                        <flux:description>
                            Specific rules that the AI should follow when processing the sources. These are separate from the main instructions.
                        </flux:description>

                        <flux:textarea  maxlength="500"
                                placeholder="Rules for the AI to follow when processing the sources."
                                rows="auto"
                                wire:model="training.rules"
                                x-on:input="length = $event.target.value.length" />
                        </flux:field>

                        <flux:separator variant="subtle" />

                        <flux:radio.group description="Controls how predictable the AI's responses are. Lower values produce more consistent and focused responses, while higher values allow more creativity and variation."
                            label="Training Temperature"
                            size="sm"
                            variant="segmented"
                            wire:model="training.temperature">
                            {{-- blade-formatter-disable --}}
                            <flux:radio icon="adjustments-vertical" label="Precise" value="0.1" />
                            <flux:radio icon="scale" label="Balanced" value="0.5" />
                            <flux:radio icon="bolt" label="Dynamic" value="0.7" />
                            <flux:radio icon="light-bulb" label="Creative" value="1" />
                            {{-- blade-formatter-enable --}}
                        </flux:radio.group>

                        <flux:radio.group description="Controls how long or detailed the training data will be."
                            label="Training Data Length"
                            size="sm"
                            variant="segmented"
                            wire:model="training.length">
                            {{-- blade-formatter-disable --}}
                            <flux:radio icon="minus" label="Concise" value="concise" />
                            <flux:radio icon="equals" label="Moderate" value="moderate" />
                            <flux:radio icon="plus" label="Detailed" value="detailed" />
                            {{-- blade-formatter-enable --}}
                        </flux:radio.group>

                        <flux:switch description="When enabled, the AI will include links in the data that it uses to answer the questions."
                            label="Include Website Links"
                            wire:model="training.links" />

                        <flux:button icon="check"
                            type="submit"
                            variant="primary">Save Changes</flux:button>
                    </form>
            </flux:tab.panel>


            <flux:tab.panel name="business">
                <form class="space-y-6" wire:submit="saveBusiness">

                        <flux:input badge="Optional" description="This will be used when the AI refers to your business in conversations."
                            label="Business Name"
                            placeholder="Your business name"
                            wire:model="business.name" />
    
                        <flux:input badge="Optional" description="The URL of your business website."
                            label="Business Website"
                            placeholder="https://www.yourbusiness.com"
                            wire:model="business.website" />
    
                        <flux:textarea badge="Optional" description="Helps the AI understand your business context when responding to customers and processing the sources."
                            label="Business Description"
                            placeholder="Brief description of what your business does"
                            rows="auto"
                            wire:model="business.description" />
    
                        <flux:input badge="Optional" description="The audience of your business. Should be a comma separated list of the audience types."
                            label="Business Audience"
                            placeholder="e.g. B2B, B2C, Entrepreneurs, Art Lovers"
                            wire:model="business.audience" />
    
                        <flux:button icon="check"
                            type="submit"
                            variant="primary">Save Changes</flux:button>
                </form>
            </flux:tab.panel>
            
        </flux:tab.group>
	</flux:card>
</div>
