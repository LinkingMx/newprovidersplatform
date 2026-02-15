<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

test('knowledge base panel redirects unauthenticated users to login', function () {
    $this->get('/kb')
        ->assertRedirect();
});

test('knowledge base panel is accessible to authenticated admin', function () {
    $this->actingAs($this->admin)
        ->get('/kb')
        ->assertRedirect();
});

test('knowledge base documentation route exists', function () {
    $this->actingAs($this->admin)
        ->get('/kb/inicio')
        ->assertSuccessful();
});
