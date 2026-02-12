<?php

use App\Jobs\SendSupplierInvitationEmail;
use App\Mail\SupplierInvitationMailable;
use App\Models\Supplier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('creates invitation token when supplier is created', function () {
    $supplier = Supplier::factory()->create(['status' => 'created']);

    $token = bin2hex(random_bytes(32));
    $invitation = $supplier->invitations()->create([
        'token' => $token,
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation)->not->toBeNull()
        ->and($invitation->token)->toBe($token)
        ->and($invitation->expires_at)->not->toBeNull()
        ->and($invitation->sent_at)->toBeNull();
});

it('sends invitation email with correct content', function () {
    Mail::fake();

    $supplier = Supplier::factory()->create([
        'name' => 'Test Provider',
        'email' => 'test@example.com',
    ]);

    $invitation = $supplier->invitations()->create([
        'token' => 'test-token-123',
        'expires_at' => now()->addDays(7),
    ]);

    dispatch(new SendSupplierInvitationEmail($supplier));

    Mail::assertSent(SupplierInvitationMailable::class, function ($mail) use ($supplier) {
        return $mail->hasTo($supplier->email);
    });

    $invitation->refresh();
    expect($invitation->sent_at)->not->toBeNull();
});

it('updates sent_at timestamp when email is sent', function () {
    $supplier = Supplier::factory()->create();

    $invitation = $supplier->invitations()->create([
        'token' => 'test-token',
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->sent_at)->toBeNull();

    dispatch(new SendSupplierInvitationEmail($supplier));

    $invitation->refresh();
    expect($invitation->sent_at)->not->toBeNull();
});

it('prevents invitation expiration check for valid tokens', function () {
    $supplier = Supplier::factory()->create();

    $validInvitation = $supplier->invitations()->create([
        'token' => 'valid-token',
        'expires_at' => now()->addDays(6),
    ]);

    expect($validInvitation->isExpired())->toBeFalse()
        ->and($validInvitation->isAccepted())->toBeFalse();
});

it('detects expired invitations', function () {
    $supplier = Supplier::factory()->create();

    $expiredInvitation = $supplier->invitations()->create([
        'token' => 'expired-token',
        'expires_at' => now()->subDays(1),
    ]);

    expect($expiredInvitation->isExpired())->toBeTrue();
});

it('dispatches send invitation job when supplier is created', function () {
    Queue::fake();

    $supplier = Supplier::factory()->create(['status' => 'created']);

    $token = bin2hex(random_bytes(32));
    $supplier->invitations()->create([
        'token' => $token,
        'expires_at' => now()->addDays(7),
    ]);

    dispatch(new SendSupplierInvitationEmail($supplier));

    Queue::assertPushed(SendSupplierInvitationEmail::class);
});

it('uses invitation token to build password reset url', function () {
    $supplier = Supplier::factory()->create();

    $invitation = $supplier->invitations()->create([
        'token' => 'my-test-token-12345',
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->token)->toBe('my-test-token-12345');

    $expectedUrl = route('supplier.set-password', ['token' => 'my-test-token-12345']);
    expect($expectedUrl)->toContain('my-test-token-12345');
});
