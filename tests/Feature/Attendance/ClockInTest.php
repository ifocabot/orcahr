<?php

use App\Jobs\ProcessAttendanceBatch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\RawTimeEvent;
use App\Models\ShiftMaster;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

function makeEmployeeWithUser(): array
{
    $user = User::factory()->create();

    $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG', 'is_active' => true]);

    $employee = Employee::create([
        'employee_code' => 'EMP-001',
        'full_name' => 'Test Employee',
        'email' => 'employee@test.com',
        'join_date' => now()->subYear()->toDateString(),
        'employment_status' => 'active',
        'department_id' => $dept->id,
        'user_id' => $user->id,
    ]);

    return [$user, $employee];
}

test('authenticated employee can clock in with selfie and coordinates', function () {
    [$user, $employee] = makeEmployeeWithUser();

    $selfie = UploadedFile::fake()->image('selfie.jpg');

    $response = $this
        ->actingAs($user)
        ->post('/attendance/clock', [
            'event_type' => 'IN',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'selfie' => $selfie,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(RawTimeEvent::where('employee_id', $employee->id)->count())->toBe(1);
    expect(RawTimeEvent::first()->event_type)->toBe('IN');

    Queue::assertPushed(ProcessAttendanceBatch::class, fn($job) => true);
});

test('employee can clock out after clocking in', function () {
    [$user, $employee] = makeEmployeeWithUser();

    // Existing IN event (older than 1 min to avoid duplicate guard)
    RawTimeEvent::create([
        'employee_id' => $employee->id,
        'event_time' => now()->subHours(8),
        'event_type' => 'IN',
        'source' => 'web',
        'processed_flag' => false,
        'selfie_path' => 'selfies/test.jpg',
        'latitude' => -6.2000,
        'longitude' => 106.8166,
    ]);

    $selfie = UploadedFile::fake()->image('selfie-out.jpg');

    $response = $this
        ->actingAs($user)
        ->post('/attendance/clock', [
            'event_type' => 'OUT',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'selfie' => $selfie,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(RawTimeEvent::where('employee_id', $employee->id)->count())->toBe(2);
    expect(RawTimeEvent::orderBy('id', 'desc')->first()->event_type)->toBe('OUT');
});

test('employee cannot double clock-in within 1 minute (TC-04)', function () {
    [$user, $employee] = makeEmployeeWithUser();

    // Existing IN event within the past minute
    RawTimeEvent::create([
        'employee_id' => $employee->id,
        'event_time' => now()->subSeconds(30),
        'event_type' => 'IN',
        'source' => 'web',
        'processed_flag' => false,
        'selfie_path' => 'selfies/test.jpg',
        'latitude' => -6.2000,
        'longitude' => 106.8166,
    ]);

    $selfie = UploadedFile::fake()->image('selfie2.jpg');

    $response = $this
        ->actingAs($user)
        ->post('/attendance/clock', [
            'event_type' => 'IN',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'selfie' => $selfie,
        ]);

    $response->assertSessionHasErrors('event_type');

    // Only the original event should exist
    expect(RawTimeEvent::where('employee_id', $employee->id)->count())->toBe(1);
    Queue::assertNothingPushed();
});

test('unauthenticated user is redirected from clock page', function () {
    $response = $this->get('/attendance/clock');
    $response->assertRedirect(route('login'));
});
