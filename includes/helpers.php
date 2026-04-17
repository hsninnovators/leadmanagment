<?php
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function badge_class(string $type, string $value): string
{
    $maps = [
        'priority' => ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'secondary'],
        'task' => ['Pending' => 'secondary', 'In Progress' => 'warning', 'Done' => 'success', 'Cancelled' => 'dark'],
        'proposal' => ['Draft' => 'secondary', 'Sent' => 'primary', 'Under Review' => 'warning', 'Accepted' => 'success', 'Rejected' => 'danger'],
    ];
    if ($type === 'stage') {
        $stageColor = [
            'New Lead' => 'secondary', 'Contacted' => 'info', 'Replied' => 'primary', 'Interested' => 'success',
            'Meeting / Discussion' => 'warning', 'Proposal Sent' => 'info', 'Follow-up' => 'warning',
            'Closed Won' => 'success', 'Not Interested' => 'dark', 'Closed Lost' => 'danger',
        ];
        return $stageColor[$value] ?? 'secondary';
    }
    if ($type === 'source') {
        return [
            'Google Maps' => 'danger', 'Facebook' => 'primary', 'Instagram' => 'warning', 'LinkedIn' => 'info',
            'WhatsApp' => 'success', 'Email' => 'secondary', 'Website' => 'dark', 'Referral' => 'success', 'Other' => 'light'
        ][$value] ?? 'secondary';
    }
    return $maps[$type][$value] ?? 'secondary';
}
