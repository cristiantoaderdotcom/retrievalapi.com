<div class="container @container mx-auto flex flex-col space-y-6">
	<div class="grid grid-cols-1 gap-6 @2xl:grid-cols-2 @4xl:grid-cols-4">

		<flux:card>
			<flux:subheading class="flex items-center gap-2">
				<flux:icon class="size-4" name="inbox-stack" />
				Email Inboxes
			</flux:subheading>
			<flux:heading size="xl">{{ data_get($this->stats, 'inboxes') }}</flux:heading>
		</flux:card>

		<flux:card>
			<flux:subheading class="flex items-center gap-2">
				<flux:icon class="size-4" name="circle-stack" />
				AI Tokens
			</flux:subheading>
			<flux:heading size="xl">{{ data_get($this->stats, 'ai_tokens') }}</flux:heading>
		</flux:card>

		<flux:card>
			<flux:subheading class="flex items-center gap-2">
				<flux:icon class="size-4" name="document-text" />
				AI Context Limit
			</flux:subheading>
			<flux:heading size="xl">{{ data_get($this->stats, 'ai_context_limit') }}</flux:heading>
		</flux:card>

		<flux:spacer />

		<flux:card class="@2xl:col-span-2 @4xl:col-span-4 space-y-6">
			<flux:subheading class="flex items-center gap-2">
				<flux:icon class="size-4" name="chart-bar" />
				Email Analytics
			</flux:subheading>

			<div class="flex flex-wrap items-center gap-2">
				<flux:card class="!px-4 !py-2">
					<flux:heading size="xl">{{ data_get($this->stats, 'total_emails') }}</flux:heading>
					<flux:subheading>
						Total Emails
					</flux:subheading>
				</flux:card>

				<flux:separator class="mx-4 hidden @2xl:block" orientation="vertical" variant="subtle" />

				<flux:card class="!px-4 !py-2">
					<flux:heading size="xl">{{ data_get($this->stats, 'total_replies') }}</flux:heading>
					<flux:subheading>
						Total Replies
					</flux:subheading>
				</flux:card>

				<flux:card class="relative !px-4 !py-2">
					<flux:tooltip class="absolute right-1 top-1">
						<flux:button icon="information-circle" size="xs" variant="ghost" />
						<flux:tooltip.content class="max-w-[20rem] space-y-2">
							<p>Reply Rate is the percentage of emails that received an AI response.</p>
						</flux:tooltip.content>
					</flux:tooltip>

					<flux:heading size="xl">{{ data_get($this->stats, 'reply_rate') }}%</flux:heading>
					<flux:subheading>
						Reply Rate
					</flux:subheading>
				</flux:card>
			</div>

			<flux:chart :value="$this->data">
				<flux:chart.viewport class="min-h-[15rem]">
					<flux:chart.svg>
						<flux:chart.line class="text-zinc-500" curve="none" field="emails" />
						<flux:chart.line class="text-blue-500" curve="none" field="replies" />

						<flux:chart.axis axis="x" field="date">
							<flux:chart.axis.tick />
							<flux:chart.axis.line />
						</flux:chart.axis>

						<flux:chart.axis axis="y">
							<flux:chart.axis.grid />
							<flux:chart.axis.tick />
						</flux:chart.axis>
					</flux:chart.svg>

					<flux:chart.tooltip>
						<flux:chart.tooltip.heading :format="['year' => 'numeric', 'month' => 'numeric', 'day' => 'numeric']" field="date" />
						<flux:chart.tooltip.value field="emails" label="Emails" />
						<flux:chart.tooltip.value field="replies" label="Replies" />
					</flux:chart.tooltip>
				</flux:chart.viewport>

				<div class="flex justify-center gap-4 pt-4">
					<flux:chart.legend label="Emails">
						<flux:chart.legend.indicator class="bg-zinc-400" />
					</flux:chart.legend>
					<flux:chart.legend label="Replies">
						<flux:chart.legend.indicator class="bg-blue-400" />
					</flux:chart.legend>
				</div>
			</flux:chart>
		</flux:card>
	</div>
</div>
