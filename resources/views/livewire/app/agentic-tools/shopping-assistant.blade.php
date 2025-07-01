<div>
    <flux:heading size="lg">Shopping Assistant</flux:heading>
    <flux:subheading>Configure your AI Shopping Assistant to help customers find and learn about products</flux:subheading>

    <div class="mt-6">
        <flux:tabs variant="segmented" wire:model="tab">
            <flux:tab name="configuration">Configuration</flux:tab>
        </flux:tabs>
    </div>

    <div class="mt-6">
        @if($tab === 'configuration')
            <div class="space-y-6">
                <!-- Main Toggle -->
                <flux:card>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="md">Shopping Assistant</flux:heading>
                            <flux:subheading>Enable AI-powered shopping assistance for your customers</flux:subheading>
                        </div>
                        <flux:button 
                            wire:click="toggleTool" 
                            variant="{{ $agentic_shopping_assistant['enabled'] ? 'primary' : 'ghost' }}"
                            size="sm"
                        >
                            {{ $agentic_shopping_assistant['enabled'] ? 'Enabled' : 'Disabled' }}
                        </flux:button>
                    </div>
                </flux:card>

                @if($agentic_shopping_assistant['enabled'])
                    <!-- Product Details Tool -->
                    <flux:card>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <flux:heading size="md">{{ $agentic_shopping_assistant['product_details']['label'] }}</flux:heading>
                                    <flux:subheading>Show detailed product information when customers ask about specific products</flux:subheading>
                                </div>
                                <flux:button 
                                    wire:click="toggleProductDetails" 
                                    variant="{{ $agentic_shopping_assistant['product_details']['enabled'] ? 'primary' : 'ghost' }}"
                                    size="sm"
                                >
                                    {{ $agentic_shopping_assistant['product_details']['enabled'] ? 'Enabled' : 'Disabled' }}
                                </flux:button>
                            </div>

                            @if($agentic_shopping_assistant['product_details']['enabled'])
                                <div class="grid gap-4 md:grid-cols-2">
                                                                    <flux:input 
                                    wire:model="agentic_shopping_assistant.product_details.label" 
                                    label="Tool Label" 
                                />

                                    <flux:field>
                                        <flux:label>Card Template</flux:label>
                                        <flux:select wire:model="agentic_shopping_assistant.product_details.card_template">
                                            <flux:select.option value="detailed">Detailed Card</flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <flux:textarea 
                                    wire:model="agentic_shopping_assistant.product_details.trigger_keywords" 
                                    rows="2"
                                    label="Trigger Keywords"
                                    description="Comma-separated keywords that trigger this tool"
                                />

                                <flux:input 
                                    wire:model="agentic_shopping_assistant.product_details.confirmation_message" 
                                    label="Confirmation Message"
                                    description="Message shown when this tool is triggered"
                                />

                                <flux:textarea 
                                    wire:model="agentic_shopping_assistant.product_details.rules" 
                                    rows="3"
                                    label="AI Processing Rules"
                                    description="Instructions for the AI on how to use this tool"
                                />
                            @endif
                        </div>
                    </flux:card>

                    <!-- Product Recommendations Tool -->
                    <flux:card>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <flux:heading size="md">{{ $agentic_shopping_assistant['product_recommendations']['label'] }}</flux:heading>
                                    <flux:subheading>Show product recommendations when customers are browsing or looking for suggestions</flux:subheading>
                                </div>
                                <flux:button 
                                    wire:click="toggleProductRecommendations" 
                                    variant="{{ $agentic_shopping_assistant['product_recommendations']['enabled'] ? 'primary' : 'ghost' }}"
                                    size="sm"
                                >
                                    {{ $agentic_shopping_assistant['product_recommendations']['enabled'] ? 'Enabled' : 'Disabled' }}
                                </flux:button>
                            </div>

                            @if($agentic_shopping_assistant['product_recommendations']['enabled'])
                                <div class="grid gap-4 md:grid-cols-2">
                                    <flux:input 
                                        wire:model="agentic_shopping_assistant.product_recommendations.label" 
                                        label="Tool Label" 
                                    />

                                    <flux:input 
                                        type="number" 
                                        min="1" 
                                        max="12" 
                                        wire:model="agentic_shopping_assistant.product_recommendations.max_results" 
                                        label="Max Results"
                                        description="Maximum number of products to show (1-12)"
                                    />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <flux:field>
                                        <flux:label>Card Template</flux:label>
                                        <flux:select wire:model="agentic_shopping_assistant.product_recommendations.card_template">
                                            <flux:select.option value="simple">Simple Card (Name, Image, Link)</flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <flux:textarea 
                                    wire:model="agentic_shopping_assistant.product_recommendations.trigger_keywords" 
                                    rows="2"
                                    label="Trigger Keywords"
                                    description="Comma-separated keywords that trigger this tool"
                                />

                                <flux:input 
                                    wire:model="agentic_shopping_assistant.product_recommendations.confirmation_message" 
                                    label="Confirmation Message"
                                    description="Message shown when this tool is triggered"
                                />

                                <flux:textarea 
                                    wire:model="agentic_shopping_assistant.product_recommendations.rules" 
                                    rows="3"
                                    label="AI Processing Rules"
                                    description="Instructions for the AI on how to use this tool"
                                />
                            @endif
                        </div>
                    </flux:card>

                    <!-- How It Works -->
                    <flux:card>
                        <flux:heading size="md">How Shopping Assistant Works</flux:heading>
                        <div class="mt-4 space-y-3 text-sm text-gray-600">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                </div>
                                <div>
                                    <strong>Product Details:</strong> When customers ask about specific products ("Tell me about iPhone 15", "Show me details about this laptop"), the AI will display a detailed product card with complete information, images, pricing, and specifications.
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                </div>
                                <div>
                                    <strong>Product Recommendations:</strong> When customers are browsing or looking for suggestions ("Show me phones", "I need a laptop under $1000", "What tablets do you recommend"), the AI will display multiple product cards with essential information and direct links.
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                </div>
                                <div>
                                    <strong>Visual Shopping:</strong> All product information is displayed as interactive HTML cards directly in the chat interface, making it easy for customers to browse and make purchasing decisions.
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <flux:button wire:click="resetToDefaults" variant="ghost">
                        Reset to Defaults
                    </flux:button>
                    <flux:button wire:click="save" variant="primary">
                        Save Configuration
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</div> 