<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('course_grade',function(Blueprint $t){$t->foreignId('course_id')->constrained()->cascadeOnDelete();$t->foreignId('grade_id')->constrained('academic_grades')->cascadeOnDelete();$t->timestamps();$t->primary(['course_id','grade_id']);});
        Schema::create('learning_resources',function(Blueprint $t){
            $t->id();$t->foreignId('academy_id')->constrained()->cascadeOnDelete();$t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('media_id')->constrained()->restrictOnDelete();$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('title');$t->string('description')->nullable();$t->string('resource_type',32)->default('document')->index();
            $t->string('visibility',32)->default('enrolled_students')->index();$t->timestamp('release_at')->nullable()->index();
            $t->boolean('downloadable')->default(true);$t->string('status',32)->default('draft')->index();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();
            $t->index(
                ['academy_id','course_id','classroom_id','status'],
                'learning_resources_scope_status_idx'
            );
        });
    }
    public function down(): void { Schema::dropIfExists('learning_resources'); Schema::dropIfExists('course_grade'); }
};