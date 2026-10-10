<x-mail::message>
# Website Enquiry

A customer has an enquiry about **{{ $data['subject'] }}**.

---

## Customer Details

| | |
|---|---|
| **Name** | {{ $data['name'] }} |
| **Phone** | {{ $data['phone'] ?? '—' }} |
| **Email** | {{ $data['email'] }} |

## Message

{{ $data['message'] ?? '(No message provided)' }}

<x-mail::button :url="'mailto:' . $data['email']">
Reply to {{ $data['name'] }}
</x-mail::button>

Sent from: {{ $data['page_url'] ?? 'unknown page' }}
</x-mail::message>
