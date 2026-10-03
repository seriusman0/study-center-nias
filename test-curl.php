<?php
$user = App\Models\User::where('email', 'admin@studycenter.com')->first();
auth()->login($user);
$email = App\Models\CollectedEmail::first();
if (!$email) die("No email");

$request = Illuminate\Http\Request::create(
    '/admin/collected-emails/' . $email->id . '/toggle-invite',
    'POST'
);

$response = app()->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
echo $response->getContent();
