import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

import './bootstrap'

/*
|--------------------------------------------------------------------------
| Farzin - Application
|--------------------------------------------------------------------------
|
| Alpine is the UI interaction layer for the Farzin frontend.
| Business logic must NOT live here.
|
*/

window.Alpine = Alpine

/*
|--------------------------------------------------------------------------
| Alpine Plugins
|--------------------------------------------------------------------------
*/

Alpine.plugin(collapse)

/*
|--------------------------------------------------------------------------
| Start Alpine
|--------------------------------------------------------------------------
*/

Alpine.start()
