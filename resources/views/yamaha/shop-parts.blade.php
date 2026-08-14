@extends('yamaha.layout')

@section('title', 'Shop Parts & Accessories')
@section('meta_description', 'Browse and order genuine Yamaha parts, accessories, road gear, lifestyle products and more.')

@section('content')
<div style="padding: 16px 0 0;">
    <div style="background:#fff7ed; border-top:2px solid #e85c24; border-bottom:2px solid #f0d9cc; padding:10px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <p style="margin:0; font-size:0.8rem; color:#92400e; font-weight:600;">
            <strong>Note:</strong> Due to browser security settings, your cart may not carry over when browsing here. To checkout, use the button to open the shop directly.
        </p>
        <a href="https://shop.northstaryamaha.com.au" target="_blank" rel="noopener"
           style="background:#e85c24; color:#fff; font-weight:800; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; padding:8px 16px; border-radius:6px; text-decoration:none; white-space:nowrap; flex-shrink:0;">
            Open Shop &amp; Checkout →
        </a>
    </div>
    <div class="parts-finder-wrapper" style="
        position: relative;
        overflow: hidden;
        height: calc(100vh - 80px - 32px - 53px);
        width: 100%;
    ">
        <iframe
            src="https://shop.northstaryamaha.com.au/cms/page/road-gear#content"
            title="Shop Yamaha Parts & Accessories"
            style="
                position: absolute;
                top: -185px;
                left: 0;
                width: 100%;
                height: calc(100% + 185px);
                border: none;
            "
            loading="eager"
            allowfullscreen
        ></iframe>
    </div>
</div>
@endsection
