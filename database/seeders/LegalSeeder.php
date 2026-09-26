<?php
namespace Database\Seeders;

use App\Models\LegalConsent;
use App\Models\LegalDocument;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class LegalSeeder extends Seeder
{
 public function run(): void
 {
  $student=User::where('email','student.armin@sheykhan.test')->firstOrFail(); $order=Order::where('buyer_id',$student->id)->latest('id')->first();
  foreach([
   ['code'=>'purchase-terms','title'=>'شرایط خرید محصولات','version'=>'1.0','document_type'=>'terms_purchase','required_for_purchase'=>true],
   ['code'=>'copyright','title'=>'قوانین کپی‌رایت محصولات','version'=>'1.0','document_type'=>'copyright','required_for_purchase'=>true],
   ['code'=>'media-release','title'=>'رضایت انتشار محتوای رسانه‌ای','version'=>'1.0','document_type'=>'media_release','required_for_purchase'=>false],
  ] as $d){
   $body='متن رسمی این سند باید توسط مدیر آکادمی تکمیل و منتشر شود.';
   $doc=LegalDocument::firstOrCreate(['code'=>$d['code'],'version'=>$d['version']],$d+['content'=>$body,'content_hash'=>hash('sha256',$body),'is_active'=>true,'published_at'=>null]);
   if($order && $d['required_for_purchase']) LegalConsent::firstOrCreate(['user_id'=>$student->id,'document_id'=>$doc->id,'order_id'=>$order->id],['document_version'=>$doc->version,'consent_type'=>'accepted','content_hash'=>$doc->content_hash,'ip_address'=>'127.0.0.1','user_agent'=>'LegalSeeder','accepted_at'=>now()->subDays(3)]);
  }
 }
}
