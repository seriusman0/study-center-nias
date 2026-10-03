<?php
$user = App\Models\User::where('email', 'admin@studycenter.com')->first();
$email = App\Models\CollectedEmail::first();

// We need to act as user
auth()->login($user);

$request = Illuminate\Http\Request::create(
    '/admin/collected-emails/' . $email->id . '/toggle-invite',
    'POST'
);

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
