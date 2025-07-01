<?php

namespace App\Livewire\App\Platforms;

use Livewire\Component;
use App\Models\Workspace;
use Flux\Flux;
use Livewire\Attributes\Locked;

class Website extends Component
{

    #[Locked]
    public Workspace $workspace;

    public string $tab = 'integration';

    public array $platform_website = [
        'welcome_message' => 'Hello! How can I help you today?',
		'fallback_message' => 'I am sorry, I do not have the answer to that question. Please try asking me something else.',
		'suggested_messages' => 'Whats the prices?',
		'message_placeholder' => 'Type your message here...',
		'remove_iframe_branding' => false,
        'user_recognition' => true,
        'conversation_continuity' => true,
        'send_on_enter' => true,
        'reset_button' => true,
    ];

    public array $styling = [
        'theme' => 'default',
		'font_family' => 'system-ui',
		'font_size' => '16px',
    ];

    public array $themePresets = [
		'default' => [
			'name' => 'Default',
			'colors' => [
				'primary' => '#4f46e5',
				'secondary' => '#6b7280',
				'background' => '#ffffff',
				'text' => '#333333',
				'chat_bubble_user' => '#f1f5f9',
				'chat_bubble_assistant' => '#eff6ff',
				'chat_text_user' => '#1e293b',
				'chat_text_assistant' => '#1e293b',
			],
		],
		'dark' => [
			'name' => 'Dark Mode',
			'colors' => [
				'primary' => '#8b5cf6',
				'secondary' => '#94a3b8',
				'background' => '#1e293b',
				'text' => '#e2e8f0',
				'chat_bubble_user' => '#334155',
				'chat_bubble_assistant' => '#312e81',
				'chat_text_user' => '#e2e8f0',
				'chat_text_assistant' => '#e2e8f0',
			],
		],
		'light_blue' => [
			'name' => 'Light Blue',
			'colors' => [
				'primary' => '#0ea5e9',
				'secondary' => '#64748b',
				'background' => '#f0f9ff',
				'text' => '#0f172a',
				'chat_bubble_user' => '#e0f2fe',
				'chat_bubble_assistant' => '#bfdbfe',
				'chat_text_user' => '#0f172a',
				'chat_text_assistant' => '#0f172a',
			],
		],
		'gradient_purple' => [
			'name' => 'Gradient Purple',
			'colors' => [
				'primary' => '#7c3aed',
				'secondary' => '#64748b',
				'background' => '#ffffff',
				'text' => '#1e293b',
				'chat_bubble_user' => '#f5f3ff',
				'chat_bubble_assistant' => '#ddd6fe',
				'chat_text_user' => '#1e293b',
				'chat_text_assistant' => '#1e293b',
			],
		],
		'warm' => [
			'name' => 'Warm',
			'colors' => [
				'primary' => '#f59e0b',
				'secondary' => '#78716c',
				'background' => '#fffbeb',
				'text' => '#292524',
				'chat_bubble_user' => '#fef3c7',
				'chat_bubble_assistant' => '#fed7aa',
				'chat_text_user' => '#292524',
				'chat_text_assistant' => '#292524',
			],
		],
		'emerald' => [
			'name' => 'Emerald',
			'colors' => [
				'primary' => '#10b981',
				'secondary' => '#6b7280',
				'background' => '#ecfdf5',
				'text' => '#064e3b',
				'chat_bubble_user' => '#d1fae5',
				'chat_bubble_assistant' => '#a7f3d0',
				'chat_text_user' => '#064e3b',
				'chat_text_assistant' => '#064e3b',
			],
		],
		'rose' => [
			'name' => 'Rose',
			'colors' => [
				'primary' => '#e11d48',
				'secondary' => '#6b7280',
				'background' => '#fff1f2',
				'text' => '#881337',
				'chat_bubble_user' => '#ffe4e6',
				'chat_bubble_assistant' => '#fecdd3',
				'chat_text_user' => '#881337',
				'chat_text_assistant' => '#881337',
			],
		],
		'amber' => [
			'name' => 'Amber',
			'colors' => [
				'primary' => '#d97706',
				'secondary' => '#6b7280',
				'background' => '#fffbeb',
				'text' => '#78350f',
				'chat_bubble_user' => '#fef3c7',
				'chat_bubble_assistant' => '#fde68a',
				'chat_text_user' => '#78350f',
				'chat_text_assistant' => '#78350f',
			],
		],
		'teal' => [
			'name' => 'Teal',
			'colors' => [
				'primary' => '#0d9488',
				'secondary' => '#6b7280',
				'background' => '#f0fdfa',
				'text' => '#134e4a',
				'chat_bubble_user' => '#ccfbf1',
				'chat_bubble_assistant' => '#99f6e4',
				'chat_text_user' => '#134e4a',
				'chat_text_assistant' => '#134e4a',
			],
		],
		'sky' => [
			'name' => 'Sky',
			'colors' => [
				'primary' => '#0284c7',
				'secondary' => '#6b7280',
				'background' => '#f0f9ff',
				'text' => '#0c4a6e',
				'chat_bubble_user' => '#e0f2fe',
				'chat_bubble_assistant' => '#bae6fd',
				'chat_text_user' => '#0c4a6e',
				'chat_text_assistant' => '#0c4a6e',
			],
		],
		'violet' => [
			'name' => 'Violet',
			'colors' => [
				'primary' => '#7c3aed',
				'secondary' => '#6b7280',
				'background' => '#f5f3ff',
				'text' => '#4c1d95',
				'chat_bubble_user' => '#ede9fe',
				'chat_bubble_assistant' => '#ddd6fe',
				'chat_text_user' => '#4c1d95',
				'chat_text_assistant' => '#4c1d95',
			],
		],
		'fuchsia' => [
			'name' => 'Fuchsia',
			'colors' => [
				'primary' => '#c026d3',
				'secondary' => '#6b7280',
				'background' => '#fdf4ff',
				'text' => '#701a75',
				'chat_bubble_user' => '#fae8ff',
				'chat_bubble_assistant' => '#f5d0fe',
				'chat_text_user' => '#701a75',
				'chat_text_assistant' => '#701a75',
			],
		],
		'lime' => [
			'name' => 'Lime',
			'colors' => [
				'primary' => '#65a30d',
				'secondary' => '#6b7280',
				'background' => '#f7fee7',
				'text' => '#3f6212',
				'chat_bubble_user' => '#ecfccb',
				'chat_bubble_assistant' => '#d9f99d',
				'chat_text_user' => '#3f6212',
				'chat_text_assistant' => '#3f6212',
			],
		],
		'slate' => [
			'name' => 'Slate',
			'colors' => [
				'primary' => '#475569',
				'secondary' => '#94a3b8',
				'background' => '#f8fafc',
				'text' => '#0f172a',
				'chat_bubble_user' => '#e2e8f0',
				'chat_bubble_assistant' => '#cbd5e1',
				'chat_text_user' => '#0f172a',
				'chat_text_assistant' => '#0f172a',
			],
		],
		'gray' => [
			'name' => 'Gray',
			'colors' => [
				'primary' => '#4b5563',
				'secondary' => '#9ca3af',
				'background' => '#f9fafb',
				'text' => '#111827',
				'chat_bubble_user' => '#f3f4f6',
				'chat_bubble_assistant' => '#e5e7eb',
				'chat_text_user' => '#111827',
				'chat_text_assistant' => '#111827',
			],
		],
		'neutral' => [
			'name' => 'Neutral',
			'colors' => [
				'primary' => '#525252',
				'secondary' => '#a3a3a3',
				'background' => '#fafafa',
				'text' => '#171717',
				'chat_bubble_user' => '#f5f5f5',
				'chat_bubble_assistant' => '#e5e5e5',
				'chat_text_user' => '#171717',
				'chat_text_assistant' => '#171717',
			],
		],
		'stone' => [
			'name' => 'Stone',
			'colors' => [
				'primary' => '#57534e',
				'secondary' => '#a8a29e',
				'background' => '#fafaf9',
				'text' => '#1c1917',
				'chat_bubble_user' => '#f5f5f4',
				'chat_bubble_assistant' => '#e7e5e4',
				'chat_text_user' => '#1c1917',
				'chat_text_assistant' => '#1c1917',
			],
		],
		'red' => [
			'name' => 'Red',
			'colors' => [
				'primary' => '#dc2626',
				'secondary' => '#6b7280',
				'background' => '#fef2f2',
				'text' => '#7f1d1d',
				'chat_bubble_user' => '#fee2e2',
				'chat_bubble_assistant' => '#fecaca',
				'chat_text_user' => '#7f1d1d',
				'chat_text_assistant' => '#7f1d1d',
			],
		],
		'orange' => [
			'name' => 'Orange',
			'colors' => [
				'primary' => '#ea580c',
				'secondary' => '#6b7280',
				'background' => '#fff7ed',
				'text' => '#7c2d12',
				'chat_bubble_user' => '#ffedd5',
				'chat_bubble_assistant' => '#fed7aa',
				'chat_text_user' => '#7c2d12',
				'chat_text_assistant' => '#7c2d12',
			],
		],
		'green' => [
			'name' => 'Green',
			'colors' => [
				'primary' => '#16a34a',
				'secondary' => '#6b7280',
				'background' => '#f0fdf4',
				'text' => '#14532d',
				'chat_bubble_user' => '#dcfce7',
				'chat_bubble_assistant' => '#bbf7d0',
				'chat_text_user' => '#14532d',
				'chat_text_assistant' => '#14532d',
			],
		],
		'cyan' => [
			'name' => 'Cyan',
			'colors' => [
				'primary' => '#06b6d4',
				'secondary' => '#6b7280',
				'background' => '#ecfeff',
				'text' => '#155e75',
				'chat_bubble_user' => '#cffafe',
				'chat_bubble_assistant' => '#a5f3fc',
				'chat_text_user' => '#155e75',
				'chat_text_assistant' => '#155e75',
			],
		],
		'blue' => [
			'name' => 'Blue',
			'colors' => [
				'primary' => '#2563eb',
				'secondary' => '#6b7280',
				'background' => '#eff6ff',
				'text' => '#1e3a8a',
				'chat_bubble_user' => '#dbeafe',
				'chat_bubble_assistant' => '#bfdbfe',
				'chat_text_user' => '#1e3a8a',
				'chat_text_assistant' => '#1e3a8a',
			],
		],
		'indigo' => [
			'name' => 'Indigo',
			'colors' => [
				'primary' => '#4f46e5',
				'secondary' => '#6b7280',
				'background' => '#eef2ff',
				'text' => '#312e81',
				'chat_bubble_user' => '#e0e7ff',
				'chat_bubble_assistant' => '#c7d2fe',
				'chat_text_user' => '#312e81',
				'chat_text_assistant' => '#312e81',
			],
		],
		'purple' => [
			'name' => 'Purple',
			'colors' => [
				'primary' => '#9333ea',
				'secondary' => '#6b7280',
				'background' => '#faf5ff',
				'text' => '#581c87',
				'chat_bubble_user' => '#f3e8ff',
				'chat_bubble_assistant' => '#e9d5ff',
				'chat_text_user' => '#581c87',
				'chat_text_assistant' => '#581c87',
			],
		],
		'pink' => [
			'name' => 'Pink',
			'colors' => [
				'primary' => '#db2777',
				'secondary' => '#6b7280',
				'background' => '#fdf2f8',
				'text' => '#831843',
				'chat_bubble_user' => '#fce7f3',
				'chat_bubble_assistant' => '#fbcfe8',
				'chat_text_user' => '#831843',
				'chat_text_assistant' => '#831843',
			],
		],
	];

