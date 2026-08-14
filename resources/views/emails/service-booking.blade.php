<x-mail::message>
# New Service Booking Request

A customer has submitted a service booking via the NorthStar Yamaha website.

---

## Customer Details

| | |
|---|---|
| **Name** | {{ $booking->name }} |
| **Phone** | {{ $booking->phone }} |
| **Email** | {{ $booking->email }} |

## Vehicle

| | |
|---|---|
| **Year** | {{ $booking->vehicle_year }} |
| **Make / Model** | {{ $booking->vehicle_model }} |
| **Registration** | {{ $booking->rego ?: '—' }} |
| **Odometer** | {{ $booking->odometer ? $booking->odometer . ' km' : '—' }} |

## Service Request

| | |
|---|---|
| **Service Type** | {{ $booking->service_type }} |
| **Preferred Date** | {{ $booking->preferred_date->format('d M Y') }} |
| **Preferred Time** | {{ $booking->preferred_time }} |

@if($booking->notes)
## Notes

{{ $booking->notes }}
@endif

<x-mail::button :url="config('app.url') . '/service/service-bookings/' . $booking->id . '/edit'">
Open Booking & Send Reply
</x-mail::button>

Thanks,
NorthStar Yamaha Website
</x-mail::message>
