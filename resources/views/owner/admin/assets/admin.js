import './js/owner/dashboard-range-chart.js';
import '../../../../js/shared/persian-calendar.js';
import '../../../../js/shared/persian-money.js';
import './js/admin-common.js';
import './js/owner/workspace-tabs.js';
import './js/owner/exam-builder.js';
import './js/owner/academy-form-options.js';
import { bootPanelRuntime } from '../../../../js/panel-runtime.js';
import './js/owner/lookup-picker.js';
import './js/owner/student-picker.js';
import './js/owner/copy-one-time-credentials.js';
bootPanelRuntime();

(() => {
'use strict';
const side=document.getElementById('side'),burger=document.getElementById('burger');
burger?.addEventListener('click',()=>{const open=side?.classList.toggle('open')??false;burger.setAttribute('aria-expanded',String(open));});
document.addEventListener('click',event=>{if(!side||!burger||!side.classList.contains('open'))return;if(side.contains(event.target)||burger.contains(event.target))return;side.classList.remove('open');burger.setAttribute('aria-expanded','false');});
const bell=document.getElementById('bell'),pop=document.getElementById('pop');
bell?.addEventListener('click',()=>{const open=pop?.hidden??true;if(pop)pop.hidden=!open;bell.setAttribute('aria-expanded',String(open));});
document.addEventListener('click',event=>{if(!pop||pop.hidden||bell?.contains(event.target)||pop.contains(event.target))return;pop.hidden=true;bell?.setAttribute('aria-expanded','false');});
document.querySelectorAll('[data-course-row]').forEach(row=>{const go=()=>{if(row.dataset.courseRow)window.location.assign(row.dataset.courseRow);};row.addEventListener('click',event=>{if(event.target.closest('a,button,form,summary,details,input,select,textarea,label'))return;go();});row.addEventListener('keydown',event=>{if(event.target!==row||!['Enter',' '].includes(event.key))return;event.preventDefault();go();});});
const search=document.getElementById('adminSearch');
search?.addEventListener('input',()=>{const term=search.value.trim().toLocaleLowerCase('fa');document.querySelectorAll('#nv .ni').forEach(link=>{link.hidden=!link.textContent.toLocaleLowerCase('fa').includes(term);});document.querySelectorAll('#nv .nav-group').forEach(group=>{const visible=[...group.querySelectorAll('.ni')].some(link=>!link.hidden);group.hidden=term!==''&&!visible;if(term&&visible)group.open=true;});});
document.addEventListener('keydown',event=>{if(event.key==='Escape'){if(pop){pop.hidden=true;bell?.setAttribute('aria-expanded','false');}side?.classList.remove('open');burger?.setAttribute('aria-expanded','false');}});
})();
