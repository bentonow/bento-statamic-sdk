@if(config('bento.enabled') && config('bento.inject_js') && isset($bento_site_uuid))
    <script src="https://fast.bentonow.com?site_uuid={{ $bento_site_uuid }}" async defer></script>
@endif
