import Alpine from 'alpinejs';

const alpine = window.Alpine || Alpine;
window.Alpine = alpine;

if (!window.__sheykhanAlpineBooted) {
    alpine.start();
    window.__sheykhanAlpineBooted = true;
}
