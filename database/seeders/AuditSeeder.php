<?php
namespace Database\Seeders;
use App\Models\Academy;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;
class AuditSeeder extends Seeder
{
 public function run(): void
 {
  $owner=User::where('email','owner@sheykhan.test')->firstOrFail();
  $academy=Academy::where('slug','sheykhan-academy')->firstOrFail();
  AuditLog::firstOrCreate(['actor_id'=>$owner->id,'action'=>'seed.platform_initialized','subject_type'=>Academy::class,'subject_id'=>$academy->id],['old_values'=>null,'new_values'=>['seed'=>'platform-data'],'ip_address'=>'127.0.0.1','user_agent'=>'AuditSeeder']);
 }
}
