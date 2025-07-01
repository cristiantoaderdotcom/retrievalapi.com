<?php

namespace App\Livewire\App\AgenticTools;

use App\Models\Workspace;
use App\Models\Report;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    #[Locked]
    public Workspace $workspace;

    public string $tab = 'configuration';

    // Report management
    public $selectedReport = null;
    public $showReportModal = false;
    public $reportNotes = '';
    public $reportStatus = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';

    public array $agentic_reports = [
        'enabled' => true,
        'types' => [
            'bug_report' => [
                'enabled' => true,
                'label' => 'Bug Report',
                'trigger_keywords' => 'bug, error, not working, broken, problem, issue, glitch',
                'confirmation_message' => 'I understand you want to report a bug. Could you please describe the issue you\'re experiencing in detail? This will help our development team fix it quickly.',
                'rules' => 'Collect detailed information about the bug including steps to reproduce, expected behavior, and actual behavior. Ask for browser/device information if relevant.'
            ],
            'abuse_report' => [
                'enabled' => true,
                'label' => 'Abuse Report',
                'trigger_keywords' => 'abuse, harassment, inappropriate, offensive, report user, violation',
                'confirmation_message' => 'I understand you want to report inappropriate behavior or content. Please provide details about what happened so our moderation team can investigate.',
                'rules' => 'Handle with sensitivity. Collect specific details about the incident, user involved, and evidence if available. Maintain confidentiality.'
            ],
            'security_issue' => [
                'enabled' => true,
                'label' => 'Security Issue',
                'trigger_keywords' => 'security, vulnerability, hack, breach, suspicious activity, unauthorized access',
                'confirmation_message' => 'I understand you want to report a security concern. Please describe the security issue you\'ve identified so our security team can investigate immediately.',
                'rules' => 'Treat as high priority. Collect detailed technical information about the security concern. Escalate immediately to security team.'
            ]
        ]
    ];

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->with('settings')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $this->agentic_reports = $this->workspace->setting('agentic_reports', $this->agentic_reports);
    }

    public function save()
    {
        // Validate the configuration
        $this->validateConfiguration();

        $this->workspace->settings()->updateOrCreate(
            ['key' => 'agentic_reports'],
            ['value' => $this->agentic_reports]
        );

        Flux::toast(variant: 'success', text: 'Report configuration updated successfully');
    }

    public function addReportType()
    {
        $typeName = 'custom_report_' . time();
        $this->agentic_reports['types'][$typeName] = [
            'enabled' => true,
            'label' => 'New Report Type',
            'trigger_keywords' => 'custom, report',
            'confirmation_message' => 'I understand you want to submit a report. Please provide details about your concern.',
            'rules' => 'Collect relevant information about the reported issue and forward to appropriate team.'
        ];
    }

    public function removeReportType($typeName)
    {
        unset($this->agentic_reports['types'][$typeName]);
    }

    public function toggleTool()
    {
        $this->agentic_reports['enabled'] = !$this->agentic_reports['enabled'];
    }

    public function resetToDefaults()
    {
        $this->agentic_reports = [
            'enabled' => true,
            'types' => [
                'bug_report' => [
                    'enabled' => true,
                    'label' => 'Bug Report',
                    'trigger_keywords' => 'bug, error, not working, broken, problem, issue, glitch',
                    'confirmation_message' => 'I understand you want to report a bug. Could you please describe the issue you\'re experiencing in detail? This will help our development team fix it quickly.',
                    'rules' => 'Collect detailed information about the bug including steps to reproduce, expected behavior, and actual behavior. Ask for browser/device information if relevant.'
                ],
                'abuse_report' => [
                    'enabled' => true,
                    'label' => 'Abuse Report',
                    'trigger_keywords' => 'abuse, harassment, inappropriate, offensive, report user, violation',
                    'confirmation_message' => 'I understand you want to report inappropriate behavior or content. Please provide details about what happened so our moderation team can investigate.',
                    'rules' => 'Handle with sensitivity. Collect specific details about the incident, user involved, and evidence if available. Maintain confidentiality.'
                ],
                'security_issue' => [
                    'enabled' => true,
                    'label' => 'Security Issue',
                    'trigger_keywords' => 'security, vulnerability, hack, breach, suspicious activity, unauthorized access',
                    'confirmation_message' => 'I understand you want to report a security concern. Please describe the security issue you\'ve identified so our security team can investigate immediately.',
                    'rules' => 'Treat as high priority. Collect detailed technical information about the security concern. Escalate immediately to security team.'
                ]
            ]
        ];

        Flux::toast(variant: 'info', text: 'Configuration reset to defaults');
    }

    // Report management methods
    public function getReportsProperty()
    {
        $query = Report::query()
            ->whereHas('conversation', function ($q) {
                $q->where('workspace_id', $this->workspace->id);
            })
            ->with('conversation')
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter !== 'all') {
            $query->where('report_type', $this->typeFilter);
        }

        return $query->paginate(10);
    }

    public function getReportTypesForFilterProperty()
    {
        return Report::query()
            ->whereHas('conversation', function ($q) {
                $q->where('workspace_id', $this->workspace->id);
            })
            ->distinct()
            ->pluck('report_type')
            ->mapWithKeys(function ($type) {
                return [$type => ucwords(str_replace('_', ' ', $type))];
            })
            ->toArray();
    }

    public function viewReport($reportId)
    {
        $this->selectedReport = Report::with('conversation')->findOrFail($reportId);
        $this->reportNotes = $this->selectedReport->notes ?? '';
        $this->reportStatus = $this->selectedReport->status;
        $this->showReportModal = true;
    }

    public function updateReportStatus()
    {
        if (!$this->selectedReport) {
            return;
        }

        $this->selectedReport->update([
            'status' => $this->reportStatus,
            'notes' => $this->reportNotes,
            'processed_at' => now(),
        ]);

        $this->showReportModal = false;
        $this->selectedReport = null;
        $this->resetPage();

        Flux::toast(variant: 'success', text: 'Report updated successfully');
    }

    public function closeModal()
    {
        $this->showReportModal = false;
        $this->selectedReport = null;
        $this->reportNotes = '';
        $this->reportStatus = '';
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    private function validateConfiguration()
    {
        // Ensure at least one report type exists
        if (empty($this->agentic_reports['types'])) {
            throw new \Exception("At least one report type is required");
        }

        // Validate each report type
        foreach ($this->agentic_reports['types'] as $typeName => $type) {
            if (empty($type['label'])) {
                throw new \Exception("Label is required for report type '{$typeName}'");
            }
            if (empty($type['trigger_keywords'])) {
                throw new \Exception("Trigger keywords are required for report type '{$typeName}'");
            }
            if (empty($type['confirmation_message'])) {
                throw new \Exception("Confirmation message is required for report type '{$typeName}'");
            }
        }
    }

    public function render()
    {
        return view('livewire.app.agentic-tools.reports')
            ->extends('layouts.app')
            ->section('main');
    }
} 