<flux:modal class="md:w-96" name="recharge-tokens">
    <div class="space-y-6">
        <div>
            <flux:heading>Recharge AI Credits</flux:heading>
            <flux:subheading>
                Purchase more AI Tokens for your Chatbots usage. Tokens are added instantly to your account.
            </flux:subheading>
        </div>

        <flux:separator text="Choose a Package" />

        <form wire:submit="store" class="space-y-4">
            <flux:radio.group class="flex-col space-y-3" wire:model="token_package" variant="cards">
                <flux:radio class="w-full" value="recharge-50">
                    <div class="flex w-full items-start justify-between">
                        <div>
                            <h3 class="font-medium">50M AI Tokens</h3>
                            <p class="mt-1 text-sm text-slate-500">+ 2M Context Limit</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold">$27</span>
                        </div>
                    </div>
                </flux:radio>

                <flux:radio class="w-full" value="recharge-250">
                    <div class="flex w-full items-start justify-between">
                        <div>
                            <h3 class="font-medium">250M AI Tokens</h3>
                            <p class="mt-1 text-sm text-slate-500">+ 5M Context Limit</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold">$108</span>
                            <p class="text-xs text-green-600">Save 20%</p>
                        </div>
                    </div>
                </flux:radio>

                <flux:radio checked class="w-full" value="recharge-500">
                    <div class="flex w-full items-start justify-between">
                        <div>
                            <h3 class="font-medium">500M AI Tokens</h3>
                            <p class="mt-1 text-sm text-slate-500">+ 10M Context Limit</p>
                            <span class="mt-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">Best Value</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold">$162</span>
                            <p class="text-xs text-green-600">Save 40%</p>
                        </div>
                    </div>
                </flux:radio>
            </flux:radio.group>

            <div class="pt-4">
                <flux:button type="submit" class="w-full" icon="credit-card" variant="primary">
                    Proceed to Payment
                </flux:button>
                <p class="mt-2 text-center text-xs text-slate-500">Secure payment processing. Tokens are added instantly.</p>
            </div>
        </form>
    </div>
</flux:modal>