@extends('yamaha.layout')

@section('title', 'Parts Finder')
@section('meta_description', 'Search and order genuine Yamaha OEM parts and accessories for your bike, ATV, watercraft or outboard.')

@section('content')
<div style="padding: 16px 0;">
<div class="parts-finder-wrapper" style="
    position: relative;
    overflow: hidden;
    height: calc(100vh - 80px - 32px);
    width: 100%;
">
    <iframe
        src="https://shop.staryamaha.com.au/partFinder/fiche/yamaha"
        title="Yamaha Parts Finder"
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
