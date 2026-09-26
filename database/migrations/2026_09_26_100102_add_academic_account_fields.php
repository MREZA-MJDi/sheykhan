<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function(Blueprint $t){ $t->string('email')->nullable()->change(); $t->string('mobile',32)->nullable()->index()->after('email'); $t->string('status',32)->default('active')->index()->after('mobile'); });
        Schema::table('student_profiles', function(Blueprint $t){
            $t->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete()->after('grade');
            $t->string('national_id_lookup',64)->nullable()->unique()->after('grade_id');
            $t->text('national_id_encrypted')->nullable()->after('national_id_lookup');
            $t->string('registration_source',32)->default('self')->index()->after('national_id_encrypted');
            $t->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete()->after('registration_source');
            $t->timestamp('onboarded_at')->nullable()->after('registered_by'); $t->string('status',32)->default('active')->index()->after('onboarded_at');
        });
        Schema::table('classrooms', function(Blueprint $t){
            $t->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete()->after('course_id');
            $t->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete()->after('grade_id');
            $t->index(['academy_id','grade_id','academic_year_id']);
        });
        Schema::table('course_enrollments', function(Blueprint $t){
            $t->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete()->after('classroom_id');
            $t->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete()->after('academic_year_id');
            $t->string('registration_source',32)->default('self')->index()->after('registered_by');
            $t->index(['student_id','academic_year_id','status']);
        });
        Schema::table('live_classes', function(Blueprint $t){
            $t->timestamp('scheduled_end_at')->nullable()->after('scheduled_at'); $t->timestamp('started_at')->nullable()->after('scheduled_end_at');
            $t->timestamp('ended_at')->nullable()->after('started_at'); $t->timestamp('recording_released_at')->nullable()->after('ended_at');
            $t->string('recording_visibility',32)->default('enrolled')->index()->after('recording_released_at');
        });
        // MySQL may be using the existing (course_id, student_id) unique index to satisfy
        // the course_id foreign key because course_id is the leftmost indexed column.
        // Drop that FK before replacing the unique index, then restore it afterwards.
        Schema::table('course_enrollments', function(Blueprint $t){
            $t->dropForeign(['course_id']);
            $t->dropUnique(['course_id','student_id']);
            $t->unique(['course_id','student_id','academic_year_id'],'course_enrollments_course_student_year_unique');
            $t->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::table('course_enrollments',function(Blueprint $t){$t->dropUnique('course_enrollments_course_student_year_unique');$t->unique(['course_id','student_id']);$t->dropIndex(['student_id','academic_year_id','status']);$t->dropIndex(['registration_source']);$t->dropForeign(['academic_year_id']);$t->dropForeign(['registered_by']);$t->dropColumn(['academic_year_id','registered_by','registration_source']);});
        Schema::table('live_classes',function(Blueprint $t){$t->dropIndex(['recording_visibility']);$t->dropColumn(['scheduled_end_at','started_at','ended_at','recording_released_at','recording_visibility']);});
        Schema::table('classrooms',function(Blueprint $t){$t->dropIndex(['academy_id','grade_id','academic_year_id']);$t->dropForeign(['grade_id']);$t->dropForeign(['academic_year_id']);$t->dropColumn(['grade_id','academic_year_id']);});
        Schema::table('student_profiles',function(Blueprint $t){$t->dropForeign(['grade_id']);$t->dropForeign(['registered_by']);$t->dropUnique(['national_id_lookup']);$t->dropIndex(['registration_source']);$t->dropIndex(['status']);$t->dropColumn(['grade_id','national_id_lookup','national_id_encrypted','registration_source','registered_by','onboarded_at','status']);});
        Schema::table('users',function(Blueprint $t){$t->dropIndex(['mobile']);$t->dropIndex(['status']);$t->dropColumn(['mobile','status']);$t->string('email')->nullable(false)->change();});
    }
};