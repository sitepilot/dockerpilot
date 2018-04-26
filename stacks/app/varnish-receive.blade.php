if (req.http.x-backend ~ "(?i)^(www.)?{{ $app['name'] }}$") {
    set req.backend_hint = {{ $app['name'] }};
}