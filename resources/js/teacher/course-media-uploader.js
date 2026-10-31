(() => {
'use strict';
const size=(bytes)=>{const units=['بایت','کیلوبایت','مگابایت','گیگابایت'];let n=Math.max(0,Number(bytes)||0),i=0;while(n>=1024&&i<units.length-1){n/=1024;i++;}return new Intl.NumberFormat('fa-IR',{maximumFractionDigits:i?1:0}).format(n)+' '+units[i];};
const maxBytes=(file)=>{const ext=(file.name.split('.').pop()||'').toLowerCase();const limits={jpg:10,jpeg:10,png:10,webp:10,pdf:50,zip:200,mp4:500,webm:500,mov:500};return (limits[ext]||10)*1024*1024;};
document.querySelectorAll('[data-media-uploader]').forEach((wrapper)=>{
 const form=wrapper.querySelector('form[data-upload-form]');if(!form||form.dataset.teacherUploaderBound==='1')return;form.dataset.teacherUploaderBound='1';
 const input=form.querySelector('input[type=file]'),preview=wrapper.querySelector('[data-upload-preview]'),image=wrapper.querySelector('[data-preview-image]'),video=wrapper.querySelector('[data-preview-video]'),generic=wrapper.querySelector('[data-preview-generic]'),name=wrapper.querySelector('[data-preview-name]'),sizeLabel=wrapper.querySelector('[data-preview-size]'),progress=wrapper.querySelector('.course-upload-progress'),bar=wrapper.querySelector('[data-upload-progress-bar]'),percent=wrapper.querySelector('[data-upload-percent]'),message=wrapper.querySelector('[data-upload-message]'),submit=form.querySelector('[type=submit]');
 let objectUrl=null;
 const showError=(text)=>{if(message){message.textContent=text;message.classList.add('is-error');}};
 input?.addEventListener('change',()=>{
   const file=input.files?.[0];if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}
   if(!file){if(preview)preview.hidden=true;if(progress)progress.hidden=true;if(message)message.textContent='';return;}
   const limit=maxBytes(file);
   if(file.size>limit){input.value='';if(preview)preview.hidden=true;showError('حجم فایل برای این نوع محتوا حداکثر '+size(limit)+' است.');return;}
   if(message){message.textContent='';message.classList.remove('is-error');}
   if(preview)preview.hidden=false;if(name)name.textContent=file.name;if(sizeLabel)sizeLabel.textContent=size(file.size);
   if(image){image.hidden=true;image.removeAttribute('src');}
   if(video){video.pause?.();video.hidden=true;video.removeAttribute('src');}
   if(generic)generic.hidden=true;
   if(file.type.startsWith('image/')&&image){objectUrl=URL.createObjectURL(file);image.src=objectUrl;image.hidden=false;image.onload=()=>{if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}};}
   else if(file.type.startsWith('video/')&&video&&file.size<=100*1024*1024){objectUrl=URL.createObjectURL(file);video.src=objectUrl;video.hidden=false;video.preload='metadata';video.onloadedmetadata=()=>{if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}};}
   else if(generic)generic.hidden=false;
 });
 form.addEventListener('submit',(event)=>{
   const file=input?.files?.[0];if(!file)return;
   if(form.dataset.uploading==='1'){event.preventDefault();return;}
   event.preventDefault();form.dataset.uploading='1';form.setAttribute('aria-busy','true');if(progress)progress.hidden=false;
   if(message){message.textContent='در حال آماده‌سازی فایل…';message.classList.remove('is-error');}
   if(submit){submit.disabled=true;submit.dataset.originalText=submit.textContent;submit.textContent='در حال آپلود…';}
   const xhr=new XMLHttpRequest();xhr.open('POST',form.action,true);xhr.withCredentials=true;
   xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');xhr.setRequestHeader('Accept','text/html,application/xhtml+xml');
   const csrf=document.querySelector('meta[name=csrf-token]')?.content;if(csrf)xhr.setRequestHeader('X-CSRF-TOKEN',csrf);
   xhr.upload.addEventListener('progress',(e)=>{if(!e.lengthComputable||!e.total)return;const n=Math.min(100,Math.round(e.loaded/e.total*100));if(bar)bar.style.width=n+'%';if(percent)percent.textContent=n.toLocaleString('fa-IR')+'٪';if(message)message.textContent=n>=100?'فایل ارسال شد؛ سرور در حال پردازش است…':'در حال ارسال فایل…';});
   const restore=()=>{form.dataset.uploading='0';form.removeAttribute('aria-busy');if(submit){submit.disabled=false;submit.textContent=submit.dataset.originalText||'آپلود فایل';delete submit.dataset.originalText;}};
   xhr.addEventListener('load',()=>{if(xhr.status>=200&&xhr.status<400){if(message)message.textContent='آپلود کامل شد؛ صفحه در حال به‌روزرسانی است…';window.location.assign(xhr.responseURL||window.location.href);return;}restore();showError(xhr.status===419?'نشست منقضی شده است؛ صفحه را تازه‌سازی کنید.':xhr.status===413?'حجم فایل از محدودیت سرور بیشتر است.':xhr.status===422?'فرمت یا حجم فایل معتبر نیست؛ خطاهای فرم را بررسی کنید.':'آپلود انجام نشد؛ دوباره تلاش کنید.');});
   xhr.addEventListener('error',()=>{restore();showError('ارتباط با سرور قطع شد؛ فایلی ثبت نشده است. دوباره تلاش کنید.');});
   xhr.addEventListener('abort',()=>{restore();showError('آپلود لغو شد.');});
   xhr.send(new FormData(form));
 });
});
})();