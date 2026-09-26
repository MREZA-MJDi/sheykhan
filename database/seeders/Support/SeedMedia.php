<?php
namespace Database\Seeders\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

final class SeedMedia
{
 public static function make(string $key,int $uploadedBy,string $mime,string $extension,string $collection,string $visibility='private'): Media
 {
  $disk=$visibility==='public'?'public':'local';
  $path='seed/'.$collection.'/'.$key.'.'.$extension;
  $storage=Storage::disk($disk);
  if(!$storage->exists($path)) $storage->put($path,self::placeholder($extension,$key));
  return Media::firstOrCreate(['disk'=>$disk,'path'=>$path],[
   'uploaded_by'=>$uploadedBy,'original_name'=>basename($path),'file_name'=>basename($path),'mime_type'=>$mime,'extension'=>$extension,
   'size'=>$storage->size($path),'checksum'=>hash('sha256',$storage->get($path)),'visibility'=>$visibility,'collection'=>$collection,
   'metadata'=>['seeded'=>true,'editable_from_admin'=>true],'status'=>'active'
  ]);
 }

 private static function placeholder(string $extension,string $key): string
 {
  return match($extension){
   'svg'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675"><rect width="1200" height="675" fill="#24345f"/><text x="80" y="340" fill="#fff" font-size="52" font-family="Arial">Sheykhan</text><text x="80" y="400" fill="#dce5ff" font-size="26" font-family="Arial">'.htmlspecialchars($key,ENT_XML1).'</text></svg>',
   'pdf'=>self::pdf($key),
   default=>'Sheykhan seeded media placeholder: '.$key,
  };
 }

 private static function pdf(string $key): string
 {
  $objects=[
   '<< /Type /Catalog /Pages 2 0 R >>',
   '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
   '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
   '<< /Length 48 >>\nstream\nBT /F1 18 Tf 72 720 Td (Sheykhan - '.$key.') Tj ET\nendstream',
   '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];
  $pdf="%PDF-1.4\n"; $offsets=[0];
  foreach($objects as $i=>$object){$offsets[$i+1]=strlen($pdf);$pdf.=($i+1)." 0 obj\n".$object."\nendobj\n";}
  $xref=strlen($pdf); $pdf.="xref\n0 6\n0000000000 65535 f \n";
  for($i=1;$i<=5;$i++) $pdf.=str_pad((string)$offsets[$i],10,'0',STR_PAD_LEFT)." 00000 n \n";
  return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
 }
}
