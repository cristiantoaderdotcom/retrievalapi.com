<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;

class ShoppingAssistant extends Component
{
    #[Locked]
    public Workspace $workspace;

    public string $tab = 'configuration';

    public array $agentic_shopping_assistant = [
        'enabled' => true,
        'product_details' => [
            'enabled' => true,
            'label' => 'Product Details',
            'trigger_keywords' => 'tell me about, show me, details about, information about, what is, describe, specs, specifications, features of, price of, cost of, price for, how much is, how much does, what does, cost for, i want to know',
            'confirmation_message' => 'I\'ll get the detailed information about that product for you.',
            'rules' => 'Use when users ask for specific details about a particular product. Extract the product name or identifier from the user\'s query and provide comprehensive product information.',
            'card_template' => 'detailed' // detailed card with all info
        ],
        'product_recommendations' => [
            'enabled' => true,
            'label' => 'Product Recommendations',
            'trigger_keywords' => 'recommend, suggest, looking for, need, want, find me, show me products, best, top, similar, like, alternatives, options, what do you have',
            'confirmation_message' => 'I\'ll find some great product recommendations for you based on your preferences.',
            'rules' => 'Use when users ask for product recommendations, want to browse products, or describe what they\'re looking for without specifying a particular product.',
            'card_template' => 'simple', // simple card with name, image, link
            'max_results' => 6
        ]
    ];

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->agentic_shopping_assistant = $this->workspace->setting('agentic_shopping_assistant', $this->agentic_shopping_assistant);
    }

    public function save()
    {
        // Validate the configuration
        $this->validateConfiguration();

        $this->workspace->settings()->updateOrCreate(
            ['key' => 'agentic_shopping_assistant'],
            ['value' => $this->agentic_shopping_assistant]
        );

        Flux::toast(variant: 'success', text: 'Shopping Assistant configuration updated successfully');
    }

    public function toggleTool()
    {
        $this->agentic_shopping_assistant['enabled'] = !$this->agentic_shopping_assistant['enabled'];
    }

    public function toggleProductDetails()
    {
        $this->agentic_shopping_assistant['product_details']['enabled'] = !$this->agentic_shopping_assistant['product_details']['enabled'];
    }

    public function toggleProductRecommendations()
    {
        $this->agentic_shopping_assistant['product_recommendations']['enabled'] = !$this->agentic_shopping_assistant['product_recommendations']['enabled'];
    }

    public function resetToDefaults()
    {
        $this->agentic_shopping_assistant = [
            'enabled' => true,
            'product_details' => [
                'enabled' => true,
                'label' => 'Product Details',
                'trigger_keywords' => 'tell me about, show me, details about, information about, what is, describe, specs, specifications, features of, price of, cost of, price for, how much is, how much does, what does, cost for, i want to know',
                'confirmation_message' => 'I\'ll get the detailed information about that product for you.',
                'rules' => 'Use when users ask for specific details about a particular product. Extract the product name or identifier from the user\'s query and provide comprehensive product information.',
                'card_template' => 'detailed'
            ],
            'product_recommendations' => [
                'enabled' => true,
                'label' => 'Product Recommendations',
                'trigger_keywords' => 'recommend, suggest, looking for, need, want, find me, show me products, best, top, similar, like, alternatives, options, what do you have',
                'confirmation_message' => 'I\'ll find some great product recommendations for you based on your preferences.',
                'rules' => 'Use when users ask for product recommendations, want to browse products, or describe what they\'re looking for without specifying a particular product.',
                'card_template' => 'simple',
                'max_results' => 6
            ]
        ];

        Flux::toast(variant: 'info', text: 'Configuration reset to defaults');
    }

    private function validateConfiguration()
    {
        // Ensure at least one tool is enabled
        if (!$this->agentic_shopping_assistant['product_details']['enabled'] && 
            !$this->agentic_shopping_assistant['product_recommendations']['enabled']) {
            throw new \Exception("At least one shopping assistant tool must be enabled");
        }

        // Validate product details configuration
        if ($this->agentic_shopping_assistant['product_details']['enabled']) {
            $details = $this->agentic_shopping_assistant['product_details'];
            if (empty($details['trigger_keywords'])) {
                throw new \Exception("Trigger keywords are required for Product Details tool");
            }
            if (empty($details['confirmation_message'])) {
                throw new \Exception("Confirmation message is required for Product Details tool");
            }
        }

        // Validate product recommendations configuration
        if ($this->agentic_shopping_assistant['product_recommendations']['enabled']) {
            $recommendations = $this->agentic_shopping_assistant['product_recommendations'];
            if (empty($recommendations['trigger_keywords'])) {
                throw new \Exception("Trigger keywords are required for Product Recommendations tool");
            }
            if (empty($recommendations['confirmation_message'])) {
                throw new \Exception("Confirmation message is required for Product Recommendations tool");
            }
            if (!is_numeric($recommendations['max_results']) || $recommendations['max_results'] < 1) {
                throw new \Exception("Max results must be a positive number");
            }
        }
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.shopping-assistant')
            ->extends('layouts.app')
            ->section('main');
    }
} 