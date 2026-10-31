/* Teacher-only entrypoint. Alpine and all behavior are bundled within this role. */
import { initUi } from './ui.js';
import { initFiles } from './files.js';
import { initAttendance } from './attendance.js';
import { initExam } from './exam.js';
import { initImages } from './images.js';
import { initTeacherForms } from './forms.js';
import { initTeacherUploadProgress } from './upload-progress.js';
import './alpine-runtime.js';
import './course-media-uploader.js';

const boot = () => {
  [initUi, initFiles, initAttendance, initExam, initImages, initTeacherForms, initTeacherUploadProgress]
    .forEach((initialize) => initialize());
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}