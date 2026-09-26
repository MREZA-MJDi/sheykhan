<?php
namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDownload;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\ProtectedFile;
use App\Models\User;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
 public function run(): void
 {
  $academy=Academy::where('slug','sheykhan-academy')->firstOrFail(); $owner=User::where('email','owner@sheykhan.test')->firstOrFail(); $student=User::where('email','student.armin@sheykhan.test')->firstOrFail();
  $categories=[];
  foreach([['books','کتابخانه','تألیفات و ترجمه‌ها'],['booklets','جزوه','جزوه‌های آموزشی آکادمی'],['exams','آزمون','آزمون‌های استاندارد']] as $i=>$x){$categories[$x[0]]=ProductCategory::firstOrCreate(['slug'=>$x[0]],['name'=>$x[1],'description'=>$x[2],'sort_order'=>$i+1,'is_active'=>true]);}
  foreach([
   ['book-smart-thinking','کتاب تفکر هوشمند','books',320000,'book'],['booklet-math-7','جزوه جامع ریاضی هفتم','booklets',180000,'booklet'],['exam-gifted-9-1','آزمون جامع تیزهوشان نهم شماره ۱','exams',95000,'exam']
  ] as $x){
   [$slug,$title,$cat,$price,$type]=$x;
   $product=Product::firstOrCreate(['slug'=>$slug],['academy_id'=>$academy->id,'category_id'=>$categories[$cat]->id,'created_by'=>$owner->id,'title'=>$title,'subtitle'=>'محصول آموزشی قابل مدیریت از پنل.','description'=>'رکورد تست چرخه کامل فروش، رضایت و دسترسی.','product_type'=>$type,'delivery_type'=>'download','price'=>$price,'currency'=>'IRR','status'=>'published','is_featured'=>true,'published_at'=>now()->subDays(5)]);
   $media=SeedMedia::make('product-'.$slug,$owner->id,'application/pdf','pdf','products','private');
   $file=ProductFile::firstOrCreate(['product_id'=>$product->id,'media_id'=>$media->id],['version'=>'1.0','is_primary'=>true,'is_preview'=>false,'requires_watermark'=>true]);
   $order=Order::firstOrCreate(['order_number'=>'SHK-SEED-'.str_pad((string)$product->id,6,'0',STR_PAD_LEFT)],['buyer_id'=>$student->id,'status'=>'paid','currency'=>'IRR','subtotal'=>$price,'discount'=>0,'total'=>$price,'billing_name'=>$student->name,'billing_mobile'=>$student->mobile,'legal_consent_completed'=>true,'paid_at'=>now()->subDays(3)]);
   $item=OrderItem::firstOrCreate(['order_id'=>$order->id,'product_id'=>$product->id],['beneficiary_id'=>$student->id,'product_title_snapshot'=>$product->title,'unit_price'=>$price,'quantity'=>1,'total_price'=>$price]);
   Payment::firstOrCreate(['order_id'=>$order->id,'gateway'=>'seed-bank'],['amount'=>$price,'currency'=>'IRR','status'=>'successful','authority'=>'SEED-AUTH-'.$product->id,'transaction_id'=>'SEED-TXN-'.$product->id,'tracking_code'=>'SEED-'.$product->id,'gateway_response'=>['seed'=>true],'paid_at'=>now()->subDays(3)]);
   $entitlement=ProductEntitlement::firstOrCreate(['order_item_id'=>$item->id],['user_id'=>$student->id,'product_id'=>$product->id,'status'=>'active','starts_at'=>now()->subDays(3),'granted_at'=>now()->subDays(3)]);
   ProductDownload::firstOrCreate(['entitlement_id'=>$entitlement->id,'product_file_id'=>$file->id],['ip_address'=>'127.0.0.1','user_agent'=>'Seeder','downloaded_at'=>now()->subDay()]);
   ProtectedFile::firstOrCreate(['product_file_id'=>$file->id,'entitlement_id'=>$entitlement->id],['source_checksum'=>$media->checksum,'generated_path'=>'protected/seed/'.$slug.'.pdf','watermark_text'=>$student->name.' | '.$order->order_number,'watermark_type'=>'text','generated_at'=>now()->subDays(3),'status'=>'ready','download_count'=>1]);
  }
 }
}
