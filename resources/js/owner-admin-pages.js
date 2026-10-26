/* New owner admin page behavior. Server routes and form submissions stay authoritative. */
import './exam-builder.js';
(() => {
  'use strict';
  const formatSize = (bytes) => {
    if (!Number.isFinite(bytes) || bytes < 0) return '۰ بایت';
    const units = ['بایت','کیلوبایت','مگابایت','گیگابایت']; let value = bytes, index = 0;
    while (value >= 1024 && index < units.length - 1) { value /= 1024; index += 1; }
    return new Intl.NumberFormat('fa-IR',{maximumFractionDigits:index ? 1 : 0}).format(value) + ' ' + units[index];
  };
  document.querySelectorAll('[data-media-uploader] input[type="file"],input[data-owner-file-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0], wrapper = input.closest('[data-media-uploader]') || input.closest('form') || input.parentElement;
      if (!wrapper) return;
      const preview=wrapper.querySelector('[data-upload-preview]'), image=wrapper.querySelector('[data-preview-image]'), video=wrapper.querySelector('[data-preview-video]'), name=wrapper.querySelector('[data-preview-name]'), size=wrapper.querySelector('[data-preview-size]'), generic=wrapper.querySelector('[data-preview-generic]');
      if (!file) { if(preview) preview.hidden=true; if(image){image.hidden=true;image.removeAttribute('src');} if(video){video.pause?.();video.hidden=true;video.removeAttribute('src');} return; }
      if(preview) preview.hidden=false; if(name) name.textContent=file.name; if(size) size.textContent=formatSize(file.size); if(image) image.hidden=true; if(video){video.pause?.();video.hidden=true;video.removeAttribute('src');} if(generic) generic.hidden=true;
      const url=URL.createObjectURL(file);
      if(file.type.startsWith('image/') && image){image.src=url;image.hidden=false;image.onload=()=>URL.revokeObjectURL(url);}
      else if(file.type.startsWith('video/') && video){video.src=url;video.hidden=false;video.onloadeddata=()=>URL.revokeObjectURL(url);}
      else if(generic) generic.hidden=false;
    });
  });
  document.querySelectorAll('form[method="POST"],form[method="post"]').forEach((form) => {
    if (form.action.includes('/logout') || form.dataset.ownerSubmitGuard === 'bound') return;
    form.dataset.ownerSubmitGuard='bound';
    form.addEventListener('submit',(event)=>{
      if(form.dataset.uploading==='true'||form.dataset.submitted==='true'){event.preventDefault();return;}
      if(!form.checkValidity()) return;
      form.dataset.submitted='true';
      const button=form.querySelector('button[type="submit"],input[type="submit"]');
      if(!button) return;
      button.setAttribute('aria-busy','true'); button.dataset.ownerOriginalText=button.tagName==='INPUT'?button.value:button.textContent;
      if(button.tagName==='INPUT') button.value='در حال ذخیره…'; else button.textContent='در حال ذخیره…';
    });
    window.addEventListener('pageshow',()=>{
      form.dataset.submitted='false';
      const button=form.querySelector('button[aria-busy="true"],input[aria-busy="true"]'); if(!button) return;
      button.removeAttribute('aria-busy'); if(button.tagName==='INPUT') button.value=button.dataset.ownerOriginalText||'ذخیره'; else button.textContent=button.dataset.ownerOriginalText||'ذخیره';
    });
  });
})();
