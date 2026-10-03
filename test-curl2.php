<?php
// Simulate web request by calling through artisan Tinker
$user = App\Models\User::where('email', 'admin@studycenter.com')->first();
auth()->login($user);
$email = App\Models\CollectedEmail::first();

// Send POST to controller method directly without HTTP
$request = Illuminate\Http\Request::create(
    '/admin/collected-emails/' . $email->id . '/toggle-invite',
    'POST'
);

$controller = app()->make(\App\Http\Controllers\CollectedEmailController::class);
try {
    $response = $controller->toggleInvite($request, $email);
    echo "Response:\n";
    echo $response->getContent();
} catch (\Exception $e) {
    echo "Exception:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
