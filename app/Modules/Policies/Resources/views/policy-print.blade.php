<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $policy->title }} — v{{ $policy->version }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; max-width: 900px; margin: 0 auto; font-size: 14px; }
        .policy-meta { color: #6c757d; margin-bottom: 1.5rem; }
        .policy-content { line-height: 1.7; }
        .policy-content h3 { margin-top: 1.5rem; }
        .policy-content ul, .policy-content ol { padding-left: 1.5rem; }
        .no-print { margin-bottom: 1rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print d-print-none mb-3">
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.close()">
            <i class="fas fa-times me-1"></i> Close
        </button>
    </div>

    <h2>{{ $policy->title }}</h2>
    <div class="policy-meta">
        <span class="badge bg-secondary text-uppercase me-2">{{ $policy->category }}</span>
        <span class="badge bg-info me-2">Version {{ $policy->version }}</span>
        @if ($policy->effective_date)
            <span class="me-2">Effective: {{ $policy->effective_date->format('M d, Y') }}</span>
        @endif
        @if ($policy->priority === 'critical' || $policy->priority === 'high')
            <span class="badge bg-danger text-uppercase">{{ $policy->priority }} Priority</span>
        @endif
    </div>

    @if ($policy->description)
        <div class="alert alert-light border mb-4">
            <strong>Summary:</strong> {{ $policy->description }}
        </div>
    @endif

    <div class="policy-content">
        @if ($policy->content)
            {!! $policy->content !!}
        @else
            <p class="text-muted text-center py-4">
                <i class="fas fa-file-pdf fa-3x mb-3 d-block"></i>
                Policy content is available as a PDF document.
            </p>
        @endif
    </div>

    <hr class="mt-4">
    <p class="text-muted small">
        This document is confidential and intended for internal use only.
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </p>
</body>
</html>