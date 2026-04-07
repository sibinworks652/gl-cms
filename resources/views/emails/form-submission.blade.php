@component('emails.layout', ['subject' => 'New Enquiry Received'])
    <h2 style="margin:0 0 12px; font-size:22px; color:#0f172a;">New Enquiry from {{ $form->name }}</h2>

    <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#475569;">
        A new form submission has been received from the website.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0 0 20px;">
        <tr>
            <td style="padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; font-size:14px; width:180px;"><strong>Form</strong></td>
            <td style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; font-size:14px;">{{ $form->name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; font-size:14px;"><strong>Submitted At</strong></td>
            <td style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; font-size:14px;">{{ optional($submission->submitted_at)->format('d M Y h:i A') ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; font-size:14px;"><strong>IP Address</strong></td>
            <td style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; font-size:14px;">{{ $submission->ip_address ?: '-' }}</td>
        </tr>
    </table>

    <h3 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Submitted Details</h3>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        @foreach(($submission->payload ?? []) as $item)
            @php($value = $item['value'] ?? null)
            <tr>
                <td style="padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; font-size:14px; width:180px; vertical-align:top;">
                    <strong>{{ $item['label'] ?? ($item['name'] ?? '-') }}</strong>
                </td>
                <td style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; font-size:14px; vertical-align:top;">
                    @if(is_array($value))
                        {{ implode(', ', $value) ?: '-' }}
                    @else
                        {{ $value !== null && $value !== '' ? $value : '-' }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
@endcomponent
