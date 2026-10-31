// Alpine is bundled only in the Teacher entrypoint. No shared panel runtime is used.
import Alpine from 'alpinejs';

if (!window.Alpine) {
  window.Alpine = Alpine;
  Alpine.start();
}

export default Alpine;