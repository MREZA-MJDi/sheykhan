/* File preview and actual multipart transfer progress for all role panels. */
const sizeLabel = bytes => {
    const units=['بایت','کیلوبایت','مگابایت','گیگابایت']; let value=Math.max(0,Number(bytes)||0),unit=0;
    while(value>=1024&&unit<units.length-1){value/=1024;unit++;}
    return new Intl.NumberFormat('fa-IR',{maximumFractionDigits:unit?1:0}).format(value)+' '+units[unit];
};
const fa = value => String(value).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
const getFiles = form => Array.from(form.querySelectorAll('input[type=file]')).flatMap(input=>Array.from(input.files||[]));
const render = form => {
    const panel=form.querySelector('[data-upload-progress-ui]'); if(!panel)return;
    const list=panel.querySelector('[data-upload-file-list]'),summary=panel.querySelector('[data-upload-summary]');
    const files=getFiles(form); list?.replaceChildren();
    if(!files.length){panel.hidden=true;return;}
    panel.hidden=false; let total=0;
    files.forEach(file=>{
        total+=file.size;
        const row=document.createElement('div');row.dataset.uploadFileRow='true';row.setAttribute('role','listitem');
        const icon=document.createElement('span');icon.dataset.uploadFileIcon='true';icon.setAttribute('aria-hidden','true');icon.textContent=file.type.startsWith('image/')?'▧':'▤';
        const copy=document.createElement('span');copy.dataset.uploadFileCopy='true';
        const name=document.createElement('strong');name.textContent=file.name;
        const size=document.createElement('small');size.textContent=sizeLabel(file.size);
        copy.append(name,size);row.append(icon,copy);list?.append(row);
    });
    if(summary)summary.textContent=fa(files.length)+' فایل انتخاب شده · مجموع '+sizeLabel(total);
};
const paint=(panel,pct,message)=>{
    const value=Math.max(0,Math.min(100,Math.round(pct)));
    const bar=panel.querySelector('[data-upload-progress-bar]'),track=panel.querySelector('[data-upload-progress-track]');
    const percent=panel.querySelector('[data-upload-progress-percent]'),status=panel.querySelector('[data-upload-progress-message]');
    if(bar)bar.style.width=value+'%';if(track)track.setAttribute('aria-valuenow',String(value));
    if(percent)percent.textContent=fa(value)+'٪';if(status)status.textContent=message;
};
const fail=(panel,message)=>{
    const error=panel.querySelector('[data-upload-progress-error]'),status=panel.querySelector('[data-upload-progress-status]');
    if(error){error.textContent=message;error.hidden=false;}if(status)status.hidden=true;
};
const ensurePanel=form=>{
    let panel=form.querySelector('[data-upload-progress-ui]');
    if(panel)return panel;
    panel=document.createElement('section');
    panel.dataset.uploadProgressUi='true';
    panel.hidden=true;
    panel.className='mt-3 rounded-xl border border-slate-200 bg-white p-3 text-slate-700';
    panel.setAttribute('aria-live','polite');
    const status=document.createElement('div');status.dataset.uploadProgressStatus='true';status.hidden=true;
    const heading=document.createElement('div');heading.className='flex items-center justify-between gap-3 text-xs font-bold';
    const message=document.createElement('span');message.dataset.uploadProgressMessage='true';message.textContent='در انتظار ارسال فایل‌ها…';
    const percent=document.createElement('span');percent.dataset.uploadProgressPercent='true';percent.textContent='۰٪';
    heading.append(message,percent);
    const track=document.createElement('div');track.dataset.uploadProgressTrack='true';track.className='mt-2 h-2 overflow-hidden rounded-full bg-slate-100';track.setAttribute('role','progressbar');track.setAttribute('aria-label','پیشرفت بارگذاری فایل');track.setAttribute('aria-valuemin','0');track.setAttribute('aria-valuemax','100');track.setAttribute('aria-valuenow','0');
    const bar=document.createElement('div');bar.dataset.uploadProgressBar='true';bar.className='h-full w-0 rounded-full bg-indigo-600 transition-[width] duration-150';track.append(bar);
    status.append(heading,track);
    const eta=document.createElement('p');eta.dataset.uploadProgressEta='true';eta.className='mt-1 text-[10px] text-slate-500';eta.setAttribute('aria-live','polite');
    const summary=document.createElement('p');summary.dataset.uploadSummary='true';summary.className='text-xs text-slate-500';
    const list=document.createElement('ul');list.dataset.uploadFileList='true';list.className='mt-2 grid gap-1 text-xs';list.setAttribute('role','list');
    const error=document.createElement('p');error.dataset.uploadProgressError='true';error.hidden=true;error.className='mt-2 text-xs font-semibold text-rose-600';error.setAttribute('role','alert');
    panel.append(status,eta,summary,list,error);
    form.append(panel);
    return panel;
};
const bind=form=>{
    if(form.dataset.uploadProgressBound==='true')return;
    const panel=ensurePanel(form);if(!panel)return;
    form.dataset.uploadProgressBound='true';
    form.addEventListener('change',event=>{
        if(event.target instanceof HTMLInputElement&&event.target.type==='file'){
            const error=panel.querySelector('[data-upload-progress-error]');if(error){error.hidden=true;error.textContent='';}
            render(form);
        }
    });
    form.addEventListener('submit',event=>{
        if(!getFiles(form).length)return;
        if(form.dataset.uploading==='true'){event.preventDefault();return;}
        const probe=new XMLHttpRequest();if(!probe.upload)return;
        event.preventDefault();form.dataset.uploading='true';form.setAttribute('aria-busy','true');
        const bar=panel.querySelector('[data-upload-progress-status]'),error=panel.querySelector('[data-upload-progress-error]');
        if(bar)bar.hidden=false;if(error){error.hidden=true;error.textContent='';}
        paint(panel,0,'در حال آماده‌سازی فایل‌ها…');
        const buttons=Array.from(form.querySelectorAll('button[type=submit],input[type=submit]'));
        buttons.forEach(button=>{button.dataset.wasDisabled=button.disabled?'1':'0';button.disabled=true;button.setAttribute('aria-busy','true');});
        const restore=()=>{form.dataset.uploading='false';form.removeAttribute('aria-busy');buttons.forEach(button=>{button.disabled=button.dataset.wasDisabled==='1';button.removeAttribute('aria-busy');delete button.dataset.wasDisabled;});};
        const startedAt=performance.now();
        const eta=panel.querySelector('[data-upload-progress-eta]');
        const xhr=new XMLHttpRequest();xhr.open((form.method||'POST').toUpperCase(),form.action,true);xhr.withCredentials=true;
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');xhr.setRequestHeader('Accept','text/html,application/xhtml+xml');
        const csrf=document.querySelector('meta[name=csrf-token]')?.content;if(csrf)xhr.setRequestHeader('X-CSRF-TOKEN',csrf);
        xhr.upload.addEventListener('progress',e=>{
            if(!e.lengthComputable||e.total<=0)return;
            const p=e.loaded/e.total*100;
            paint(panel,p,p>=100?'فایل ارسال شد؛ سرور در حال پردازش است…':'در حال ارسال فایل‌ها…');
            if(eta&&e.loaded>0&&p<100){
                const elapsed=Math.max((performance.now()-startedAt)/1000,.25);
                const remaining=Math.max(1,Math.ceil((e.total-e.loaded)/(e.loaded/elapsed)));
                eta.textContent=remaining>=60?'زمان تقریبی باقی‌مانده: '+fa(Math.ceil(remaining/60))+' دقیقه':'زمان تقریبی باقی‌مانده: '+fa(remaining)+' ثانیه';
            }else if(eta){eta.textContent=p>=100?'ارسال فایل تمام شد؛ پردازش سرور ادامه دارد.':'';}
        });
        xhr.addEventListener('load',()=>{
            if(xhr.status>=200&&xhr.status<400){paint(panel,100,'ارسال کامل شد؛ در حال بازگشت به صفحه…');window.location.assign(xhr.responseURL||form.action);return;}
            restore();fail(panel,xhr.status===419?'نشست منقضی شده است؛ صفحه را تازه‌سازی کنید.':xhr.status===413?'حجم فایل از محدودیت سرور بیشتر است.':xhr.status===422?'اطلاعات یا فایل انتخاب‌شده معتبر نیست؛ خطاهای فرم را بررسی کنید.':'ارسال فایل انجام نشد؛ دوباره تلاش کنید.');paint(panel,0,'ارسال ناموفق بود');
        });
        xhr.addEventListener('error',()=>{restore();fail(panel,'ارتباط با سرور قطع شد؛ فایل‌ها ثبت نشده‌اند. دوباره تلاش کنید.');paint(panel,0,'ارتباط قطع شد');});
        xhr.addEventListener('abort',()=>{restore();fail(panel,'ارسال فایل لغو شد.');paint(panel,0,'ارسال لغو شد');});
        xhr.send(new FormData(form));
    });
};
export function bootFileUploadProgress(root=document){root.querySelectorAll('form').forEach(form=>{if(form.dataset.uploadProgress==='off'||form.hasAttribute('data-custom-upload')||form.closest('[data-media-uploader]'))return;if(form.querySelector('input[type=file]'))bind(form);});}
