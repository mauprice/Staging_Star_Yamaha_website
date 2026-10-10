<?php

namespace App\Http\Controllers;

use App\Mail\ProductEnquiryMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EnquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validated manually (rather than $request->validate()) because this
        // app's exception handler only renders JSON for /api/* routes - a
        // thrown ValidationException here would redirect instead of
        // returning the 422 JSON this endpoint's JS caller expects.
        $validator = Validator::make($request->all(), [
            'subject'  => 'required|string|max:150',
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150',
            'phone'    => 'nullable|string|max:30',
            'message'  => 'nullable|string|max:1500',
            'page_url' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // validated() omits optional fields the caller didn't submit at all,
        // but the mail view expects every key to exist.
        $data = array_merge(
            ['phone' => null, 'message' => null, 'page_url' => null],
            $validator->validated()
        );

        Mail::to(env('BOOKING_EMAIL', 'admin@iwcdigital.com'))
            ->send(new ProductEnquiryMail($data));

        return response()->json([
            'message' => "Thanks {$data['name']}! We've received your enquiry and will be in touch shortly.",
        ]);
    }
}
