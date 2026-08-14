<x-mail::message>
# Your Service Booking Request

Hi {{ $booking->name }},

Thank you for contacting NorthStar Yamaha. Please find our response to your service booking request below.

---

{{ $booking->staff_reply }}

---

@if($booking->alt_date_1 || $booking->alt_date_2 || $booking->alt_date_3)
## Alternative Dates Available

We can offer you the following alternative appointment dates:

@if($booking->alt_date_1)
- {{ $booking->alt_date_1->format('l, d M Y') }}
@endif
@if($booking->alt_date_2)
- {{ $booking->alt_date_2->format('l, d M Y') }}
@endif
@if($booking->alt_date_3)
- {{ $booking->alt_date_3->format('l, d M Y') }}
@endif

Please reply to this email or call us to confirm which date suits you best.

@endif

---

## Your Booking Summary

**Customer Details**

| | |
|---|---|
| **Name** | {{ $booking->name }} |
| **Phone** | {{ $booking->phone }} |
| **Email** | {{ $booking->email }} |

**Vehicle**

| | |
|---|---|
| **Year** | {{ $booking->vehicle_year }} |
| **Make / Model** | {{ $booking->vehicle_model }} |
| **Registration** | {{ $booking->rego ?: '—' }} |
| **Odometer** | {{ $booking->odometer ? number_format($booking->odometer) . ' km' : '—' }} |

**Service Request**

| | |
|---|---|
| **Service Type** | {{ $booking->service_type }} |
| **Requested Date** | {{ $booking->preferred_date->format('l, d M Y') }} |
| **Requested Time** | {{ $booking->preferred_time }} |

@if($booking->notes)
**Your Notes**

{{ $booking->notes }}
@endif

---

If you have any questions, please don't hesitate to contact us.

**Phone:** (07) 3266 8000
**Email:** service@northstarmotorcycles.com.au

<x-mail::button url="mailto:service@northstarmotorcycles.com.au">
Reply to NorthStar Yamaha
</x-mail::button>

Thanks,
NorthStar Yamaha Service Department
</x-mail::message>
