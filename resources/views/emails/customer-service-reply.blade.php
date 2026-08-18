<x-mail::message>
# New Reply from {{ $booking->name }}

A customer has replied to their service booking through the website reply form.

---

{{ $booking->customer_reply }}

---

## Booking Reference

| | |
|---|---|
| **Name** | {{ $booking->name }} |
| **Phone** | {{ $booking->phone }} |
| **Email** | {{ $booking->email }} |
| **Vehicle** | {{ $booking->vehicle_year }} {{ $booking->vehicle_model }} |
| **Service Type** | {{ $booking->service_type }} |
| **Requested Date** | {{ $booking->preferred_date->format('l, d M Y') }} |

You can reply directly to this email to respond to {{ $booking->name }}, or manage the booking in the service portal.

<x-mail::button :url="$adminUrl">
View Booking
</x-mail::button>

Thanks,
Star Yamaha Website
</x-mail::message>
