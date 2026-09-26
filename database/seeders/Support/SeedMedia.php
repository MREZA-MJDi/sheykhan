<?php
namespace Database\Seeders\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

final class SeedMedia
{
 public static function make(string $key,int $uploadedBy,string $mime,string $extension,string $collection,string $visibility='private'): Media
 {
  $disk=$visibility==='public'?'public':'local'; $path='seed/'.$collection.'/'.$key.'.'.$extension; $storage=Storage::disk($disk);
  if(!$storage->exists($path)) $storage->put($path,self::placeholder($extension,$key));
  return Media::firstOrCreate(['disk'=>$disk,'path'=>$path],[
   'uploaded_by'=>$uploadedBy,'original_name'=>basename($path),'file_name'=>basename($path),'mime_type'=>$mime,'extension'=>$extension,
   'size'=>$storage->size($path),'checksum'=>hash('sha256',$storage->get($path)),'visibility'=>$visibility,'collection'=>$collection,
   'metadata'=>['seeded'=>true,'editable_from_admin'=>true],'status'=>'active'
  ]);
 }
 private static function placeholder(string $ext,string $key): string
 {
  if($ext==='svg') return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675"><rect width="1200" height="675" fill="#24345f"/><text x="80" y="340" fill="#fff" font-size="52" font-family="Arial">Sheykhan</text><text x="80" y="400" fill="#dce5ff" font-size="26" font-family="Arial">'.htmlspecialchars($key,ENT_XML1).'</text></svg>';
  if($ext==='pdf') return "%PDF-1.4\n1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n4 0 obj<< /Length 44 >>stream\nBT /F1 18 Tf 72 720 Td (Sheykhan '.$key.') Tj ET\nendstream\nendobj\n5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\ntrailer<< /Root 1 0 R /Size 6 >>\n%%EOF";
  return 'Sheykhan seeded media placeholder: '.$key;
 }
}
