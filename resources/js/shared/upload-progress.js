/* File preview and actual multipart transfer progress for all role panels. */
const sizeLabel = bytes => {
    const units=['بایت','کیلوبایت','مگابایت','گیگابایت']; let value=Math.max(0,Number(bytes)||0),unit=0;
    while(value>=1024&&unit<units.length-1){value/=1024;unit++;}
    return new Intl.NumberFormat('fa-IR',{maximumFractionDigits:unit?1:0}).format(value)+' '+units[unit];
};
const fa = value => String(value).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
const previewUrls = new WeakMap();
const getFiles = form => Array.from(form.querySelectorAll('input[type=file]')).flatMap(input=>Array.from(input.files||[]));
const render = form => {
    const panel=form.querySelector('[data-upload-progress-ui]');
    const list=panel?.querySelector('[data-upload-file-list]'),summary=panel?.querySelector('[data-upload-summary]');
    const input=form.querySelector('input[type=file]');
    const files=getFiles(form);
    list?.replaceChildren();

    const previousUrl=previewUrls.get(form);
    if(previousUrl){URL.revokeObjectURL(previousUrl);previewUrls.delete(form);}

    const preview=form.querySelector('[data-upload-preview]');
    const image=preview?.querySelector('[data-preview-image]');
    const video=preview?.querySelector('[data-preview-video]');
    const generic=preview?.querySelector('[data-preview-generic]');
    const previewName=preview?.querySelector('[data-preview-name]');
    const previewSize=preview?.querySelector('[data-preview-size]');

    if(!files.length){
        if(panel)panel.hidden=true;
        if(preview)preview.hidden=true;
        if(image){image.hidden=true;image.removeAttribute('src');}
        if(video){video.pause?.();video.hidden=true;video.removeAttribute('src');}
        if(generic)generic.hidden=true;
        return;
    }

    if(panel)panel.hidden=false;
    let total=0;
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

    const file=files[0];
    if(preview){
        preview.hidden=false;
        if(previewName)previewName.textContent=file.name;
        if(previewSize)previewSize.textContent=sizeLabel(file.size);
        if(image){image.hidden=true;image.removeAttribute('src');}
        if(video){video.pause?.();video.hidden=true;video.removeAttribute('src');}
        if(generic)generic.hidden=true;

        if(file.type.startsWith('image/')&&image){
            const url=URL.createObjectURL(file);
            previewUrls.set(form,url);
            image.src=url;image.hidden=false;
            image.onload=()=>{if(previewUrls.get(form)===url){URL.revokeObjectURL(url);previewUrls.delete(form);}};
        }else if(file.type.startsWith('video/')&&video&&file.size<=100*1024*1024){
            const url=URL.createObjectURL(file);
            previewUrls.set(form,url);
            video.src=url;video.hidden=false;video.preload='metadata';
        }else if(generic){
            generic.hidden=false;
        }
    }
};
const paint=(panel,pct,message)=>{
    const value=Math.max(0,Math.min(100,Math.round(pct)));
    const bar=panel.querySelector('[data-upload-progress-bar]'),track=panel.querySelector('[data-upload-progress-track]');
    const percent=panel.querySelector('[data-upload-progress-percent], [data-upload-percent]'),status=panel.querySelector('[data-upload-progress-message], [data-upload-message]');
    if(bar)bar.style.width=value+'%';if(track)track.setAttribute('aria-valuenow',String(value));
    if(percent)percent.textContent=fa(value)+'٪';if(status){status.textContent=message;status.classList.remove('text-rose-600');}
};
const fail=(panel,message)=>{
    const error=panel.querySelector('[data-upload-progress-error]'),status=panel.querySelector('[data-upload-progress-status], [data-upload-message]');
    if(error){error.textContent=message;error.hidden=false;}if(status){status.textContent=message;status.hidden=false;status.classList.add('text-rose-600');}
};
const ensurePanel=form=>{
    let panel=form.querySelector('[data-upload-progress-ui]');
    if(panel)return panel;
    panel=form.querySelector('[data-upload-progress]');
    if(panel){panel.dataset.uploadProgressUi='true';return panel;}
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

const xhrRequest=(method,url,body,onProgress)=>{
    return new Promise((resolve,reject)=>{
        const xhr=new XMLHttpRequest();
        xhr.open(method,url,true);
        xhr.withCredentials=true;
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
        xhr.setRequestHeader('Accept','application/json');
        const csrf=document.querySelector('meta[name=csrf-token]')?.content;
        if(csrf)xhr.setRequestHeader('X-CSRF-TOKEN',csrf);
        if(onProgress)xhr.upload.addEventListener('progress',onProgress);
        xhr.addEventListener('load',()=>{
            let payload={};
            try{payload=JSON.parse(xhr.responseText||'{}');}catch{}
            resolve({status:xhr.status,payload,xhr});
        });
        xhr.addEventListener('error',()=>reject(new Error('ارتباط با سرور قطع شد؛ بخش‌های دریافت‌شده برای ادامه آپلود نگهداری می‌شوند.')));
        xhr.addEventListener('abort',()=>reject(new Error('ارسال فایل لغو شد؛ می‌توانی با انتخاب دوباره همان فایل ادامه بدهی.')));
        xhr.send(body||null);
    });
};
const uploadChunks=async(form,panel)=>{
    const files=getFiles(form);
    if(files.length!==1)throw new Error('برای آپلود تکه‌ای، هر بار فقط یک فایل انتخاب کن.');
    const file=files[0];
    const maxBytes=Number(form.dataset.maxBytes||0);
    if(maxBytes>0&&file.size>maxBytes)throw new Error('حجم فایل نباید بیشتر از '+sizeLabel(maxBytes)+' باشد.');
    if(file.size<1)throw new Error('فایل خالی است؛ یک فایل معتبر انتخاب کن.');
    const chunkBytes=5*1024*1024;
    const totalChunks=Math.ceil(file.size/chunkBytes);
    const collection=form.querySelector('[name=collection]')?.value||'course-assets';
    const uploadUrl=form.dataset.chunkUrl;
    const statusTemplate=form.dataset.chunkStatusUrl;
    const completeTemplate=form.dataset.chunkCompleteUrl;
    if(!uploadUrl||!statusTemplate||!completeTemplate)throw new Error('مسیر ادامه آپلود در فرم تنظیم نشده است.');
    const storageKey='sheykhan-course-upload:'+form.action+':'+collection+':'+file.name+':'+file.size+':'+file.lastModified;
    const makeId=()=>window.crypto?.randomUUID?.()||'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>{const r=Math.random()*16|0;return(c==='x'?r:(r&3|8)).toString(16);});
    const byId=template=>template.replace('UPLOAD_ID',encodeURIComponent(uploadId));
    let uploadId=null;
    try{uploadId=sessionStorage.getItem(storageKey);}catch{}
    if(!uploadId){uploadId=makeId();try{sessionStorage.setItem(storageKey,uploadId);}catch{}}
    let status=await xhrRequest('GET',byId(statusTemplate),null);
    if(status.status===404||status.status===410||status.status===409){
        uploadId=makeId();
        try{sessionStorage.setItem(storageKey,uploadId);}catch{}
        status={status:404,payload:{}};
    }else if(status.status<200||status.status>=300){
        throw new Error(status.payload.message||'وضعیت آپلود قابل بازیابی نیست؛ دوباره تلاش کن.');
    }
    const received=new Set(Array.isArray(status.payload.received_chunks)?status.payload.received_chunks.map(Number):[]);
    let sentBytes=0;
    received.forEach(index=>{
        if(index>=0&&index<totalChunks)sentBytes+=Math.min(chunkBytes,file.size-index*chunkBytes);
    });
    paint(panel,file.size?sentBytes/file.size*100:0,received.size?'ادامه آپلود از بخش باقی‌مانده…':'شروع آپلود تکه‌ای…');
    const eta=panel.querySelector('[data-upload-progress-eta]');
    if(eta)eta.textContent='بخش‌های تکمیل‌شده برای همین فایل قابل بازیابی هستند.';
    for(let index=0;index<totalChunks;index++){
        if(received.has(index))continue;
        const start=index*chunkBytes;
        const end=Math.min(file.size,start+chunkBytes);
        const part=file.slice(start,end);
        const body=new FormData();
        body.append('upload_id',uploadId);
        body.append('chunk_index',String(index));
        body.append('total_chunks',String(totalChunks));
        body.append('file_name',file.name);
        body.append('file_size',String(file.size));
        body.append('mime_type',file.type||'application/octet-stream');
        body.append('collection',collection);
        body.append('chunk',part,file.name+'.part');
        const response=await xhrRequest('POST',uploadUrl,body,event=>{
            const completed=sentBytes+(event.lengthComputable?Math.min(event.loaded,part.size):0);
            paint(panel,file.size?completed/file.size*100:0,'در حال ارسال بخش '+fa(index+1)+' از '+fa(totalChunks)+'…');
        });
        if(response.status<200||response.status>=300){
            const message=response.payload.message||Object.values(response.payload.errors||{}).flat().find(Boolean);
            throw new Error(message||'بخشی از فایل ارسال نشد؛ دوباره تلاش کن.');
        }
        sentBytes+=part.size;
        paint(panel,file.size?sentBytes/file.size*100:100,'بخش '+fa(index+1)+' از '+fa(totalChunks)+' دریافت شد.');
    }
    paint(panel,100,'همه بخش‌ها دریافت شدند؛ فایل در حال یکپارچه‌سازی و اعتبارسنجی است…');
    const completed=await xhrRequest('POST',byId(completeTemplate),new FormData());
    if(completed.status<200||completed.status>=300){
        const message=completed.payload.message||Object.values(completed.payload.errors||{}).flat().find(Boolean);
        throw new Error(message||'تکمیل فایل انجام نشد؛ می‌توانی بدون ارسال دوباره بخش‌ها تلاش کنی.');
    }
    try{sessionStorage.removeItem(storageKey);}catch{}
    paint(panel,100,'فایل با موفقیت ذخیره شد؛ صفحه در حال به‌روزرسانی است…');
    if(completed.payload.redirect_url)window.location.assign(completed.payload.redirect_url);
    else window.location.reload();
};

const bind=form=>{
    if(form.dataset.uploadProgressBound==='true')return;
    const panel=ensurePanel(form);if(!panel)return;
    form.dataset.uploadProgressBound='true';
    form.addEventListener('change',event=>{
        if(event.target instanceof HTMLInputElement&&event.target.type==='file'){
            const error=panel.querySelector('[data-upload-progress-error]');if(error){error.hidden=true;error.textContent='';}
            const status=panel.querySelector('[data-upload-message]');if(status){status.textContent='';status.classList.remove('text-rose-600');}
            render(form);
        }
    });
    form.addEventListener('submit',event=>{
        if(form.dataset.chunkUpload==='true'){
            if(!getFiles(form).length)return;
            event.preventDefault();
            if(form.dataset.uploading==='true')return;
            form.dataset.uploading='true';form.setAttribute('aria-busy','true');
            const status=panel.querySelector('[data-upload-progress-status]'),error=panel.querySelector('[data-upload-progress-error]');
            if(status)status.hidden=false;if(error){error.hidden=true;error.textContent='';}
            const buttons=Array.from(form.querySelectorAll('button[type=submit],input[type=submit]'));
            buttons.forEach(button=>{button.dataset.wasDisabled=button.disabled?'1':'0';button.disabled=true;button.setAttribute('aria-busy','true');});
            const restore=()=>{form.dataset.uploading='false';form.removeAttribute('aria-busy');buttons.forEach(button=>{button.disabled=button.dataset.wasDisabled==='1';button.removeAttribute('aria-busy');delete button.dataset.wasDisabled;});};
            uploadChunks(form,panel).catch(exception=>{restore();fail(panel,exception?.message||'آپلود انجام نشد؛ همان فایل را دوباره انتخاب کن تا از بخش‌های قبلی ادامه پیدا کند.');paint(panel,0,'آپلود متوقف شد؛ امکان ادامه وجود دارد.');});
            return;
        }
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
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');xhr.setRequestHeader('Accept','application/json');
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
            let payload={};
            try{payload=JSON.parse(xhr.responseText||'{}');}catch{}
            if(xhr.status>=200&&xhr.status<300){
                paint(panel,100,'ارسال کامل شد؛ در حال بازگشت به صفحه…');
                if(payload.redirect_url){window.location.assign(payload.redirect_url);}
                else{window.location.reload();}
                return;
            }
            restore();
            const validationMessage=Object.values(payload.errors||{}).flat().find(Boolean);
            const retryAfter=xhr.getResponseHeader('Retry-After');
            const message=xhr.status===429
                ? 'تعداد درخواست‌های آپلود بیش از حد مجاز شده است؛ '+(retryAfter?'پس از '+fa(retryAfter)+' ثانیه دوباره تلاش کنید.':'کمی بعد دوباره تلاش کنید.')
                : (payload.message||validationMessage||(
                    xhr.status===419?'نشست منقضی شده است؛ صفحه را تازه‌سازی کنید.':
                    xhr.status===413?'حجم فایل از محدودیت سرور یا PHP بیشتر است.':
                    xhr.status===422?'فایل انتخاب‌شده معتبر نیست؛ فرمت و حجم آن را بررسی کنید.':
                    'ارسال فایل انجام نشد؛ دوباره تلاش کنید.'
                ));
            fail(panel,message);paint(panel,0,'ارسال ناموفق بود');
        });
        xhr.addEventListener('error',()=>{restore();fail(panel,'ارتباط با سرور قطع شد؛ فایل‌ها ثبت نشده‌اند. دوباره تلاش کنید.');paint(panel,0,'ارتباط قطع شد');});
        xhr.addEventListener('abort',()=>{restore();fail(panel,'ارسال فایل لغو شد.');paint(panel,0,'ارسال لغو شد');});
        xhr.send(new FormData(form));
    });
};
export function bootFileUploadProgress(root=document){root.querySelectorAll('form').forEach(form=>{if(form.dataset.uploadProgress==='off'||form.hasAttribute('data-custom-upload'))return;if(form.querySelector('input[type=file]'))bind(form);});}
