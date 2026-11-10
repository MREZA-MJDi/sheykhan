<?php

namespace Tests\Feature;

use App\Models\StudentPersonalNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPersonalNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_list_and_delete_a_personal_note(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->post(route('student.notes.store'), [
                'content' => '  نکته‌ای برای مرور پیش از آزمون  ',
            ])
            ->assertRedirect(route('student.notes.index'))
            ->assertSessionHasNoErrors();

        $note = StudentPersonalNote::query()->sole();

        $this->assertSame($student->id, $note->student_id);
        $this->assertSame('نکته‌ای برای مرور پیش از آزمون', $note->content);

        $this->actingAs($student)
            ->get(route('student.notes.index'))
            ->assertOk()
            ->assertViewHas('personalNotes', fn ($notes) => $notes->total() === 1
                && $notes->getCollection()->first()->is($note));

        $this->actingAs($student)
            ->delete(route('student.notes.personal.destroy', $note))
            ->assertRedirect(route('student.notes.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('student_personal_notes', ['id' => $note->id]);
    }

    public function test_student_cannot_delete_another_students_personal_note(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $other = User::query()->where('email', 'student.parsa@sheykhan.test')->firstOrFail();

        $note = StudentPersonalNote::query()->create([
            'student_id' => $owner->id,
            'content' => 'یادداشت محرمانه',
        ]);

        $this->actingAs($other)
            ->delete(route('student.notes.personal.destroy', $note))
            ->assertNotFound();

        $this->assertDatabaseHas('student_personal_notes', [
            'id' => $note->id,
            'student_id' => $owner->id,
        ]);
    }

    public function test_non_students_cannot_create_personal_notes(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('student.notes.store'), ['content' => 'این درخواست مجاز نیست.'])
            ->assertForbidden();

        $this->assertDatabaseCount('student_personal_notes', 0);
    }
}
