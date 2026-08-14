<x-mail::message>
# Sell My Bike Enquiry

A customer would like a valuation for their vehicle.

---

## Customer Details

| | |
|---|---|
| **Name** | {{ $data['name'] }} |
| **Phone** | {{ $data['phone'] }} |
| **Email** | {{ $data['email'] }} |
| **Suburb** | {{ $data['suburb'] }} |

## Vehicle

| | |
|---|---|
| **Year** | {{ $data['year'] }} |
| **Make** | {{ $data['make'] }} |
| **Model** | {{ $data['model'] }} |
| **Odometer** | {{ $data['kms'] ? number_format($data['kms']) . ' km' : '—' }} |
| **Asking Price** | {{ $data['asking_price'] ? '$' . number_format($data['asking_price']) : 'Open to offers' }} |
| **Condition** | {{ $data['condition'] }} |

@if($data['message'])
## Additional Information

{{ $data['message'] }}
@endif

<x-mail::button :url="'mailto:' . $data['email']">
Reply to {{ $data['name'] }}
</x-mail::button>

Thanks,
NorthStar Yamaha Website
</x-mail::message>