    public array $fontFamilies = [
		'system-ui' => 'System Default',
		'Roboto' => 'Roboto',
		'Open+Sans' => 'Open Sans',
		'Lato' => 'Lato',
		'Montserrat' => 'Montserrat',
		'Poppins' => 'Poppins',
		'Raleway' => 'Raleway',
		'Nunito' => 'Nunito',
		'Inter' => 'Inter',
		'Oswald' => 'Oswald',
		'Source+Sans+Pro' => 'Source Sans Pro',
		'Ubuntu' => 'Ubuntu',
		'Playfair+Display' => 'Playfair Display',
		'Merriweather' => 'Merriweather',
		'PT+Sans' => 'PT Sans',
		'Roboto+Condensed' => 'Roboto Condensed',
		'Roboto+Slab' => 'Roboto Slab',
		'Quicksand' => 'Quicksand',
		'Work+Sans' => 'Work Sans',
		'Rubik' => 'Rubik',
		'Noto+Sans' => 'Noto Sans',
		'Fira+Sans' => 'Fira Sans',
		'Mukta' => 'Mukta',
		'Nunito+Sans' => 'Nunito Sans',
		'Barlow' => 'Barlow',
		'Josefin+Sans' => 'Josefin Sans',
		'Dosis' => 'Dosis',
		'Oxygen' => 'Oxygen',
		'Cabin' => 'Cabin',
		'Bitter' => 'Bitter',
		'Crimson+Text' => 'Crimson Text',
		'Libre+Franklin' => 'Libre Franklin',
		'Karla' => 'Karla',
		'DM+Sans' => 'DM Sans',
		'Manrope' => 'Manrope',
	];

