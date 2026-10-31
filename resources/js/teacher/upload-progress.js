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
        const row=document.createElement('div');row.className='teacher-upload-progress__file';
        const icon=document.createElement('span');icon.className='teacher-upload-progress__file-icon';icon.setAttribute('aria-hidden','true');icon.textContent=file.type.startsWith('image/')?'▧':'▤';
        const copy=document.createElement('span');copy.className='teacher-upload-progress__file-copy';
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
const bind=form=>{
    if(form.dataset.uploadProgressBound==='true')return;
    const panel=form.querySelector('[data-upload-progress-ui]');if(!panel)return;
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
        const xhr=new XMLHttpRequest();xhr.open((form.method||'POST').toUpperCase(),form.action,true);xhr.withCredentials=true;
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');xhr.setRequestHeader('Accept','text/html,application/xhtml+xml');
        const csrf=document.querySelector('meta[name=csrf-token]')?.content;if(csrf)xhr.setRequestHeader('X-CSRF-TOKEN',csrf);
        xhr.upload.addEventListener('progress',e=>{if(e.lengthComputable&&e.total>0){const p=e.loaded/e.total*100;paint(panel,p,p>=100?'فایل ارسال شد؛ سرور در حال پردازش است…':'در حال ارسال فایل‌ها…');}});
        xhr.addEventListener('load',()=>{
            if(xhr.status>=200&&xhr.status<400){paint(panel,100,'ارسال کامل شد؛ در حال بازگشت به صفحه…');window.location.assign(xhr.responseURL||form.action);return;}
            restore();fail(panel,xhr.status===419?'نشست منقضی شده است؛ صفحه را تازه‌سازی کنید.':xhr.status===413?'حجم فایل از محدودیت سرور بیشتر است.':xhr.status===422?'اطلاعات یا فایل انتخاب‌شده معتبر نیست؛ خطاهای فرم را بررسی کنید.':'ارسال فایل انجام نشد؛ دوباره تلاش کنید.');paint(panel,0,'ارسال ناموفق بود');
        });
        xhr.addEventListener('error',()=>{restore();fail(panel,'ارتباط با سرور قطع شد؛ فایل‌ها ثبت نشده‌اند. دوباره تلاش کنید.');paint(panel,0,'ارتباط قطع شد');});
        xhr.addEventListener('abort',()=>{restore();fail(panel,'ارسال فایل لغو شد.');paint(panel,0,'ارسال لغو شد');});
        xhr.send(new FormData(form));
    });
};
export function bootFileUploadProgress(root=document){root.querySelectorAll('form[data-upload-progress]').forEach(bind);}
