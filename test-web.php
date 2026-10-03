<?php
$user = App\Models\User::where('email', 'admin@studycenter.com')->first();
auth()->login($user);
$email = App\Models\CollectedEmail::first();

// we create a proper HTTP request passing the middleware
$request = Illuminate\Http\Request::create(
    '/admin/collected-emails/' . $email->id . '/toggle-invite',
    'POST'
);

$app = app();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Skip CSRF validation for this test
$app->bind(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, function () {
    return new class {
        public function handle($request, $next) { return $next($request); }
    };
});

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