    public function applyTheme($theme) {
		if (empty($this->themePresets[$theme])) {
			return;
		}

		data_set($this->styling, 'theme', $theme);
		data_set($this->styling, 'custom_colors', $this->themePresets[$theme]['colors']);
		
		$this->workspace->settings()->updateOrCreate(
			['key' => 'styling'],
			['value' => $this->styling]
		);

		Flux::toast(variant: 'success', text: 'Styling applied successfully');
	}

       public function getDefaultEmbedCode(): string {
        return '<iframe
    src="' . route('embed.website.index', $this->workspace->uuid) . '"
    width="100%"
    height="600px"
    frameborder="0"
    style="border: none;">
</iframe>';
    }

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->platform_website = $this->workspace->setting('platform_website', $this->platform_website);
        $this->styling = $this->workspace->setting('styling', $this->styling);
    }

    public function render()
    {
        return view('livewire.app.platforms.website')
            ->extends('layouts.app')
            ->section('main');
    }

    public function storeChatInterface()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'platform_website'],
            ['value' => $this->platform_website]
        );

        Flux::toast(variant: 'success', text: 'Chat settings updated successfully');
    }

    public function storeStyling()
    {
        $this->workspace->settings()->updateOrCreate(
            ['key' => 'styling'],
            ['value' => $this->styling]
        );

        Flux::toast(variant: 'success', text: 'Styling updated successfully');
    }
}
