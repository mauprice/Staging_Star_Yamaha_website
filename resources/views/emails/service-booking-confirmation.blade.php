<x-mail::message>
# Thanks, {{ $booking->name }}!

We've received your service booking request and a member of the Star Yamaha team will be in touch shortly to confirm your appointment.

---

## Your Request

| | |
|---|---|
| **Vehicle** | {{ $booking->vehicle_year }} {{ $booking->vehicle_model }} |
| **Service Type** | {{ $booking->service_type }} |
| **Preferred Date** | {{ $booking->preferred_date->format('d M Y') }} |
| **Preferred Time** | {{ $booking->preferred_time }} |

@if($booking->notes)
## Your Notes

{{ $booking->notes }}
@endif

If any of these details are incorrect, or you'd like to make changes, just reply to this email.

Thanks,
Star Yamaha
</x-mail::message>
